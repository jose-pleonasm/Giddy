<?php

/**
 * Realizuje uloziste
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage Storage
 * @version    $Id: Session.php, 2011-03-10 14:49 $
 */

Base::import('Storage/Client');

/**
 * Implementace ukladaci struktury vazane k HTTP klientovi
 * prostrednictvim session
 *
 * @package    Giddy
 * @subpackage Storage
 * @author     Josef Hrabec
 * @version    Release: 0.9.2
 * @since      Trida je pristupna od verze 0.4
 */
class Storage_Client_Session extends Storage_Client
{
  /**
   * Instance tridy
   *
   * @var object
   */
  private static $instance = NULL;

  /**
   * Nastaveni storage
   *
   * @var array
   */
  private $cfg = array();

  /**
   * Zda je session spustena (v "tomto kole")
   *
   * @var bool
   */
  private $sessionRun = false;

  /**
   * Pokud je session prave zpustena
   *
   * Se session se da pracovat i kdyz cookie neni jeste nastavena
   * proto tato pojistka pro metodu isAvailable()
   *
   * @see Storage_Session::isAvailable()
   * @var bool
   */
  private $sessionStarting = false;

  /**
   * Pokud je session prave zrusena
   *
   * S objektem se da pracovat i po prave odepsane
   * session, coz je nezadouci!
   * proto tato pojistka pro metodu isAvailable()
   *
   * @see Storage_Session::isAvailable()
   * @var bool
   */
  private $sessionDying = false;

  /**
   * Spusti JENOM session
   *
   * @uses   session_start()
   * @uses   session_set_cookie_params()
   * @uses   session_regenerate_id()
   * @return void
   */
  private function sessionStart ()
  {
    if ($this->sessionRun) {
      return;
    }
    @session_set_cookie_params($this->cfg['lifetime'], $this->cfg['path']);
    @session_start();
    @session_regenerate_id(true);
    $this->sessionRun = true;
  }

  /**
   * Inicializace
   *
   * @param  array $cfg
   */
  private function __construct ($cfg = array())
  {
    $this->cfg['lifetime'] = isset($cfg['lifetime']) ? $cfg['lifetime'] : 0;
    $this->cfg['path']     = isset($cfg['path']) ? $cfg['path'] : (dirname($_SERVER['PHP_SELF']) == DIRECTORY_SEPARATOR
                                                                    ? '/' : dirname($_SERVER['PHP_SELF']) . '/');
    $sessName = isset($cfg['sessName']) ? $cfg['sessName'] : 'PHPSESSID';
    session_name($sessName);
  }

  /**
   * Zmeni hodnotu specifikovaneho paramtru nastaveni
   *
   * @since  metoda je pristupna od verze 0.9.2
   * @param  string  $name
   * @param  mixed   $value
   * @return mixed   puvodni hodnota
   */
  public function changeCfg($name, $value)
  {
    $reinitSession = false;
    if (isset($this->cfg[$name])) {
      $buffer = $this->cfg[$name];
      $this->cfg[$name] = $value;
      $reinitSession = true;

    } elseif ($name == 'sessName') {
      $buffer = session_name();
      session_name($value);
      $reinitSession = true;

    } else {
      throw new ArgumentOutOfRangeException();
    }

    if ($reinitSession) {
      if ($this->isAvailable()) {
        $data = $this->toArray();
        $this->quit();
        $this->init();
        $this->fromArray($data);
      }
    }

    return $buffer;
  }

  /**
   * Vytvori instanci
   *
   * Realizace singletonu
   *
   * @param  array $cfg
   * @return UserStorage_Session
   */
  public static function getInstance ($cfg = array())
  {
    if (self::$instance == NULL) {
      self::$instance = new self($cfg);
    }
    return self::$instance;
  }

  /**
   * Inicializuje storage jako takovy
   *
   * @uses   session_start()
   * @uses   session_set_cookie_params()
   * @uses   session_regenerate_id()
   * @return void
   * @throws RuntimeException
   */
  public function init ()
  {
    $this->sessionDying = false;
    $this->sessionStarting = true;
    if (!ini_set('session.use_cookies', 'on')) {
      throw new RuntimeException('Doesnt use cookie');
    }
    @session_set_cookie_params($this->cfg['lifetime'], $this->cfg['path']);
    @session_start();
    @session_regenerate_id(true);
    $this->sessionRun = true;
  }

