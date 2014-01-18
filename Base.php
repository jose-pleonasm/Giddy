<?php

/**
 * Zakladni jednotka frameworku
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Base.php, 2011-03-29 16:25 $
 */

require_once 'exceptions.php';

if (function_exists('spl_autoload_register')) {
  spl_autoload_register(array('Base', 'tryLoad'));
}


/**
 * Zakladni jednotka frameworku
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.4
 * @since      Trida je pristupna od verze 0.1
 * @static
 */
final class Base
{
  /**
   * Cesta ke tridam
   *
   * @var string
   */
  private static $frameworkRoot = '';

  /**
   * Objekt pro logovani
   *
   * @var Logger
   */
  private static $logger = NULL;

  /**
   * Vlozene hodnoty
   *
   * Slouzi jako interni uloziste hodnot pro potreby aplikace
   *
   * @var array
   */
  private static $vars = array();

  /**
   * Parsuje a interpretuje "high" import
   *
   * @param  string  $expression
   * @return mixed   pokud je uloha kompletne splnena je vraceno true,
   *                 pokud je treba import dokoncit v Base::import() je vraceno cokoliv mimo true
   * @throws ImportException
   * @since  metoda je pristupna od verze 0.9.4
   */
  private static function execImport($expression)
  {
    //$commands = explode(' ', $expression);
    $buffer = preg_split('$\s+$', $expression);
    $commands = array();
    foreach ($buffer as $item) {
      $item = trim($item);
      if (!empty($item)) {
        $commands[] = $item;
      }
    }
    if (empty($commands)) {
      throw new ImportException('Invalid expression', $expression);
    }
    if (substr($commands[0], 0, 1) == '/'
        || substr($commands[0], 1, 2) == ':' . DIRECTORY_SEPARATOR) {
      $lastSlashPos = strrpos($commands[0], '/') | strrpos($commands[0], DIRECTORY_SEPARATOR);
      $lastSlashPos++;
      $includePath = substr($commands[0], 0, $lastSlashPos);
      $commands[0] = substr($commands[0], $lastSlashPos);
    }

    // prikaz "*" (vlozi vsechny soubory s koncovkou "php" ve specifikovanem adresari)
    $index = in_array('*', $commands);
    if ($index !== false) {
      // argumenty
      $skip = array();
      if (strpos(end($commands), '[') !== false) {
        $buffer = substr(end($commands), 1, -1);
        $args = explode(',', $buffer);
        if (!empty($args)) {
          foreach ($args as $arg) {
            if (substr($arg, 0, 1) == '^') {
              $skip[] = substr($arg, 1);
            }
          }
        }
      }
      // vlozeni souboru
      $dir = isset($includePath) ? $includePath : self::$frameworkRoot;
      $dh = opendir($dir);
      while (($fileName = readdir($dh)) !== false) {
        if (substr($fileName, -4, 4) == '.php') {
          if (!in_array(substr($fileName, 0, -4), $skip)) {
            require_once $dir . $fileName;
          }
        }
      }
      closedir($dh);
      return true;
    }

    // pouze vlozeni
    if (count($commands) == 1) {
      $path = (isset($includePath) ? $includePath : self::$frameworkRoot) . $commands[0] . '.php';
      return $path;
    }

    // prikaz "as"
    $index = in_array('as', $commands);
    if ($index !== false) {
      if (!isset($commands[$index-1]) || !isset($commands[$index+1])) {
        throw new ImportException('Invalid import requirement', $expression);
      }
      $fileName = str_replace('_', DIRECTORY_SEPARATOR, $commands[$index-1]);
      $path = (isset($includePath) ? $includePath : self::$frameworkRoot) . $fileName . '.php';
      if (is_file($path) && is_readable($path)) {
        //nacteni souboru
        $fh = @fopen($path, 'r');
        $content = @fread($fh, filesize($path));
        @fclose($fh);
        //prejmenovani
        $content = preg_replace('$[cC][lL][aA][sS][sS]\s+' . $commands[$index-1] . '$', 'class ' . $commands[$index+1], $content);
        //osetreni pripadnych volani statickych vlastnosti tridy jejim jmenem
        if (strpos($content, $commands[$index-1] . '::') !== false) {
          $content = str_replace($commands[$index-1] . '::', $commands[$index+1] . '::', $content);
        }
        //osetreni pripadnych potomku
        if (stripos($content, 'extends') !== false) {
          $content = preg_replace('$\s+[eE][xX][tT][eE][nN][dD][sS]\s+' . $commands[$index-1] . '$', ' extends ' . $commands[$index+1], $content);
        }
        //osetreni pro eval
        $content = str_replace('<?php', '', $content);
        $content = str_replace('?>', '', $content);
        //interpretace kodu (misto funkce require())
        eval($content);
        return true;
      }
    }

    return false;
  }

  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * Inicializuje "jadro" frameworku
   *
   * @uses   __FILE__
   * @uses   DIRECTORY_SEPARATOR
   * @uses   dirname()
   * @uses   Base::$frameworkRoot
   */
  public static function init()
  {
    self::$frameworkRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  }

