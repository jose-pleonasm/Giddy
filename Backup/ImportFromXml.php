<?php

/**
 * Zalohavaci mechanizmus
 *
 * Modul pro export z XML
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Giddy
 * @package   Giddy_Backup
 * @version   $Id: ImportFromXml.php, 2010-10-08 11:28 $
 */

/**
 * Zakladna pro "preskakovaci" vyjimku
 */
class SkipOverException extends Exception {}

/**
 * Tato vyjimka umoznuje vynechat sloupec pri importu
 *
 * @author Jose
 */
class SkipColumnException extends SkipOverException {}

/**
 * Tato vyjimka umoznuje vynechat radek pri importu
 *
 * @author Jose
 */
class SkipRowException extends SkipOverException {}

/**
 * Modul pro import
 *
 * Importuje data z XML dokumentu do DB,
 * inverzni modul k ExportIntoXml
 *
 * @package   Giddy_Backup
 * @author    Josef Hrabec
 * @version   Release: 0.8.4
 * @since     Trida je pristupna od verze 0.1
 */
class ImportFromXml
{
  const NORMAL = 1;
  const INTENSIVE = 2;
  const HARD = 3;

  /**
   * Podporovane (platne) verze zalohovaciho XML dokumentu
   *
   * @var  array
   */
  protected static $supportedVersions = array('1.0');

  /**
   * Objekt pro praci s DB
   *
   * @var DBI_Common
   */
  protected $db;

  /**
   * Nastaveni objektu
   *
   * @var array
   */
  protected $settings;

  /**
   * Nazev funkce pro upravu dat pred vlozenim do DB
   *
   * Funkce musi primat 2 paramatry: column - nazev sloupce, do ktereho hodnota nalezi
   *                                 value  - hodnota, jez bude do sloupce vlozena
   * A musi vratit upravenou hodnotu
   *
   * tj: <code>mixed modifyFunctionName(string $column, mixed $value)</code>
   *
   * @var callback
   */
  protected $modifyFunction = false;

  /**
   * Protokol importu (posledniho)
   *
   * @var array
   */
  protected $protocol;

  /**
   * Pripraveni importu
   *
   * Pokusi se nastavit prostredi (nastaveni php.ini)
   * pro proces dle (predpokladane) narocnosti
   *
   * @param  int $intensity
   * @return void
   */
  protected function prepare ($intensity = self::NORMAL)
  {
    if ($intensity == self::INTENSIVE) {
      $result = @ini_set('max_execution_time', '180');
      if ($result === false) {
        $this->protocol['notice'][] = 'set max_execution_time to 180 failed';
      }
    } elseif ($intensity == self::HARD) {
      $result = @ini_set('max_execution_time', '600');
      $result = $result && @ini_set('memory_limit', '128M');
      if ($result === false) {
        $this->protocol['notice'][] = 'set max_execution_time to 600 and/or memory_limit to 128M failed';
      }
    }
  }

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
  public static function loadFile ($file)
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

    //zkontroluje verzi zpracovavaneho XML dokumentu
    $version = (string) $xml['version'];
    if (!in_array($version, self::$supportedVersions, true)) {
      throw new VersionMismatchException('XML doc version "' . $version . '" is not supported');
    }

