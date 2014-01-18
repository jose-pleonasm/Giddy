<?php

/**
 * Nastroj pro ladeni
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Debug.php, 2011-08-12 13:27 $
 */

/**
 * Nastroj pro ladeni
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8.1
 * @since      Trida je pristupna od verze 0.1
 * @static
 */
class Debug
{
  /**
   * Urcuje zda je rezim debug zapnuty
   *
   * @var bool
   */
  private static $enabled;

  /**
   * Zda prave probiha osetreni vyjimky, pripadne chyby
   *
   * @var bool
   */
  private static $handlerProcess = false;

  /**
   * Puvodni nastaveni error_reporting
   *
   * @var int
   */
  private static $originErrReport = NULL;

  /**
   * Seznam IP adres specifikuje NEprodukcni mod
   *
   * @var array
   */
  private static $allowedIps = array('127.0.0.1');

  /**
   * Seznam IP adres ktere jsou blokovany
   *
   * @var array
   */
  private static $disallowedIps = array();

  private static $storage;

  /**
   * how many nested levels of array/object properties display {@link Debug::dump()}
   *
   * @var int
   */
  public static $maxDepth = 5;

  /**
   * how long strings display {@link Debug::dump()}
   *
   * @var int
   */
  public static $maxLen = 500;

  /**
   * sensitive keys not displayed by {@link Debug::dump()}
   *
   * @var array
   */
  public static $keysToHide = array('password', 'passwd', 'pass', 'pwd', 'creditcard', 'credit card', 'pin');

  /**
   * cil logovani
   *
   * @var string
   */
  public static $logFile = './debug.log';

  /**
   * Zaloguje zpravu
   *
   * @uses   class_exists()
   * @uses   fopen()
   * @uses   fwrite()
   * @uses   fclose()
   * @uses   date()
   * @uses   Logger::justLog()
   * @uses   Debug::$logFile
   * @param  string  $message  zprava
   * @return void
   */
  private static function log ($message)
  {
    if (class_exists('Logger')) {
      Logger::justLog($message, self::$logFile);
    } else {
      @$file = fopen(self::$logFile, 'a');
      @fwrite($file, date('Y-m-d H:i:s') . " - " . $message . "\n");
      @fclose($file);
    }
  }

  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct ()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * Zda skript bezi v produkcnim modu
   *
   * @return bool
   */
  public static function isProductionMode ()
  {
    return !in_array($_SERVER['REMOTE_ADDR'], self::$allowedIps);
  }

  /**
   * Zda skript bezi v povolenem kontextu
   *
   * @return bool
   */
  public static function isAllowedContext ()
  {
    return !in_array($_SERVER['REMOTE_ADDR'], self::$disallowedIps);
  }

  /**
   * Zapne ladeni
   *
   * @uses   Debug::$enabled
   * @return void
   */
  public static function enable ()
  {
    self::$enabled = true;

    self::$originErrReport = ini_get('error_reporting');
    error_reporting(0);

    set_exception_handler(array(__CLASS__, 'exceptionHandler'));
    set_error_handler(array(__CLASS__, 'errorHandler'));
  }

  /**
   * Zda je zapnut Debug
   *
   * @return bool
   */
  public static function isEnabled()
  {
    return self::$enabled;
  }

  /**
   * Vypise error page
   *
   * @param  mixed $e
   * @return void
   */
  public static function printErrorScreen ($e)
  {
    if (is_object($e)) {
      $severity = method_exists($e, 'getSeverity') ? $e->getSeverity() : NULL;
      $class = get_class($e);
      $code = $e->getCode();
      $msg = $e->getMessage();
      $file = $e->getFile();
      $line = $e->getLine();
      $trace = $e->getTrace();
      $reflection = new ReflectionClass($class);
    } else {
      $type = $e['type'];
      $code = $e['severity'];
      $msg = $e['message'];
      $file = $e['file'];
      $line = $e['line'];
      $trace = $e['trace'];
    }

    require_once 'templates/Debug.errorscreen.phtml';
  }