  /**
   * Nastavy adresar kde je framework
   *
   * @uses   DIRECTORY_SEPARATOR
   * @uses   dirname()
   * @uses   trim()
   * @uses   substr()
   * @uses   str_replace()
   * @uses   Base::$frameworkRoot
   * @param  string  $dir
   * @return string  vrati puvodni adresar
   */
  public static function setRoot($dir = '')
  {
    if (empty($dir)) {
      $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    } else {
      $dir = trim((string) $dir);
      if (DIRECTORY_SEPARATOR != '/') {
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
      }
      if (substr($dir, -1, 1) != DIRECTORY_SEPARATOR) {
        $dir .= DIRECTORY_SEPARATOR;
      }
    }

    $oldDir = self::$frameworkRoot;
    self::$frameworkRoot = $dir;
    return $oldDir;
  }

  /**
   * Nastavy objekt pro logovani
   *
   * @param  Logger  $logger
   */
  public static function setLogger(Logger $logger)
  {
    self::$logger = $logger;
  }

  /**
   * Vrati adresar kde je framework
   *
   * @uses   Base::$frameworkRoot
   * @return string  vrati adresar
   */
  public static function getRoot()
  {
    return self::$frameworkRoot;
  }

  /**
   * Vrati verzi Giddy frameworku
   *
   * @uses   GiddyFramework::VERSION
   * @return string
   */
  public static function getVersion()
  {
    return GiddyFramework::VERSION;
  }

  /**
   * Nastavy hodnotu
   *
   * @uses   isset()
   * @uses   Base::$vars
   * @param  string  $name
   * @param  mixed   $value
   * @return mixed
   */
  public static function setVariable($name, $value)
  {
    $buffer = isset(self::$vars[$name]) ? self::$vars[$name] : NULL;
    self::$vars[$name] = $value;
    return $buffer;
  }

  /**
   * Vrati hodnotu
   *
   * @uses   isset()
   * @uses   Base::$vars
   * @param  string  $name
   * @return mixed
   */
  public static function getVariable($name)
  {
    return isset(self::$vars[$name]) ? self::$vars[$name] : NULL;
  }

  /**
   * Nacte tridu
   *
   * @uses   DIRECTORY_SEPARATOR
   * @uses   Base::$frameworkRoot
   * @uses   strpos()
   * @uses   str_replace()
   * @uses   is_file()
   * @uses   Base::execImport()
   * @uses   System::isIncludeable()
   * @param  string  $expression  nazev tridy - soubor s tridou (bez koncovky '.php')
   * @param  boolean $high        zda pouzit komplexnejsi import (obdoba z Pythonu)
   * @return void
   * @throws ImportException  pokud nebyl soubor (dle jmena tridy) nalezen v dane ceste
   */
  public static function import($expression, $high = false)
  {
    if ($high) {
      $import = self::execImport($expression);
      // pokud je $import true, je jiz vse potrebne vykonano
      if ($import === true) {
        return;
      }
      // pokud je $import false, jde zrejme o invalidni vyraz
      if ($import === false) {
        throw new ImportException('High import failed', $expression);
      }
    } else {
      $fileName = str_replace('_', DIRECTORY_SEPARATOR, $expression);
      $import = self::$frameworkRoot . $fileName . '.php';
    }

    if (is_file($import)) {
      require_once $import;
    } elseif (System::isIncludable($import)) {
      require_once $import;
    } else {
      throw new ImportException('File ' . $import . ' missing', $expression);
    }
  }