    return $xml;
  }

  /**
   * Nacte XML retezec
   *
   * @uses   LIBXML_NOCDATA
   * @uses   in_array()
   * @uses   simplexml_load_string()
   * @uses   libxml_use_internal_errors()
   * @uses   libxml_get_errors()
   * @uses   ImportFromXml::$supportedVersions
   * @param  string  $string
   * @return SimpleXMLElement
   * @throws FileNotFoundException
   * @throws XmlParsingFailedException
   * @throws VersionMismatchException
   * @since  Trida je pristupna od verze 0.6
   */
  public static function loadString ($string)
  {
    libxml_use_internal_errors(true);
    //nacteni XML dokumentu
    $xml = @simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) {
      throw new XmlParsingFailedException('XML parsing failed', 0, libxml_get_errors());
    }

    //zkontroluje verzi zpracovavaneho XML dokumentu
    $version = (string) $xml['version'];
    if (!in_array($version, self::$supportedVersions, true)) {
      throw new VersionMismatchException('XML doc version "' . $version . '" is not supported');
    }

    return $xml;
  }

  /**
   * Osetri data z XML
   *
   * Inverzni funkce k ExportIntoXml::handleDataForXml()
   *
   * @uses   preg_replace()
   * @param  string  $data
   * @return string
   */
  public static function handleDataFromXml ($data)
  {
    if ($data == 'NULL') {
      return 'NULL';
    }
    $data = preg_replace('/<!\[CDATA\[/', '<![CDATA[', $data);
    $data = preg_replace('/\]\]>/', ']]>', $data);

    return $data;
  }

  /**
   * Vrati meta informace o zaloze
   *
   * @uses   empty()
   * @uses   ImportFromXml::loadFile()
   * @param  mixed  $backup
   * @param  string $atr
   * @return mixed
   * @throws ImportFromXmlExceptin
   */
  public static function getBackupInfo ($backup, $atr = NULL)
  {
    if (empty($backup)) {
      throw new ImportFromXmlExceptin('Neplatný zdroj');
    }

    if ($backup instanceof SimpleXMLElement) {
      $xml = $backup;
    } else {
      //nacteni XML dokumentu
      $xml = self::loadFile($backup);
    }

    $info['version']   = (string) $xml['version'];
    $info['source']    = (string) $xml['source'];
    $info['timestamp'] = (string) $xml['timestamp'];
    $info['desc']      = (string) $xml['desc'];

    if (empty($atr)) {
      return $info;
    } else {
      return $info[$atr];
    }
  }

  /**
   * Odhadne narocnost zalohy na zpracovani
   *
   * @uses   filesize()
   * @uses   ImportFromXml::NORMAL
   * @uses   ImportFromXml::INTENSIVE
   * @uses   ImportFromXml::HARD
   * @param  string $file
   * @param  bool   $isFile
   * @return int
   */
  public static function estimateIntensity ($file, $isFile = true)
  {
    if ($isFile) {
      $size = @filesize($file);
      if ($size === false) {
        return false;
      }
    } else {
      $size = strlen($file);
    }
    $limit1 = 634880; // 620kB
    $limit2 = 1269760; // 1.2MB

    if ($size <= $limit1) {
      return self::NORMAL;
    } elseif ($size > $limit1 && $size <= $limit2) {
      return self::INTENSIVE;
    } else {
      return self::HARD;
    }
  }

  /**
   * Constructor
   *
   * @uses   ImportFromXml::$settings
   * @uses   ImportFromXml::$db
   * @param  DBI_Common  $db
   * @param  array       $settings
   */
  public function __construct(DBI_Common $db, $settings = array())
  {
    $this->db = $db;

    $this->settings['liveInfo'] = isset($settings['liveInfo']) ? $settings['liveInfo'] : false;
    $this->settings['failWhenExc'] = isset($settings['failWhenExc']) ? $settings['failWhenExc'] : true;
  }

  /**
   * Zaregistruje fce (pripadne metodu objektu), ktera se aplikuje
   * na kazdou hodnotu kazdeho sloupce!
   *
   * @uses   is_callable()
   * @uses   ImportFromXml::$modifyFunction
   * @param  callback  $callback
   * @return void
   * @throws setModifyFunction
   */
  public function setModifyFunction ($callback)
  {
    if (is_callable($callback)) {
      $this->modifyFunction = $callback;
    } else {
      throw new InvalidArgumentException('Specified argument is not callable');
    }
  }

  /**
   * Importuje data ze specifikovaneho XML dokumentu do DB
   *
   * Zaroven vygeneruje protokol o importu
   *
   * @uses   microtime()
   * @uses   call_user_func()
   * @uses   ImportFromXml::$settings
   * @uses   ImportFromXml::$db
   * @uses   ImportFromXml::$modifyFunction
   * @uses   ImportFromXml::$protocol
   * @uses   DBI::escapeString()
   * @uses   DBI::query()
   * @uses   ImportFromXml::handleDataFromXml()
   * @uses   ImportFromXml::loadFile()
   * @param  string  $file
   * @param  bool    $isFile
   * @return bool    true pokud import uspesne probehl, jinak false
   * @throws InvalidArgumentException
   * @todo   pridat pordporu ruznych datovych typu
   */
  public function import ($file, $isFile = true)
  {
    if ($isFile) {
      $xml = self::loadFile($file);
    } else {
      $xml = self::loadString($file);
    }

    //liveInfo
    if ($this->settings['liveInfo']) {
      echo "<pre>\n\n";
      echo " File $file loaded, import run...\n";
    }

    //inicializace protokolu importu (vymazani dosavadnich hodnot)
    $this->protocol = array();
    $this->protocol['status']      = 'OK';
    $this->protocol['total']       = 0;
    $this->protocol['totalFailed'] = 0;
    $this->protocol['exceptions']  = NULL;
    $this->protocol['time']        = NULL;
    $this->protocol['notice']      = array();

    // pripraveni procesu dle narocnosti
    $intensity = self::estimateIntensity($file, $isFile);
    if ($intensity === false) {
      $this->protocol['notice'][] = 'estimete intensity failed';
      $intensity = self::NORMAL;
    } else {
      $this->protocol['notice'][] = 'intensity: ' . $intensity;
    }
    $this->prepare($intensity);
    //liveInfo
    if ($this->settings['liveInfo']) {
      echo " Choice intensity: $intensity\n";
    }

    //identifikace radku
    $rowNumber = 0;
    //pro zaznamenani casu behu
    $timeStart = microtime(true);

    foreach ($xml as $table => $row) {
      //inkrementace radku - nastaveni aktualni pozice
      $rowNumber++;
      //pro osetreni carky v nazvech sloupcu a hodnot pro insert
      $accent = false;
      //pocatecni inicializace pomocnych promennych pro dalsi cyklus !DULEZITE!
      $columns = "";
      $values  = "";

      //liveInfo
      if ($this->settings['liveInfo']) {
        echo "\n Table: $table, row #$rowNumber\n";
      }

      foreach ($row as $column => $value) {
        //osetri opravnenost carky (jako oddelovace)
        if($accent) {
          $columns .= ", ";
          $values  .= ", ";
        }

        //osetri vstupni data z XML
        $value = self::handleDataFromXml($value);
        //pokud je zaregistrovana fce pro modifikovani dat, je aplikovana
        if ($this->modifyFunction) {
          try {
            $value = call_user_func($this->modifyFunction, $column, $value, $table);
          }
          catch (SkipColumnException $e) {
            $this->protocol['notice'][] = 'column ' . $column . ' at row ' . $rowNumber . ' of table ' . $table . ' skipped';
            // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento sloupec vynechan
            continue;
          }
          catch (SkipRowException $e) {
            $this->protocol['notice'][] = 'row ' . $rowNumber . ' of table ' . $table . ' skipped';
            // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento RADEK vynechan
            continue 2;
          }
        }

        $columns .= "`" . $column . "`";
        if ($value === 'NULL') {
          $values .= "NULL";
        } else {
          $values .= "'" . $this->db->escapeString($value) . "'";
        }

        $accent = true;
      }
    
      //SQL dotaz
      $query = "INSERT INTO `" . $table . "` (" . $columns . ") VALUES (" . $values . ")";

      //liveInfo
      if ($this->settings['liveInfo']) {
        echo "  | query: $query\n";
      }

      //provedeni dotazu
      try {
        $this->db->query($query);

        //liveInfo
        if ($this->settings['liveInfo']) {
          echo "  | result: <span style='color: green'>OK</span>\n\n";
        }
      }
      catch (Exception $e) {
        if ($this->settings['failWhenExc']) {
          throw $e;
        } else {
          $this->protocol['status'] = 'FAILURES';
          $this->protocol['totalFailed']++;
          $this->protocol['exceptions'][$rowNumber] = $e;

          //liveInfo
          if ($this->settings['liveInfo']) {
            echo "  | result: <span style='color: red'>FAILED:</span> " . $e->getMessage() . "\n\n";
          }
        }
      }
    }

    //liveInfo
    if ($this->settings['liveInfo']) {
      echo "</pre>";
    }

    //doba behu
    $this->protocol['time'] = microtime(true) - $timeStart;
    //celkem importovanych radku
    $this->protocol['total'] = $rowNumber;

    return $this->protocol['status'] == 'OK';
  }

  /**
   * Transformovany import
   *
   * Umoznuje import do jinak definovanych tabulek
   * -> vynechani nekterych dat, prejmenovani tabulek ci sloupcu
   *
   * Zaroven vygeneruje protokol o importu
   *
   * @uses   is_array()
   * @uses   microtime()
   * @uses   call_user_func()
   * @uses   SimpleXMLElement::xpath()
   * @uses   ImportFromXml::$settings
   * @uses   ImportFromXml::$db
   * @uses   ImportFromXml::$modifyFunction
   * @uses   ImportFromXml::$protocol
   * @uses   ImportFromXml::loadFile()
   * @uses   ImportFromXml::handleDataFromXml()
   * @uses   DBI::escapeString()
   * @uses   DBI::query()
   * @param  string  $file
   * @param  array   $mapTables  prevodni matice tabulek: klic je nazev puvodni, hodnota je nazev nove
   * @param  array   $mapColumns prevodni matice sloupcu: klic je nazev nove tabulky, hodnota je pole, kde:
   *                                                      klic je nazev puvodniho sloupce a hodnota je nazev noveho sloupce
   * @param  bool    $isFile
   * @return bool    true pokud import uspesne probehl, jinak false
   * @throws InvalidArgumentException
   */
  public function transformImport ($file, $mapTables, $mapColumns, $isFile = true)
  {
    if (!is_array($mapTables)) {
      throw new InvalidArgumentException('Tables matrix is not array');
    }
    if (!is_array($mapColumns)) {
      throw new InvalidArgumentException('Columns matrix is not array');
    }

    //nacteni XML dokumentu
    if ($isFile) {
      $xml = self::loadFile($file);
    } else {
      $xml = self::loadString($file);
    }

    //liveInfo
    if ($this->settings['liveInfo']) {
      echo "<pre>\n\n";
      echo " File $file loaded, transformImport run...\n";
    }

    //inicializace protokolu importu (vymazani dosavadnich hodnot)
    $this->protocol = NULL;
    $this->protocol['status']      = 'OK';
    $this->protocol['total']       = 0;
    $this->protocol['totalFailed'] = 0;
    $this->protocol['exceptions']  = NULL;
    $this->protocol['time']        = NULL;
    $this->protocol['notice']      = array();

    // pripraveni procesu dle narocnosti
    $intensity = self::estimateIntensity($file, $isFile);
    if ($intensity === false) {
      $this->protocol['notice'][] = 'estimate intensity failed';
      $intensity = self::NORMAL;
    } else {
      $this->protocol['notice'][] = 'intensity: ' . $intensity;
    }
    $this->prepare($intensity);
    //liveInfo
    if ($this->settings['liveInfo']) {
      echo " Choice intensity: $intensity\n";
    }

    //identifikace radku
    $rowNumber = 0;
    //pro zaznamenani casu behu
    $timeStart = microtime(true);

    foreach ($mapTables as $elementName => $tableName) {
      //dotaz na vsechny aktualni elementy
      $results = $xml->xpath('/backup/' . $elementName);

      //liveInfo
      if ($this->settings['liveInfo']) {
        echo "\n Table: $tableName\n";
      }

      //vlozeni po radku
      foreach ($results as $result) {
        //inkrementace radku - nastaveni aktualni pozice
        $rowNumber++;
        //pro osetreni carky v nazvech sloupcu a hodnot pro insert
        $accent = false;
        //pocatecni inicializace pomocnych promennych pro dalsi cyklus !DULEZITE!
        $columns = "";
        $values  = "";

        //liveInfo
        if ($this->settings['liveInfo']) {
          echo "  + Row #$rowNumber\n";
        }

        foreach ($mapColumns[$tableName] as $subElementName => $columnName) {
          //osetreni vstupnich dat z XML
          $value = self::handleDataFromXml($result->$subElementName);
          //pokud je zaregistrovana fce pro modifikovani dat, je aplikovana
          if ($this->modifyFunction) {
            try {
              $value = call_user_func($this->modifyFunction, $columnName, $value, $tableName);
            }
            catch (SkipColumnException $e) {
              $this->protocol['notice'][] = 'column ' . $columnName . ' at row ' . $rowNumber . ' of table ' . $tableName . ' skipped';
              // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento sloupec vynechan
              continue;
            }
            catch (SkipRowException $e) {
              $this->protocol['notice'][] = 'row ' . $rowNumber . ' of table ' . $tableName . ' skipped';
              // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento RADEK vynechan
              continue 2;
            }
          }

          //osetri opravnenost carky (jako oddelovace)
          if ($accent) {
            $columns .= ", ";
            $values  .= ", ";
          }

          $columns .= "`" . $columnName . "`";
          if ($value === 'NULL') {
            $values .= "NULL";
          } else {
            $values .= "'" . $this->db->escapeString($value) . "'";
          }

          $accent = true;
        }

        //SQL dotaz
        $query = "INSERT INTO `" . $tableName . "` (" . $columns . ") VALUES (" . $values . ")";

        //liveInfo
        if ($this->settings['liveInfo']) {
          echo "  | query: $query\n";
        }

        //provedeni dotazu
        try {
          $this->db->query($query);

          //liveInfo
          if ($this->settings['liveInfo']) {
            echo "  | result: <span style='color: green'>OK</span>\n\n";
          }
        }
        catch (Exception $e) {
          if ($this->settings['failWhenExc']) {
            throw $e;
          }
          else {
            $this->protocol['status'] = 'FAILURES';
            $this->protocol['totalFailed']++;
            $this->protocol['exceptions'][$rowNumber] = $e;

            //liveInfo
            if ($this->settings['liveInfo']) {
              echo "  | result: <span style='color: red'>FAILED:</span> " . $e->getMessage() . "\n\n";
            }
          }
        } //end catch
      } //end foreach #2
    } //end foreach #1

    //liveInfo
    if ($this->settings['liveInfo']) {
      echo "</pre>";
    }

    //doba behu
    $this->protocol['time'] = microtime(true) - $timeStart;
    //celkem importovanych radku
    $this->protocol['total'] = $rowNumber;

    return $this->protocol['status'] == 'OK';
  }

  /**
   * Umoznuje import do jinak pojmenovanych tabulek/sloupcu
   *
   * @param  string  $file
   * @param  array   $alteredTables   matice prejmenovanych tabulek: klic je nazev puvodni, hodnota je nazev nove
   * @param  array   $alteredColumns  matice prejmenovanych sloupcu: klic je nazev nove tabulky, hodnota je pole, kde:
   *                                                                   klic je nazev puvodni, hodnota je nazev nove
   * @param  bool    $isFile
   * @return bool    true pokud import uspesne probehl, jinak false
   */
  public function regulatedImport($file, $alteredTables = NULL, $alteredColumns = NULL, $isFile = true)
  {
    if ($isFile) {
      $xml = self::loadFile($file);
    } else {
      $xml = self::loadString($file);
    }

    //inicializace protokolu importu (vymazani dosavadnich hodnot)
    $this->protocol = array();
    $this->protocol['status']      = 'OK';
    $this->protocol['total']       = 0;
    $this->protocol['totalFailed'] = 0;
    $this->protocol['exceptions']  = NULL;
    $this->protocol['time']        = NULL;
    $this->protocol['notice']      = array();

    // pripraveni procesu dle narocnosti
    $intensity = self::estimateIntensity($file, $isFile);
    if ($intensity === false) {
      $this->protocol['notice'][] = 'estimete intensity failed';
      $intensity = self::NORMAL;
    } else {
      $this->protocol['notice'][] = 'intensity: ' . $intensity;
    }
    $this->prepare($intensity);

    //identifikace radku
    $rowNumber = 0;
    //pro zaznamenani casu behu
    $timeStart = microtime(true);

    foreach ($xml as $table => $row) {
      $correctTable = isset($alteredTables[$table]) ? $alteredTables[$table] : $table;
      //inkrementace radku - nastaveni aktualni pozice
      $rowNumber++;
      //pro osetreni carky v nazvech sloupcu a hodnot pro insert
      $accent = false;
      //pocatecni inicializace pomocnych promennych pro dalsi cyklus !DULEZITE!
      $columns = "";
      $values  = "";

      foreach ($row as $column => $value) {
        $correctColumn = isset($alteredColumns[$correctTable][$column]) ? $alteredColumns[$correctTable][$column] : $column;
        //osetri opravnenost carky (jako oddelovace)
        if($accent) {
          $columns .= ", ";
          $values  .= ", ";
        }

        //osetri vstupni data z XML
        $value = self::handleDataFromXml($value);
        //pokud je zaregistrovana fce pro modifikovani dat, je aplikovana
        if ($this->modifyFunction) {
          try {
            $value = call_user_func($this->modifyFunction, $correctColumn, $value, $correctTable);
          }
          catch (SkipColumnException $e) {
            $this->protocol['notice'][] = 'column ' . $correctColumn . ' at row ' . $rowNumber . ' of table ' . $correctTable . ' skipped';
            // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento sloupec vynechan
            continue;
          }
          catch (SkipRowException $e) {
            $this->protocol['notice'][] = 'row ' . $rowNumber . ' of table ' . $correctTable . ' skipped';
            // pokud modifikacni funkce vyhodi tuto vyjimku, bude tento RADEK vynechan
            continue 2;
          }
        }

        $columns .= "`" . $correctColumn . "`";
        if ($value === 'NULL') {
          $values .= "NULL";
        } else {
          $values .= "'" . $this->db->escapeString($value) . "'";
        }

        $accent = true;
      }

      //SQL dotaz
      $query = "INSERT INTO `" . $correctTable . "` (" . $columns . ") VALUES (" . $values . ")";

      //liveInfo
      if ($this->settings['liveInfo']) {
        echo "  | query: $query\n";
      }

      //provedeni dotazu
      try {
        $this->db->query($query);
      }
      catch (Exception $e) {
        if ($this->settings['failWhenExc']) {
          throw $e;
        } else {
          $this->protocol['status'] = 'FAILURES';
          $this->protocol['totalFailed']++;
          $this->protocol['exceptions'][$rowNumber] = $e;
        }
      }
    }

    //doba behu
    $this->protocol['time'] = microtime(true) - $timeStart;
    //celkem importovanych radku
    $this->protocol['total'] = $rowNumber;

    return $this->protocol['status'] == 'OK';
  }

  /**
   * Poskytne protokol o poslednim importu
   *
   * @uses   ImportFromXml::$protocol;
   * @return array
   */
  public function getProtocol ()
  {
    return $this->protocol;
  }
}