  /**
   * Ladici Exception handler
   *
   * @param  Exception $e
   * @return void
   */
  public static function exceptionHandler (Exception $e)
  {
    self::$handlerProcess = true;

    if (!self::isAllowedContext()) {
      /*
      ob_start();
      self::printErrorScreen($e);
      $buffer = ob_get_clean();
      @$file = fopen('./debug-' . date('Y-m-d_H-i-s') . '.html', 'w');
      @fwrite($file,  $buffer);
      @fclose($file);
      */
      self::log($e->getCode() . ': ' . $e->getMessage()
              . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
      Base::turnErrorPage();
    }

    if (!headers_sent()) {
      header('HTTP/1.1 500 Internal Server Error');
    }

    self::printErrorScreen($e);
    exit;
  }

  /**
   * Ladici Error handler
   *
   * @param  int    $severity  error level
   * @param  string $message   zprava
   * @param  string $file      soubor kde byla chyba vyvolana
   * @param  int    $line      radek kde byla chyba vyvolana
   * @param  array  $context   kontext chyby
   * @return bool   FALSE pro vyvolani nativniho error handleru, jinak NULL
   */
  public static function errorHandler ($severity, $message, $file, $line, $context)
  {
    if (self::$handlerProcess) {
      return NULL;
    }

    @$types = array(
      E_ERROR => 'Error',
      E_USER_ERROR => 'User error',
      E_WARNING => 'Warning',
      E_USER_WARNING => 'User warning',
      E_NOTICE => 'Notice',
      E_USER_NOTICE => 'User notice',
      E_STRICT => 'Strict standards',
      E_DEPRECATED => 'Deprecated',
      E_USER_DEPRECATED => 'User deprecated',
    );

    if (!self::isAllowedContext()) {
      self::log($types[$severity] . ': ' . $message
              . ' in ' . $file . ' on line ' . $line);
      Base::turnErrorPage();
    }

    $info = array(
      'type' => $types[$severity],
      'severity' => $severity,
      'message' => $message,
      'file' => $file,
      'line' => $line,
      'context' => $context,
      'trace' => NULL
    );

    if ($severity === E_ERROR || $severity === E_USER_ERROR) {
      throw new ErrorException($message, 0, $severity);
    }

    error_reporting(self::$originErrReport);
    return false;
  }

  /**
   * Stopky
   *
   * @param  string  $name  identifikator
   * @return integer ubehle sekundy
   */
  public static function timer ($name = 'default')
  {
    static $time = array();
    $now = microtime(true);
    $result = isset($time[$name]) ? $now - $time[$name] : 0;
    $time[$name] = $now;
    return $result;
  }

  /**
   * Internal dump() implementation.
   *
   * @uses   ENT_NOQUOTES
   * @uses   Debug::$maxLen
   * @uses   Debug::$maxDepth
   * @uses   Debug::$keysToHide
   * @uses   is_bool()
   * @uses   is_int()
   * @uses   is_float()
   * @uses   is_string()
   * @uses   is_array()
   * @uses   is_object()
   * @uses   is_resource()
   * @uses   get_class()
   * @uses   get_resource_type()
   * @uses   strlen()
   * @uses   substr()
   * @uses   str_repeat()
   * @uses   strrpos()
   * @uses   strtolower()
   * @uses   htmlentities()
   * @uses   count()
   * @uses   in_array()
   * @uses   array_pop()
   * @uses   uniqid()
   * @param  mixed   $var   variable to dump
   * @param  int     $level current recursion level
   * @return string  dump
   */
  public static function dump ($var, $level = 0)
  {
    if (is_bool($var)) {
      return "<span>bool</span>(" . ($var ? 'true' : 'false') . ")\n";

    } elseif ($var === NULL) {
      return "<span>NULL</span>\n";

    } elseif (is_int($var)) {
      return "<span>int</span>($var)\n";

    } elseif (is_float($var)) {
      return "<span>float</span>($var)\n";

    } elseif (is_string($var)) {
      if (self::$maxLen && (strlen($var) > self::$maxLen)) {
        $s = htmlentities(substr($var, 0, self::$maxLen), ENT_NOQUOTES) . ' ... ';
      } else {
        $s = htmlentities($var, ENT_NOQUOTES);
      }
      return "<span>string</span>(" . strlen($var) . ") \"$s\"\n";

    } elseif (is_array($var)) {
      $s = "<span>array</span>(" . count($var) . ") {\n";
      $space = str_repeat('  ', $level);

      static $marker;
      if ($marker === NULL) $marker = uniqid("\x00", TRUE);
      if (isset($var[$marker])) {
        $s .= "$space  *RECURSION*\n";

      } elseif ($level < self::$maxDepth || !self::$maxDepth) {
        $var[$marker] = 0;
        foreach ($var as $k => &$v) {
          if ($k === $marker) continue;
          $s .= "$space  " . (is_int($k) ? $k : "\"$k\"") . " => ";
          if (self::$keysToHide && is_string($v) && in_array(strtolower($k), self::$keysToHide)) {
            $s .= "<span>string</span>(?) <i>*** hidden ***</i>\n";
          } else {
            $s .= self::dump($v, $level + 1);
          }
        }
        unset($var[$marker]);
      } else {
        $s .= "$space  ...\n";
      }
      return $s . "$space}\n";

    } elseif (is_object($var)) {
      $arr = (array) $var;
      $s = "<span>object</span>(" . get_class($var) . ") (" . count($arr) . ") {\n";
      $space = str_repeat('  ', $level);

      static $list = array();
      if (in_array($var, $list, TRUE)) {
        $s .= "$space  *RECURSION*\n";

      } elseif ($level < self::$maxDepth || !self::$maxDepth) {
        $list[] = $var;
        foreach ($arr as $k => &$v) {
          $m = '';
          if ($k[0] === "\x00") {
            $m = $k[1] === '*' ? ' <span>protected</span>' : ' <span>private</span>';
            $k = substr($k, strrpos($k, "\x00") + 1);
          }
          $s .= "$space  \"$k\"$m => ";
          if (self::$keysToHide && is_string($v) && in_array(strtolower($k), self::$keysToHide)) {
            $s .= "<span>string</span>(?) <i>*** hidden ***</i>\n";
          } else {
            $s .= self::dump($v, $level + 1);
          }
        }
        array_pop($list);
      } else {
        $s .= "$space  ...\n";
      }
      return $s . "$space}\n";

    } elseif (is_resource($var)) {
      return "<span>resource of type</span>(" . get_resource_type($var) . ")\n";

    } else {
      return "<span>unknown type</span>\n";
    }
  }

  /**
   * Vrati nazev typu hodnoty
   *
   * @param  mixed  $value
   * @return string
   */
  public static function getSqlValueType ($value)
  {
    //TODO: int, float atd. moc nefunguje
    if (is_bool($value)) {
      return 'bool';
    } elseif ($value === NULL) {
      return 'null';
    } elseif (strtoupper($value) == 'NULL') {
      return 'null';
    } elseif (is_int($value)) {
      return 'int';
    } elseif (is_float($value)) {
      return 'float';
    } elseif (is_string($value)) {
      return 'string';
    } else {
      return 'unknow';
    }
  }

  /**
   * SQL var dump
   *
   * @param  mixed  $var
   * @return string
   */
  public static function dumpSqlVar ($var)
  {
    if (strpos($var, '\'') === 0 || strpos($var, '"') === 0) {
      if (self::$maxLen && (strlen($var) > self::$maxLen)) {
        $s = htmlentities(substr($var, 0, self::$maxLen), ENT_NOQUOTES) . ' ... ';
      } else {
        $s = htmlentities($var, ENT_NOQUOTES);
      }
      return "<span>string</span>(" . strlen($var) . ") \"$s\"\n";

    } elseif ($var == 'NULL') {
      return "<span>NULL</span>\n";

    } elseif (strpos($var, '.') !== false) {
      return "<span>float</span>($var)\n";

    } else {
      return "<span>int</span>($var)\n";
    }
  }

  /**
   * SQL query dump
   *
   * @param  string  $sql
   * @param  bool    $ident
   * @return string
   */
  public static function dumpSql ($sql, $ident = false)
  {
    if (empty(self::$storage['numDumpSql'])) {
      self::$storage['numDumpSql'] = 0;
    }
    self::$storage['numDumpSql']++;

    $sql = trim($sql);

    if (preg_match("/^(select)\s+/i", $sql)) {
      //TODO: select

    } elseif (preg_match("/^(insert)\s+/i", $sql)) {
      $unmatchedCC = false;

      preg_match_all("/INSERT\s+INTO\s+`?([a-z][a-z0-9\_]{0,100})`?\s+(\(\s*(`?([a-z0-9\_]+)`?\s*,?\s*)+\))?\s+VALUES\s+\(\s*((('([^']*)'|\\\"([^\\\"]*)\\\"|[0-9]+|[0-9]+\.[0-9]+|NOW\(\)|NULL)\s*,?\s*)+)\);?/i", $sql, $matches);
      if (empty($matches[0])) {
        return '<p>Chyba parsovani prikazu <code>INSERT</code></p>';
      }

      $table = trim($matches[1][0]);

      $bufferS = trim($matches[2][0]);
      $bufferS = substr($bufferS, 1, -1);
      $buffer  = explode(',', $bufferS);
      $columns = array();
      foreach ($buffer as $column) {
        $column = trim($column);
        if (stripos($column, '`') === 0) {
          $column = substr($column, 1, -1);
        }
        $columns[] = $column;
      }

      $bufferS = trim($matches[5][0]);
      $buffer = preg_split("/('[^']*'|\\\"[^\\\"]*\\\"|[0-9]+|[0-9]+\.[0-9]+|NOW\(\)|NULL)\s*,\s*/i", $bufferS, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
      $values = array();
      for ($i = 0; $i < count($buffer); $i++) {
        $value = trim($buffer[$i]);
        $key = !empty($columns[$i]) ? $columns[$i] : '#' . ($i+1);
        @$values[$key] = $value;
      }
      if (count($values) != count($columns)) {
        $unmatchedCC = true;
      }

      $s  = '<style type="text/css">';
      $s .= '.giddy_debug_dumpSql .ident {background: #eee; margin: 0 0 1em 0;}';
      $s .= '.giddy_debug_dumpSql .ident h2 {font-size: 1em; margin: 0 0 0 0;}';
      $s .= '.giddy_debug_dumpSql .ident p {margin: 0 0 0 0;}';
      $s .= '.giddy_debug_dumpSql th {text-align: left;}';
      $s .= '.giddy_debug_dumpSql .null {color: #3465a4;}';
      $s .= '.giddy_debug_dumpSql .string {color: #cc0000;}';
      $s .= '.giddy_debug_dumpSql .int {color: #4e9a06;}';
      $s .= '.giddy_debug_dumpSql .float {color: #f57900;}';
      $s .= '.giddy_debug_dumpSql .bool {color: #75507b;}';
      $s .= '</style>';
      $s .= '<div class="giddy_debug_dumpSql">';
      if ($ident) {
        $s .= '<div class="ident">';
        $s .= '<h2 id="dumpSql' . self::$storage['numDumpSql'] . '">#' . self::$storage['numDumpSql'] . '</h2>';
        $s .= '<p>' . $sql . '</p>';
        $s .= '</div>';
      }
      $s .= '<table>';
      $s .= '<tr>';
      $s .= '<th>';
      $s .= 'Query:';
      $s .= '</th>';
      $s .= '<td>';
      $s .= 'INSERT';
      $s .= '</td>';
      $s .= '</tr>';
      $s .= '<tr>';
      $s .= '<th>';
      $s .= 'Table:';
      $s .= '</th>';
      $s .= '<td>';
      $s .= $table;
      $s .= '</td>';
      $s .= '</tr>';
      $s .= '<tr>';
      $s .= '<th>';
      $s .= 'Values:';
      $s .= '</th>';
      $s .= '<td>';
      $s .= '<table>';
      foreach ($values as $column => $value) {
        $s .= '<tr>';
        $s .= '<th>';
        $s .= $column;
        $s .= '</th>';
        $s .= '<td class="' . self::getSqlValueType($value) . '">';
        $s .= $value;
        $s .= '</td>';
        $s .= '</tr>';
      }
      $s .= '</table>';
      if ($unmatchedCC) {
       $s .= '<p><em>Počet sloupců nesouhlásí s počtem zadaných hodnot!</em></p>';
      }
      $s .= '</td>';
      $s .= '</tr>';
      $s .= '</table>';
      $s .= '</div>';

      return $s;

    } else {
      throw new InvalidArgumentException('Unsupported SQL query');
    }
  }
}
