<?php

/**
 * Logovaci mechanizmus
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Logger.php, 2010-03-17 16:17 $
 */

Base::import('Common');

/**
 * Logger
 *
 * Trida realizuje logovani udalosti.
 * Umoznuje logovani nasledujicimi metodami:
 * - xml: do XML dokumentu
 * - file: do souboru - TXT
 * - mail: zasilani e-mailu
 * - db: do DB
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class Logger extends Common
{
  const EMERG   = 0;  // System is unusable
  const ALERT   = 1;  // Immediate action required
  const CRIT    = 2;  // Critical conditions
  const ERROR   = 3;  // Error conditions
  const WARNING = 4;  // Warning conditions
  const NOTICE  = 5;  // Normal but significant
  const INFO    = 6;  // Informational
  const DEBUG   = 7;  // Debug-level messages

  const ALL   = 255;  // All messages
  const NONE  = -1;   // No message

  /**
   * Verze tridy
   */
  const VERSION = '2.6';

  /**
   * Verze XML dokumentu (dle platneho DTD)
   */
  const XML_DOC_VER = '2.2';

  /**
   * Zda a co (od kdy) logovat
   *
   * @var integer
   */
  protected $recordLevel;

  /**
   * Jakym zpusobem se ma log zaznamenat [file|xml|mail|db]
   *
   * @var string
   */
  protected $method;

  /**
   * Cil logu (nazev souboru, e-mail, pole s DB parametry)
   *
   * @var  mixed
   */
  protected $target;

  /**
   * Zprava
   *
   * @var array
   */
  protected $recNotice;

  /**
   * Informace o puvodu zpravy
   *
   * @var array
   */
  protected $recSource;

  /**
   * Informace o odpovidajicim klientovi
   *
   * @var array
   */
  protected $recClient;

  /**
   * Nastaveni
   *
   * @var array
   */
  protected $cfg = array();

  /**
   * Format logu pro soubor
   *
   * Popis:
   * %time%  - cas
   * %id%    - ID webu
   * %name%  - nazev webu
   * %severity%  - typ zpravy
   * %msg%   - zprava
   * %file%  - soubor
   * %line%  - radek
   * %trace% - trace
   * %ip%    - client IP
   * %agent% - USER AGENT
   *
   * @var string
   */
  public $fileFormat = '%time% [%severity%] [client %ip%] %msg% in %file% on line %line%';

  /**
   * Informace o zdroji - nazev webu
   *
   * @var string
   */
  public static $webName;

  /**
   * Informace o zdroji - id webu
   *
   * @var string
   */
  public static $webId;

  /**
   * Informace o zdroji - popis webu
   *
   * @var string
   */
  public static $webDesc;

  /**
   * Osetri vstupni data do XML dokumentu
   *
   * @uses   ENT_QUOTES
   * @uses   htmlspecialchars()
   * @uses   preg_replace()
   * @uses   Logger::$cfg
   * @since  Metoda je pristupna od verze 0.4
   * @param  string  $data  data jez maji byt vlozena do XML
   * @return string  osetrena data
   */
  private function escapeXml ($data)
  {
    //http://phpfashion.com/escapovani-definitivni-prirucka
    return htmlspecialchars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', $data),
                            ENT_QUOTES,
                            $this->cfg['encoding']);
  }

  /**
   * Zaznamenani logu metodou "mail" => zaslani na e-mail
   *
   * @uses   phpversion()
   * @uses   isset()
   * @uses   mail()
   * @uses   date()
   * @uses   str_replace()
   * @uses   implode()
   * @uses   Logger::$webName
   * @uses   Logger::$webId
   * @uses   Logger::$webDesc
   * @uses   Logger::$fileFormat
   * @uses   Logger::$cfg
   * @uses   Logger::$target
   * @uses   Logger::$recNotice
   * @uses   Logger::$recClient
   * @uses   Logger::$recSource
   * @uses   Logger::severityToName()
   * @uses   Logger::fileLog()
   * @return void
   */
  protected function sendLog ()
  {
    $headSep = "\r\n";
    $priority = ($this->recNotice['severity'] < self::NOTICE) ? 1 : 2;
    //nastaveni hlavicek zpravy
    $headers = array();
    $headers[] = "Content-Type: text/plain; charset=\"" . $this->cfg['encoding'] . "\"";
    $headers[] = "From: " . self::$webName;
    $headers[] = "X-Priority: " . $priority;
    $headers[] = "X-Mailer: Giddy Framework/Logger";

    // predmet
    $subject = "Zprava z webu " . self::$webId;

    // zprava
    $main  = "\n [Web]";
    $main .= "\n Web:\t " . self::$webName;
    $main .= "\n ID:\t " . self::$webId;
    $main .= "\n Popis:\t " . self::$webDesc;
    $main .= "\n\n [Log]";
    $main .= "\n Čas:\t " . date($this->cfg['timeformat']) . " " . $this->cfg['timezone'];
    $main .= "\n Typ:\t " . self::severityToName($this->recNotice['severity']);
    $main .= "\n Kód:\t " . $this->recNotice['code'];
    $main .= "\n Zpráva:\n > " . str_replace("\n", "\n > ", $this->recNotice['message']);
    $main .= "\n\n [Client]";
    $main .= "\n IP adresa:\t " . $this->recClient['ip_addr'];
    $main .= "\n Doména:\t " . $this->recClient['domen_addr'];
    $main .= "\n User-Agent:\t " . $this->recClient['user_agent'];
    $main .= "\n\n [Source]";
    if (isset($this->recSource['file'])) {
      $main .= "\n Soubor:\t " . $this->recSource['file'];
    }
    if (isset($this->recSource['line'])) {
      $main .= "\n Řádek:\t\t ".$this->recSource['line'];
    }
    if (isset($this->recSource['trace'])) {
      $main .= "\n Backtrace:\t ".$this->recSource['trace'];
    }
    if (isset($this->recSource['context'])) {
      $main .= "\n Kontext:\t ".$this->recSource['context'];
    }
    $main .= "\n\n ------------------------------------------------------------";
    $main .= "\n Generated by Logger " . self::VERSION;
    $main .= "\n";

    //odeslani udalosti na mail
    if (!mail($this->target, $subject, $main, implode($headSep, $headers))) {
      //nahradni reseni
      $buffer = $this->target;
      $this->target = 'mail-salvage.log';
      $this->fileLog();
      $this->target = $buffer;
    }
  }

  /**
   * Zaznamenani logu metodou "file" => ulozeni do souboru
   *
   * @uses   LOCK_EX
   * @uses   LOCK_UN
   * @uses   isset()
   * @uses   fopen()
   * @uses   flock()
   * @uses   fwrite()
   * @uses   fclose()
   * @uses   str_replace()
   * @uses   Logger::$webName
   * @uses   Logger::$webId
   * @uses   Logger::$webDesc
   * @uses   Logger::$fileFormat
   * @uses   Logger::$cfg
   * @uses   Logger::$target
   * @uses   Logger::$recNotice
   * @uses   Logger::$recClient
   * @uses   Logger::$recSource
   * @uses   Logger::severityToName()
   * @uses   Logger::fileLog()
   * @return void
   */
  protected function fileLog ()
  {
    $report = str_replace('%time%', date($this->cfg['timeformat']), $this->fileFormat);
    if (isset(self::$webId)) {
      $report = str_replace('%id%', self::$webId, $report);
    }
    $report = str_replace('%name%', self::$webName, $report);
    $report = str_replace('%severity%', self::severityToName($this->recNotice['severity']), $report);
    $report = str_replace('%msg%', $this->recNotice['message'], $report);
    if (isset($this->recSource['file'])) {
      $report = str_replace('%file%', $this->recSource['file'], $report);
    }
    if (isset($this->recSource['line'])) {
      $report = str_replace('%line%', $this->recSource['line'], $report);
    }
    if (isset($this->recSource['trace'])) {
      $report = str_replace('%trace%', $this->recSource['trace'], $report);
    }
    $report = str_replace('%ip%', $this->recClient['ip_addr'], $report);
    $report = str_replace('%agent%', $this->recClient['user_agent'], $report);

    //ulozeni zaznamu do souboru - zalogovani udalosti
    $file = fopen($this->target, 'a');
    $canWrite = flock($file, LOCK_EX);
    while (!$canWrite) {
      usleep(round(rand(0, 20)*100000));
      $canWrite = flock($file, LOCK_EX);
    }
    fwrite($file, $report . "\n");
    flock($file, LOCK_UN); //uvolneni zamku
    fclose($file);
  }

  /**
   * Zaznamenani logu metodou "xml" => ulozeni do XML dokumentu
   *
   * @uses   LOCK_EX
   * @uses   LOCK_UN
   * @uses   empty()
   * @uses   is_file()
   * @uses   fopen()
   * @uses   flock()
   * @uses   fwrite()
   * @uses   fclose()
   * @uses   simplexml_load_file()
   * @uses   SimpleXMLElement::xpath()
   * @uses   SimpleXMLElement::attributes()
   * @uses   SimpleXMLElement::addChild()
   * @uses   SimpleXMLElement::addAttribute()
   * @uses   SimpleXMLElement::asXml()
   * @uses   Logger::$webName
   * @uses   Logger::$webId
   * @uses   Logger::$webDesc
   * @uses   Logger::$cfg
   * @uses   Logger::$target
   * @uses   Logger::$recNotice
   * @uses   Logger::$recClient
   * @uses   Logger::$recSource
   * @uses   Logger::severityToName()
   * @uses   Logger::escapeXml()
   * @uses   Logger::fileLog()
   * @return void
   */
  protected function xmlLog ()
  {
    //pokud soubor (XML dokument) neexistuje, vytvori se
    if (!is_file($this->target)) {
      $xmlSource  = "<?xml version=\"1.0\" encoding=\"" . $this->cfg['encoding'] . "\" standalone=\"yes\"?>";
      $xmlSource .= "\n<!DOCTYPE log_sheet SYSTEM \"http://jose.cz/work/xml/dtd/Logger/log_sheet" . self::XML_DOC_VER . ".dtd\">";
      $xmlSource .= "\n<log_sheet version=\"" . self::XML_DOC_VER . "\">";
      $xmlSource .= "\n\t<source>";
      $xmlSource .= "\n\t\t<web_name>" . self::$webName . "</web_name>";
      $xmlSource .= "\n\t\t<web_id>" . self::$webId . "</web_id>";
      $xmlSource .= "\n\t\t<description>" . self::$webDesc . "</description>";
      $xmlSource .= "\n\t</source>";
      $xmlSource .= "\n\t<logs>";
      $xmlSource .= "\n\t</logs>";
      $xmlSource .= "\n</log_sheet>";

      $file = fopen($this->target, 'a');
      $canWrite = flock($file, LOCK_EX);
      while (!$canWrite) {
        usleep(round(rand(0, 20)*100000));
        $canWrite = flock($file, LOCK_EX);
      }
      fwrite($file, $xmlSource);
      flock($file, LOCK_UN);
      fclose($file);
    }

    //nacteni XML dokumentu
    $xml = simplexml_load_file($this->target);
    if (empty($xml)) {
      //nahradni reseni
      $buffer = $this->target;
      $this->target = 'xml-salvage.log';
      $this->fileLog();
      $this->target = $buffer;
    }

    //zjisteni a nastaveni id
    $lastLog = $xml->xpath("/log_sheet/logs/log[last()]");
    if (!empty($lastLog[0])) {
      $atr = $lastLog[0]->attributes();
      if (!empty($atr['id'])) {
        $id  = $atr['id'] + 1;
      } else {
        $id  = 1;
      }
    } else {
      $id = 1;
    }

    //sestaveni zaznamu - upraveni xml dokumentu
    $logs = $xml->logs;

    $log = $logs->addChild('log');
    $log->addAttribute('id', $id);
    $log->addChild('severity', self::severityToName($this->recNotice['severity']));
    $timestamp = $log->addChild('timestamp', date($this->cfg['timeformat']));
    $timestamp->addAttribute('timezone', $this->cfg['timezone']);
    $timestamp->addAttribute('format', $this->cfg['timeformat']);
    $timestamp->addAttribute('xml:space', 'preserve', 'http://www.w3.org/XML/1998/namespace');
    $original_notice = $log->addChild('original_notice');
    $original_notice->addChild('code', $this->recNotice['code']);
    $original_notice->addChild('message', $this->escapeXml($this->recNotice['message']));
    $user_info = $log->addChild('user_info');
    $user_info->addChild('ip_addr', $this->recClient['ip_addr']);
    $user_info->addChild('domen_addr', $this->recClient['domen_addr']);
    $user_info->addChild('http_user_agent', $this->escapeXml($this->recClient['user_agent']));
    if (isset($this->recSource['file'])
        || isset($this->recSource['line'])
        || isset($this->recSource['trace'])
        || isset($this->recSource['context'])) {
      $debug_info = $log->addChild('debug_info');
    }
    if (isset($this->recSource['file'])) {
      $debug_info->addChild('file', $this->recSource['file']);
    }
    if (isset($this->recSource['line'])) {
      $debug_info->addChild('line', $this->recSource['line']);
    }
    if (isset($this->recSource['trace'])) {
      $debug_info->addChild('backtrace', $this->escapeXml($this->recSource['trace']));
    }
    if (isset($this->recSource['context'])) {
      $debug_info->addChild('context', $this->escapeXml($this->recSource['context']));
    }

    //zapis do souboru - zalogovani udalost
    $xml->asXML($this->target);
  }

  /**
   * Zaznamenani logu metodou "db" => ulozeni do DB
   *
   * Metoda vyuziva jednotku DBI
   * Tabulka musi mit nasledujici strukturu (MySQL):
   * 
   * <code>
   *
   * CREATE TABLE table_log (
   *     id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
   *     web_id CHAR(100) NOT NULL DEFAULT '',
   *     severity CHAR(10) NOT NULL DEFAULT '',
   *     code INTEGER NOT NULL DEFAULT 0,
   *     message TEXT NOT NULL,
   *     file CHAR(100) NOT NULL DEFAULT '',
   *     line CHAR(5) NOT NULL DEFAULT '',
   *     trace TEXT,
   *     ip_addr CHAR(255) NOT NULL DEFAULT '',
   *     domen_addr CHAR(255) NOT NULL DEFAULT '',
   *     user_agent CHAR(255) NOT NULL DEFAULT '',
   *     timestamp DATETIME NOT NULL,
   *     PRIMARY KEY(id)
   * ) TYPE=MyISAM COMMENT='Logy'
   *   DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;
   *
   * </code>
   *
   * Pokud behem insertu nastane vyjimka, je zaznam zaznamenan do souboru!
   *
   * @uses   Logger::$webId
   * @uses   Logger::$target
   * @uses   Logger::$recNotice
   * @uses   Logger::$recClient
   * @uses   Logger::$recSource
   * @uses   Logger::severityToName()
   * @uses   Logger::fileLog()
   * @uses   DBI::escapeString()
   * @uses   DBI::query()
   * @return void
   * @since  Metoda je pristupna od verze 2.5
   */
  protected function dbLog ()
  {
    $query = "INSERT INTO ".$this->target['table']." (web_id,
                                                      severity,
                                                      code,
                                                      message,
                                                      file,
                                                      line,
                                                      trace,
                                                      relevant_info,
                                                      ip_addr,
                                                      domen_addr,
                                                      user_agent,
                                                      timestamp)
              VALUES ('" . $this->target['db']->escapeString(self::$webId) . "',
                      '" . $this->target['db']->escapeString(self::severityToName($this->recNotice['severity'])) . "',
                      " . (integer) $this->recNotice['code'] . ",
                      '" . $this->target['db']->escapeString($this->recNotice['message']) . "',
                      '" . $this->target['db']->escapeString($this->recSource['file']) . "',
                      '" . $this->recSource['line'] . "',
                      '" . $this->target['db']->escapeString($this->recSource['trace']) . "',
                      '" . $this->target['db']->escapeString($this->recSource['context']) . "',
                      '" . $this->target['db']->escapeString($this->recClient['ip_addr']) . "',
                      '" . $this->target['db']->escapeString($this->recClient['domen_addr']) . "',
                      '" . $this->target['db']->escapeString($this->recClient['user_agent']) . "',
                      NOW())";

    try {
      $this->target['db']->query($query);
    }
    catch (Exception $e) {
      //nahradni reseni
      $buffer = $this->target;
      $this->target = 'db-salvage.log';
      $this->fileLog();
      $this->target = $buffer;
    }
  }

  /**
   * Inicializace
   *
   * @param  integer  $recordLevel  stupen zaznamenavani
   * @param  string   $method       metoda logovani
   * @param  string   $target       cil logovani
   * @param  array    $cfg          nastaveni instance
   * @throws InvalidArgumentException
   */
  public function __construct ($recordLevel = self::ALL, $method = 'file', $target = 'messages.log', $cfg = array())
  {
    if ($method != 'file' && $method != 'mail'
        && $method != 'xml' && $method != 'db') {
      throw new InvalidArgumentException('Method ' . $method . ' is not supported');
    }
    if ($method == 'db') {
      if (!($target['db'] instanceof DBI_Common) || empty($target['table'])) {
        throw new InvalidArgumentException('Target for specified method is not complete');
      }
    }

    $this->recordLevel = $recordLevel;
    $this->method      = $method;
    $this->target      = $target;
    $this->cfg['encoding']   = isset($cfg['encoding']) ? $cfg['encoding'] : 'UTF-8';
    $this->cfg['timezone']   = isset($cfg['timezone']) ? $cfg['timezone'] : '+0100';
    $this->cfg['timeformat'] = isset($cfg['timeformat']) ? $cfg['timeformat'] : 'Y-m-d H:i:s';
  }

  /**
   * Zalogovani
   *
   * Inicializuje promenne, rozhodne o zpusobu zpracovani logu
   *
   * @uses   isset()
   * @uses   GetHostByAddr()
   * @uses   Logger::$method
   * @uses   Logger::$recordLevel
   * @uses   Logger::$recNotice
   * @uses   Logger::$recClient
   * @uses   Logger::$recSource
   * @uses   Logger::fileLog()
   * @uses   Logger::sendLog()
   * @uses   Logger::xmlLog()
   * @uses   Logger::dbLog()
   * @param  integer $severity dulezitost zpravy
   * @param  string  $message  zprava/hlaseni
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function log ($severity, $message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    if ($severity > $this->recordLevel && $this->recordLevel != self::ALL) {
      return false;
    }

    $this->recNotice['severity'] = $severity;
    $this->recNotice['code'] = '';
    $this->recNotice['message'] = $message;
    $this->recSource['file'] = $file;
    $this->recSource['line'] = $line;
    $this->recSource['trace'] = $trace;
    $this->recSource['context'] = $context;
    $this->recClient['ip_addr'] = $_SERVER['REMOTE_ADDR'];
    $this->recClient['ip_addr'] .= isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                                 ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    $this->recClient['ip_addr'] .= isset($_SERVER['HTTP_FORWARDED'])
                                 ? $_SERVER['HTTP_FORWARDED'] : '';
    $this->recClient['ip_addr'] .= isset($_SERVER['HTTP_CLIENT_IP'])
                                 ? $_SERVER['HTTP_CLIENT_IP'] : '';
    $this->recClient['user_agent'] = isset($_SERVER['HTTP_USER_AGENT'])
                                   ? $_SERVER['HTTP_USER_AGENT'] : '';
    $this->recClient['domen_addr'] = isset($_SERVER['REMOTE_HOST'])
                                   ? $_SERVER['REMOTE_HOST'] : GetHostByAddr($_SERVER['REMOTE_ADDR']);

    if ($this->method == 'file') {
      $this->fileLog();
    } elseif ($this->method == 'mail') {
      $this->sendLog();
    } elseif ($this->method == 'xml') {
      $this->xmlLog();
    } elseif ($this->method == 'db') {
      $this->dbLog();
    }
  }

  /**
   * Zalogovani zpravy jako EMERG
   *
   * @uses   Logger::EMERG
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function emerg ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::EMERG, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako ALERT
   *
   * @uses   Logger::ALERT
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function alert ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::ALERT, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako CRIT
   *
   * @uses   Logger::CRIT
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function crit ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::CRIT, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako ERROR
   *
   * @uses   Logger::ERROR
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function error ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::ERROR, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako WARNING
   *
   * @uses   Logger::WARNING
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function warning ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::WARNING, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako NOTICE
   *
   * @uses   Logger::NOTICE
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function notice ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::NOTICE, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako INFO
   *
   * @uses   Logger::INFO
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function info ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::INFO, $message, $file, $line, $trace, $context);
  }

  /**
   * Zalogovani zpravy jako DEBUG
   *
   * @uses   Logger::DEBUG
   * @uses   Logger::log()
   * @param  string  $message  zprava k zalogovani
   * @param  string  $file     cesta k souboru, v nemz chyba nastala
   * @param  string  $line     radek, na kterem byla chyba zaznamenana
   * @param  string  $trace    nazvi funkci aktivnich v okamziku chyby - napr. pomoci $Exception->getTraceAsString()
   * @param  string  $context  pripadne dalsi kontextove informace (napr: SQL dotaz)
   * @return void
   */
  public function debug ($message, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
  {
    $this->log(Logger::DEBUG, $message, $file, $line, $trace, $context);
  }

  /**
   * Prevede typ logu na odpovidajici nazev
   * (cislo na retezec)
   *
   * Pokud je predlozeno platne cislo (dle nektere vise definovane konstanty)
   * je vracen odpovidajici nazev, jinak je vracena puvodni hodnota
   *
   * @param  mixed  $severity  hodnota patricne konstanty
   * @return string odpovidajici nazev typu logu
   */
  public static function severityToName ($severity)
  {
    $map = array(
      self::EMERG   => 'emerg',
      self::ALERT   => 'alert',
      self::CRIT    => 'critical',
      self::ERROR   => 'error',
      self::WARNING => 'warning',
      self::NOTICE  => 'notice',
      self::INFO    => 'info',
      self::DEBUG   => 'debug'
    );

    return isset($map[$severity]) ? $map[$severity] : $severity;
  }

  /**
   * Zaloguje $message do $target
   *
   * @param  string  $message   zprava pro zalogovani
   * @param  string  $target    jmeno/adresa soboru, do ktereho zpravu zalogovat
   * @param  boolean $timestamp zda pridat casovou znamku
   * @return void
   */
  public static function justLog ($message, $target = 'statement.log', $timestamp = true)
  {
    if ($timestamp) {
      $message = date('Y-m-d H:i:s') . " -- " . $message;
    }
    $message .= "\n";

    @ $file = fopen($target, 'a');
    @ fwrite($file, $message);
    @ fclose($file);
  }
}