  /**
   * Zajistuje autoloading jednotek frameworku
   *
   * @uses   DIRECTORY_SEPARATOR
   * @uses   Base::$frameworkRoot
   * @uses   str_replace()
   * @param  string  $class
   * @return void
   */
  public static function tryLoad($class)
  {
    // aliasy
    if ($class == 'gTools') {
      $class = 'tools';
    }

    $path = str_replace('_', DIRECTORY_SEPARATOR, $class);
    $include = self::$frameworkRoot . $path . '.php';
    return @include $include;
  }

  /**
   * Exception handler
   *
   * @uses   class_exists()
   * @uses   fopen()
   * @uses   fwrite()
   * @uses   fclose()
   * @uses   date()
   * @uses   Logger::crit()
   * @uses   Logger::justLog()
   * @uses   Base::$logger
   * @uses   Base::turnErrorPage()
   * @param  Exception  $e
   */
  public static function exceptionHandler(Exception $e)
  {
    if (!empty(self::$logger)) {
      self::$logger->crit($e->getCode() . ': ' . $e->getMessage(),
                          $e->getFile(),
                          $e->getLine(),
                          serialize($e->getTrace()));

    } else {
      $report = $e->getCode() . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
      if (class_exists('Logger')) {
        Logger::justLog($report);
      } else {
        @ $file = fopen('./alerts.log', 'a');
        @ fwrite($file, date('Y-m-d H:i:s') . " - " . $report . "\n");
        @ fclose($file);
      }
    }

    self::turnErrorPage();
  }

  /**
   * Error handler
   *
   * @uses   class_exists()
   * @uses   fopen()
   * @uses   fwrite()
   * @uses   fclose()
   * @uses   date()
   * @uses   Logger::crit()
   * @uses   Logger::justLog()
   * @uses   Base::$logger
   * @uses   Base::turnErrorPage()
   * @param  int    $severity  error level
   * @param  string $message   zprava
   * @param  string $file      soubor kde byla chyba vyvolana
   * @param  int    $line      radek kde byla chyba vyvolana
   * @param  array  $context   kontext chyby
   * @return bool   FALSE pro vyvolani nativniho error handleru, jinak NULL
   */
  public static function errorHandler($severity, $message, $file, $line, $context)
  {
    if ($severity !== E_ERROR && $severity !== E_USER_ERROR) {
      return true;
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

    if (!empty(self::$logger)) {
      self::$logger->crit($types[$severity] . ': ' . $message,
                          $file,
                          $line,
                          serialize($context));

    } else {
      $report = $types[$severity] . ': ' . $message . ' in ' . $file . ' on line ' . $line;
      if (class_exists('Logger')) {
        Logger::justLog($report);
      } else {
        @ $file = fopen('./alerts.log', 'a');
        @ fwrite($file, date('Y-m-d H:i:s') . " - " . $report . "\n");
        @ fclose($file);
      }
    }

    self::turnErrorPage();
  }

  /**
   * Zpracuje vyjimku
   *
   * Kvuli predchozi verzi
   *
   * @param  Exception  $e
   * @return void
   */
  public static function catchError(Exception $e)
  {
    trigger_error('This way is condemnation! Use turnErrorPage', E_USER_NOTICE);

    // nepouziva se
    if ($e instanceof ImportException) {
      $msg = 'Základní komponenta aplikace není k dispozici';
    } elseif ($e instanceof DBI_ConnectException) {
      $msg = 'Nenavázáno spojení s DB';
    } elseif ($e instanceof DBI_Exception) {
      $msg = 'Potíže při interakci s DB';
    } elseif ($e instanceof RuntimeException) {
      $msg = 'Při běhu aplikace se vyskytla neočekávaná chyba';
    } elseif ($e instanceof LogicException) {
      $msg = 'Aplikace obsahuje závažnou chybu';
    } else {
      $msg = 'V aplikaci se vyskytla chyba';
    }

    self::turnErrorPage();
  }

  /**
   * Vypise error page
   *
   * @uses   headers_sent()
   * @uses   header()
   * @uses   is_file()
   * @uses   include()
   * @param  string  $template  cesta k sablone pro tisk
   * @return void
   */
  public static function turnErrorPage($template = 'templates/Base.errorscreen.phtml')
  {
    if (!headers_sent()) {
      header('HTTP/1.1 500 Internal Server Error');
    }

    $errorPagePrinted = false;
    if (is_file($template)) {
      $errorPagePrinted = true;
      include($template);
    }

    if (!$errorPagePrinted) {
      include('templates/Base.errorscreen.phtml');
    }
    if (!$errorPagePrinted) {
      echo 'The web application is now unavailable';
    }

    exit;
  }
}
