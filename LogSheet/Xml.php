<?php

/**
 * XML LogSheet
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_LogSheet
 * @author     Josef Hrabec
 * @version    $Id: Xml.php, 2010-01-04 18:58 $
 */

/**
 * Implementuje LogSheet pro XML
 *
 * @package    Giddy
 * @subpackage LogSheet
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.5
 */

class LogSheet_Xml extends LogSheet
{
  /**
   * Podporovane (platne) verze zalohovaciho XML dokumentu
   *
   * @var array
   */
  private static $supportedVersions = array('2.1', '2.2');

  /**
   * Zdrojovy dokument
   *
   * @var string
   */
  private $source;

  /**
   * Verze zdrojoveho dokumentu
   *
   * @var string
   */
  private $version;

  /**
   * Pocet zaznamu
   *
   * @var int
   */
  private $count;

  /**
   * Informace o zdroji logu
   *
   * @var array
   */
  private $meta;

  /**
   * Zaznamy logu
   *
   * @var array
   */
  private $records;

  /** @var bool */
  private $sqlAvailable = false;

  /**  @var SqliteAdapter */
  private $adapter;

  /**
   * Nacte XML soubor
   *
   * @uses   LIBXML_NOCDATA
   * @uses   is_file()
   * @uses   in_array()
   * @uses   simplexml_load_file()
   * @uses   libxml_use_internal_errors()
   * @uses   libxml_get_errors()
   * @uses   ImportFromXml::$supportedVersions
   * @param  string  $file
   * @return SimpleXMLElement
   * @throws FileNotFoundException
   * @throws XmlParsingFailedException
   * @throws VersionMismatchException
   */
  private function loadFile($file, $checkVersion = true)
  {
    if (!is_file($file)) {
      throw new FileNotFoundException('File "' . $file . '" doesnt exist');
    }

    libxml_use_internal_errors(true);
    //nacteni XML dokumentu
    $xml = @simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) {
      throw new XmlParsingFailedException('XML parsing failed', 0, libxml_get_errors());
    }

    //zkontroluje verzi zpracovavaneho XML dokumentu (pokud neni externe nezadany)
    if ($checkVersion) {
      $version = (string) $xml['version'];
      if (!in_array($version, self::$supportedVersions, true)) {
        throw new VersionMismatchException('XML doc version "' . $version . '" is not supported');
      }
    }