  /**
   * Ukonci storage
   *
   * @uses   $_COOKIE
   * @uses   $_SESSION
   * @uses   session_name()
   * @uses   setcookie()
   * @uses   session_destroy()
   * @return void
   */
  public function quit ()
  {
    $this->sessionStarting = false;
    $this->sessionDying = true;
    //FIXME: pokud se session nespusti, nebude k ni pristup (korektne se neodstrani)
    // pokud se ale session spusti, vytvori se i (nepouzitelne) cookie
    $this->sessionStart();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time()-42000, $this->cfg['path']);
    }
    @session_destroy();
  }

  /**
   * Zda je storage inicializovany
   * a pripaveny k pouziti
   *
   * @uses   $_COOKIE
   * @return bool
   */
  public function isAvailable ()
  {
    if ($this->sessionDying) {
      return false;
    }
    if ($this->sessionStarting) {
      return true;
    }
    return !empty($_COOKIE[session_name()]);
  }

  /**
   * Zda je zasobnik prazdny
   *
   * @uses   $_SESSION
   * @return bool
   * @throws StorageUnavailableException
   */
  public function isEmpty ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return empty($_SESSION);
  }

  /**
   * Nastavi hodnotu
   *
   * @uses   $_SESSION
   * @param  string  $key
   * @param  mixed   $item
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function put ($key, $item)
  {
    if ($key === NULL || $item === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    $_SESSION[$key] = $item;
  }

  /**
   * Nastavi hodnotu
   *
   * @uses   $_SESSION
   * @param  string  $key
   * @param  mixed   $value
   * @return bool
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function exists ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return isset($_SESSION[$key]);
  }

  /**
   * Vrati hodnotu
   *
   * @uses   $_SESSION
   * @param  string  $key
   * @return mixed
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function get ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
  }

  /**
   * Odstrani hodnotu
   *
   * @uses   $_SESSION
   * @param  string  $key
   * @return void
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function remove ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();
    unset($_SESSION[$key]);
  }

  /**
   * Vrati pocet polozek zasobniku
   *
   * @uses   $_SESSION
   * @uses   count()
   * @return integer
   * @throws StorageUnavailableException
   */
  public function count ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();
 
    return count($_SESSION);
  }

  /**
   * Vyhleda klic v zasobniku
   *
   * @uses   $_SESSION
   * @uses   array_key_exists()
   * @param  mixed   $key  hledany klic
   * @return bool          true pokud tato mapa obsahuje hledany klic, jinak false
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function containsKey ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return array_key_exists($key, $_SESSION);
  }

  /**
   * Vyhleda polozku v zasobniku
   *
   * @uses   $_SESSION
   * @param  mixed $item
   * @return bool
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function containsValue ($item)
  {
    if ($item === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return in_array($item, $_SESSION);
  }

  /**
   * Vrati klic prvniho nalezeneho specifikovaneho objektu
   *
   * @uses   $_SESSION
   * @uses   array_search()
   * @param  mixed  $item  hledany objekt
   * @return mixed  odpovidajici klic
   * @throws NullPointerException
   * @throws StorageUnavailableException
   */
  public function keyOf ($item)
  {
    if ($item === NULL) {
      throw new NullPointerException();
    }
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }
    $this->sessionStart();

    return array_search($item, $_SESSION);
  }

  /**
   * Vrati klice tohoto zasobniku
   *
   * @uses   $_SESSION
   * @return array
   * @throws StorageUnavailableException
   */
  public function getKeys ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $this->sessionStart();
    return array_keys($_SESSION);
  }

  /**
   * Vrati polozky tohoto zasobniku
   *
   * @uses   $_SESSION
   * @return array
   * @throws StorageUnavailableException
   */
  public function getValues ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $this->sessionStart();
    return array_values($_SESSION);
  }

  /**
   * Vrati cely zasobnik jako (standartni) pole
   *
   * @uses   $_SESSION
   * @return array
   * @throws StorageUnavailableException
   */
  public function toArray ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $this->sessionStart();
    return $_SESSION;
  }

  /**
   * Vycisti zasobnik (smaze data)
   *
   * @uses   $_SESSION
   * @return void
   * @throws StorageUnavailableException
   */
  public function clear ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $this->sessionStart();
    $_SESSION = array();
  }

  /**
   * Vrati iterator
   *
   * @uses   $_SESSION
   * @uses   Base::import()
   * @uses   MapIterator::__construct()
   * @uses   AbstractMap::$data
   * @return MapIterator  iterator pro prochazeni pole
   */
  public function getIterator ()
  {
    Base::import('Collections/MapIterator');
    return new MapIterator($_SESSION);
  }
}
