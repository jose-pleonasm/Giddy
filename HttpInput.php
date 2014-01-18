<?php

/**
 * HttpRequest
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: HttpInput.php, 2011-02-09 10:53 $
 */

Base::import('Common');

/**
 * Reprezentuje HTTP pozadavek
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8
 * @since      Trida je pristupna od verze 0.1
 */
class HttpInput extends Common
{
  /**
   * Nazev zdroje dat [_GET|_POST|_COOKIE|_REQUEST|_SERVER]
   *
   * @var string
   */
  protected $source;

  /**
   * Zda je zdroj upravovan fci pro osetreni metaznaku
   *
   * @var bool
   */
  protected $gpc;

  /**
   * Osetri vstupni data
   *
   * @uses   is_array()
   * @uses   urlDecode()
   * @uses   trim()
   * @uses   stripSlashes()
   * @uses   HttpInput::handleArray()
   * @param  mixed   $value     vstupni data urcena ke zpracovani
   * @param  string  $slashes   zda odstranit lomitka
   * @param  boolean $urlDecode zda osetrit fci urlDecode (vstup z get)
   * @return mixed   osetrena data
   */
  protected static function handleValue ($value, $strip = false, $urlDecode = false)
  {
    if (is_array($value)) {
      return self::handleArray($value, $strip, $urlDecode);
    }
    if ($urlDecode) {
      $value = urlDecode($value);
    }
    $value = trim($value);
    if ($strip) {
      $value = stripSlashes($value);
    }

    return $value;
  }

  /**
   * Osetri vstupni data (pole)
   *
   * @uses   is_array()
   * @uses   HttpInput::handleValue()
   * @uses   HttpInput::handleArray()
   * @param  array   $data      vstupni data urcena ke zpracovani
   * @param  string  $slashes   zda odstranit lomitka
   * @param  boolean $urlDecode zda osetrit fci urlDecode (vstup z get)
   * @return array   osetrena data
   * @throws InvalidArgumentException()
   */
  protected static function handleArray ($array, $strip = false, $urlDecode = false)
  {
    if (!is_array($array)) {
      throw new InvalidArgumentException();
    }

    foreach ($array as $key => $value) {
      //musi se osetrit i pripadny klic
      $key = self::handleValue($key, $strip);
      if (is_array($value)) {
        $values[$key] = self::handleArray($value, $strip, $urlDecode);
      } else {
        $values[$key] = self::handleValue($value, $strip, $urlDecode);
      }
    }

    return $values;
  }

  /**
   * Inicializace
   *
   * @uses   get_magic_quotes_gpc()
   * @uses   HttpInput::$source
   * @uses   HttpInput::$gpc
   * @param  string  $source  fixni urceni zdroje
   * @throws InvalidArgumentException()
   */
  public function __construct ($source)
  {
    if ($source == 'get') {
      $this->source = '_GET';
    } elseif ($source == 'post') {
      $this->source = '_POST';
    } elseif ($source == 'cookie') {
      $this->source = '_COOKIE';
    } elseif ($source == 'request') {
      $this->source = '_REQUEST';
    } elseif ($source == 'server') {
      $this->source = '_SERVER';
    } else {
      throw new InvalidArgumentException();
    }

    $this->gpc = ($this->source != '_SERVER') && get_magic_quotes_gpc();
  }

  /**
   * Vrati specifikovanou hodnotu
   *
   * @uses   HttpInput::getValue()
   * @param  string  $name  nazev/klic hodnoty
   * @return mixed
   */
  public function __get ($name)
  {
    return $this->getValue($name);
  }

  /**
   * Zjisti zda je hodnota nastavena
   *
   * @uses   isset()
   * @param  string  $name
   * @return bool
   */
  public function exist ($name)
  {
    return isset($GLOBALS[$this->source][$name]);
  }

  /**
   * Zjisti zda neni hodnota prazdna
   *
   * @uses   empty()
   * @param  string  $name
   * @return bool
   */
  public function isEmpty ($name)
  {
    return empty($GLOBALS[$this->source][$name]);
  }