    return $xml;
  }

  /**
   * Inicializace
   *
   * @param  string  $source
   * @param  mixed $params
   * @return void
   */
  public function __construct($source, $params = NULL)
  {
    $this->source = $source;
    $checkVersion = (is_bool($params) ? $params : (isset($params['checkVersion']) ? (bool) $params['checkVersion'] : true));
    $xml = self::loadFile($this->source, $checkVersion);

    $this->version = (string) $xml['version'];
    $this->meta['web_name']    = (string) $xml->source->web_name;
    $this->meta['description'] = (string) $xml->source->description;
    // eliminace rozdilnych struktur dle verzi
    if (version_compare($this->version, '2.2', '>=')) {
      $this->meta['web_id'] = (string) $xml->source->web_id;
    } else {
      $this->meta['web_id'] = (string) $xml->source->server_name . '/' . (string) $xml->source->web_name;
    }
    $logs = (array) $xml->logs;
    if (is_array($logs['log'])) {
      $logs = $logs['log'];
    }

    $this->records = array();
    $this->count = 0;
    foreach ($logs as $log) {
      $this->records[$this->count] = array(
        'id'         => (integer) $log['id'],
        'timestamp'  => (string) $log->timestamp,
        'ip'         => (string) $log->user_info->ip_addr,
        'domen'      => (string) $log->user_info->domen_addr,
        'user_agent' => (string) $log->user_info->http_user_agent,
        'file'       => (string) $log->debug_info->file,
        'line'       => (string) $log->debug_info->line,
        'message'    => (string) $log->original_notice->message
      );
      // eliminace rozdilnych struktur dle verzi
      if (version_compare($this->version, '2.2', '>=')) {
        $this->records[$this->count]['severity'] = (string) $log->severity;
        $this->records[$this->count]['code'] = (string) $log->original_notice->code;
        $this->records[$this->count]['trace'] = (string) $log->debug_info->backtrace;
        $this->records[$this->count]['context'] = (string) $log->debug_info->context;
        $this->records[$this->count]['type'] = '';
      } else {
        $this->records[$this->count]['type'] = (string) $log->original_notice->type;
        $this->records[$this->count]['trace'] = (string) $log->debug_info->backtrace;
        $this->records[$this->count]['context'] = (string) $log->debug_info->context;
        $this->records[$this->count]['severity'] = '';
        $this->records[$this->count]['code'] = '';
      }
      $this->count++;
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#getVersion()
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#getMeta($subject)
   */
  public function getMeta($subject)
  {
    return $this->meta[$subject];
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#getRecords()
   */
  public function getRecords($restrictions = NULL)
  {
    if (!isset($restrictions)) {
      return $this->records;

    } else {
      $result = array();
      foreach ($this->records as $record) {
        $ok = 0;
        foreach ($restrictions as $param => $value) {
          if ($record[$param] == $value) {
            $ok++;
          }
        }
        if ($ok == count($restrictions)) {
          $result[] = $record;
        }
      }
      return $result;
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#getRecord($id)
   */
  public function getRecord($id)
  {
    foreach ($this->records as $record) {
      if ($record['id'] == $id) {
        return $record;
      }
    }
    return NULL;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#getLastRecord()
   */
  public function getLastRecord()
  {
    return $this->records[count($this->records)-1];
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#count()
   */
  public function count()
  {
    return $this->count;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#enableSql($params)
   */
  public function enableSql($params = NULL)
  {
    if ($this->sqlAvailable) {
      return true;
    }

    // pomoci DBI
    if ($params instanceof DBI_Common || is_array($params)) {
      if (is_array($params)) {
        if (!($params[0] instanceof DBI_Common)) {
          return false;
        }
        $dbi = $params[0];
      } else {
        $dbi = $params;
      }
      require_once 'DbiAdapter.php';
      $this->adapter = new DbiAdapter($dbi);
      if ($this->adapter->tableExists()) {
        $lastRecSource = $this->getLastRecord();
        $lastRecSql    = $this->adapter->arrayQuery("SELECT timestamp FROM " . LogSheet_ISqlAdapter::TABLE . " ORDER BY timestamp DESC LIMIT 1");
        // pokud je zdroj zmenen po vytvoreni jeho SQL obrazu, je obraz vytvoren znovu
        if ($lastRecSource['timestamp'] > $lastRecSql[0]['timestamp']) {
          $this->adapter->clear();
          $this->adapter->createTable();
          $this->adapter->insertArray($this->records);
        }
      } else {
        $this->adapter->createTable();
        $this->adapter->insertArray($this->records);
      }
      return $this->sqlAvailable = true;

    // pomoci SQLite
    } elseif (System::loadExtension('sqlite')) {
      require_once 'SqliteAdapter.php';
      $this->adapter = new SqliteAdapter('log_sheet_' . md5($this->source));
      if ($this->adapter->tableExists()) {
        // pokud je zdroj zmenen po vytvoreni jeho SQLite obrazu, je obraz vytvoren znovu
        if (filemtime($this->source)+60 > filemtime('log_sheet_' . md5($this->source))) {
          $this->adapter->clear();
          $this->adapter->createTable();
          $this->adapter->insertArray($this->records);
        }
      } else {
        $this->adapter->createTable();
        $this->adapter->insertArray($this->records);
      }
      return $this->sqlAvailable = true;

    } else {
      return false;
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#isSqlAvailable()
   */
  public function isSqlAvailable()
  {
    return $this->sqlAvailable;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet#sqlQuery($query)
   */
  public function sqlQuery($query)
  {
    if (!$this->isSqlAvailable()) {
      throw new BadMethodCallException('SQL is not available');
    }
    return $this->adapter->arrayQuery($query);
  }
}