  /**
   * Vrati specifikovanou hodnotu
   *
   * Vrati konkretni (prostrednictvim metody objektu)
   * nebo vychozi (prostrednictvim druheho parametru) hodnotu
   *
   * @uses   isset()
   * @uses   strToUpper()
   * @uses   HttpInput::$source
   * @uses   HttpInput::$gpc
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev/klic hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public function getValue ($name, $default = NULL)
  {
    if ($this->source == '_SERVER') {
      $name = 'HTTP_' . strToUpper($name);
    }
    if (isset($GLOBALS[$this->source][$name])) {
      $value = $GLOBALS[$this->source][$name];
    } else {
      return $default;
    }
    $urlDecode = $this->source == '_GET' ? true : false;

    return self::handleValue($value, $this->gpc, $urlDecode);
  }

  /**
   * Vrati vsechny data specifikovaneho zdroje
   *
   * @return array
   */
  public function getValues ()
  {
    if (empty($GLOBALS[$this->source])) {
      return NULL;
    }
    $values = array();
    $urlDecode = $this->source == '_GET' ? true : false;
    foreach ($GLOBALS[$this->source] as $key => $value) {
      $value = self::handleValue($value, $this->gpc, $urlDecode);
      $values[$key] = $value;
    }
    return $values;
  }

  /**
   * Ziskani konkretni hodnoty z $_GET
   *
   * Vrati specifikovanou hodnotu metody (mimo objekt)
   *
   * @uses   isset()
   * @uses   get_magic_quotes_gpc()
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev pozadovane hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public static function get ($name, $default = NULL)
  {
    if (isset($_GET[$name])) {
      return self::handleValue($_GET[$name], get_magic_quotes_gpc(), true);
    } else {
      return $default;
    }
  }

  /**
   * Ziskani konkretni hodnoty z $_POST
   *
   * Vrati specifikovanou hodnotu metody (mimo objekt)
   *
   * @uses   isset()
   * @uses   get_magic_quotes_gpc()
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev pozadovane hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public static function post ($name, $default = NULL)
  {
    if (isset($_POST[$name])) {
      return self::handleValue($_POST[$name], get_magic_quotes_gpc());
    } else {
      return $default;
    }
  }

  /**
   * Ziskani konkretni hodnoty z $_COOKIE
   *
   * Vrati specifikovanou hodnotu metody (mimo objekt)
   *
   * @uses   isset()
   * @uses   get_magic_quotes_gpc()
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev pozadovane hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public static function cookie ($name, $default = NULL)
  {
    if (isset($_COOKIE[$name])) {
      return self::handleValue($_COOKIE[$name], get_magic_quotes_gpc());
    } else {
      return $default;
    }
  }

  /**
   * Ziskani konkretni hodnoty z $_REQUEST
   *
   * Vrati specifikovanou hodnotu metody (mimo objekt)
   *
   * @uses   isset()
   * @uses   get_magic_quotes_gpc()
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev pozadovane hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public static function request ($name, $default = NULL)
  {
    if (isset($_REQUEST[$name])) {
      return self::handleValue($_REQUEST[$name], get_magic_quotes_gpc());
    } else {
      return $default;
    }
  }

  /**
   * Ziskani konkretni hodnoty z $_SERVER (v HTTP mnozine)
   *
   * Vrati specifikovanou hodnotu metody (mimo objekt)
   *
   * @uses   isset()
   * @uses   strToUpper()
   * @uses   HttpInput::handleValue()
   * @param  string  $name     nazev pozadovane hodnoty
   * @param  mixed   $default  vychozi hodnota pokud neni pozadovana hodnota nastavena
   * @return mixed   pozadovana hodnota
   */
  public static function server ($name, $default = NULL)
  {
    $name = 'HTTP_' . strToUpper($name);
    if (isset($_SERVER[$name])) {
      return self::handleValue($_SERVER[$name], false);
    } else {
      return $default;
    }
  }

  /**
   * Vrati IP adresu klienta
   *
   * @param  bool  $full
   * @return string
   */
  public static function getRemoteAddress($full = true)
  {
    $ip = NULL;
    if (isset($_SERVER['REMOTE_ADDR'])) {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($full) {
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip .= '@' . $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip .= '@' . $_SERVER['HTTP_CLIENT_IP'];
      }
    }
    return $ip;
  }

  /**
   * Vrati domenu hostitele klienta
   *
   * @return string
   */
  public static function getRemoteHost()
  {
    if (!isset($_SERVER['REMOTE_HOST'])) {
      if (!isset($_SERVER['REMOTE_ADDR'])) {
        return NULL;
      }
      $_SERVER['REMOTE_HOST'] = getHostByAddr($_SERVER['REMOTE_ADDR']);
    }

    return $_SERVER['REMOTE_HOST'];
  }
}
