<?php

/**
 * Realizuje uloziste
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage Storage
 * @version    $Id: Cookie.php, 2011-03-10 14:49 $
 */

Base::import('Storage/Client');
Base::import('HttpInput');

/**
 * Implementace ukladaci struktury vazane k HTTP klientovi
 * prostrednictvim cookie
 *
 * @package    Giddy
 * @subpackage Storage
 * @author     Josef Hrabec
 * @version    Release: 0.9.3
 * @since      Trida je pristupna od verze 0.4
 */
class Storage_Client_Cookie extends Storage_Client
{
  const COOKIE_VERSION = '1.0';

  /**
   * Vlastni polozky zasobniku
   *
   * Slouzi pro vlastni potreby zasobniku
   *
   * @var array
   */
  private static $privateItems = array('_version');

  /**
   * Informace o behu zasobniku
   *
   * @var array
   */
  private $run;

  /**
   * Nastaveni Storage
   *
   * @var array
   */
  private $cfg = array();

  /**
   * Nazev cookie (relace)
   *
   * @var array
   */
  private $cookieName;

  /**
   * Cache
   *
   * @var array
   */
  private $cache;

  /**
   * Ulozi data do cookie
   *
   * @param  array  $data
   * @return void
   */
  private function saveData ($data)
  {
    $this->cache = $data;
    setcookie($this->cookieName, serialize($data), $this->cfg['lifetime'], $this->cfg['path']);
  }

  /**
   * Nacte data z cookie (pripadne jen z cache)
   *
   * @return array
   */
  private function loadData ()
  {
    if (!empty($this->cache)) {
      return $this->cache;
    }
    $this->cache = unserialize(HttpInput::cookie($this->cookieName));
    return $this->cache;
  }

  /**
   * Inicializace
   *
   * @param  array $cfg
   */
  public function __construct ($cfg = array())
  {
    $this->cfg['lifetime'] = isset($cfg['lifetime']) ? $cfg['lifetime'] : 0;
    $this->cfg['path']     = isset($cfg['path']) ? $cfg['path'] : (dirname($_SERVER['PHP_SELF']) == DIRECTORY_SEPARATOR
                                                                    ? '/' : dirname($_SERVER['PHP_SELF']) . '/');
    $this->cookieName = isset($cfg['sessName']) ? $cfg['sessName'] : 'CSC';
  }

  /**
   * Zmeni hodnotu specifikovaneho paramtru nastaveni
   *
   * @since  metoda je pristupna od verze 0.9.3
   * @param  string  $name
   * @param  mixed   $value
   * @return mixed   puvodni hodnota
   */
  public function changeCfg($name, $value)
  {
    $reinitCookie = false;
    if (isset($this->cfg[$name])) {
      $buffer = $this->cfg[$name];
      $this->cfg[$name] = $value;
      $reinitCookie = true;

    } elseif ($name == 'sessName') {
      $buffer = $this->cookieName;
      $this->cookieName = $value;
      $reinitCookie = true;

    } else {
      throw new ArgumentOutOfRangeException();
    }

    if ($reinitCookie) {
      if ($this->isAvailable()) {
        $data = $this->loadData();
        $this->quit();
        $this->init();
        $this->saveData($data);
      }
    }

    return $buffer;
  }

  /**
   * Inicializuje storage jako takovy
   *
   * Vytvori Cookie
   *
   * @return void
   */
  public function init ()
  {
    if (empty($_COOKIE[$this->cookieName])) {
      $data['_version'] = self::COOKIE_VERSION;
      $this->saveData($data);

      $this->run['dying'] = false;
      $this->run['starting'] = true;
    }
  }

  /**
   * Zda je storage inicializovany
   * a pripaveny k pouziti
   *
   * @return bool
   */
  public function isAvailable ()
  {
    if ($this->run['dying']) {
      return false;
    }
    if ($this->run['starting']) {
      return true;
    }
    if (empty($_COOKIE[$this->cookieName])) {
      return false;
    } else {
      $data = $this->loadData();
      if ($data['_version'] == self::COOKIE_VERSION) {
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * Zda je zasobnik prazdny
   *
   * @return bool
   * @throws StorageUnavailableException
   */
  public function isEmpty ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    unset($data['_version']);
    return empty($data);
  }

  /**
   * Nastavi hodnotu
   *
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
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    $data[$key] = $item;
    $this->saveData($data);
  }

  /**
   * Nastavi hodnotu
   *
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
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    return isset($data[$key]);
  }

  /**
   * Vrati hodnotu
   *
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
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    return isset($data[$key]) ? $data[$key] : NULL;
  }

  /**
   * Odstrani hodnotu
   *
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
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    unset($data[$key]);
    $this->saveData($data);
  }

  /**
   * Vrati pocet polozek zasobniku
   *
   * @uses   Storage_Client_Cookie::loadData()
   * @uses   count()
   * @return integer
   * @throws StorageUnavailableException
   */
  public function count ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    return count($data) - count(self::$privateItems);
  }

  /**
   * Vyhleda klic v zasobniku
   *
   * @uses   Storage_Client_Cookie::loadData()
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
      throw new StorageUnavailableException('Storage is not available');
    }
    $data = $this->loadData();

    return array_key_exists($key, $data);
  }

  /**
   * Vyhleda polozku v zasobniku
   *
   * @uses   Storage_Client_Cookie::loadData()
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
      throw new StorageUnavailableException('Storage is not available');
    }
    $data = $this->loadData();

    return in_array($item, $data);
  }

  /**
   * Vrati klic prvniho nalezeneho specifikovaneho objektu
   *
   * @uses   array_search()
   * @uses   Storage_Client_Cookie::loadData()
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
      throw new StorageUnavailableException('Storage is not available');
    }
    $data = $this->loadData();

    return array_search($item, $data);
  }

  /**
   * Vrati klice tohoto zasobniku
   *
   * @uses   Storage_Client_Cookie::loadData()
   * @return array
   * @throws StorageUnavailableException
   */
  public function getKeys ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    unset($data['_version']);
    return array_keys($data);
  }

  /**
   * Vrati polozky tohoto zasobniku
   *
   * @uses   Storage_Client_Cookie::loadData()
   * @return array
   * @throws StorageUnavailableException
   */
  public function getValues ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    unset($data['_version']);
    return array_values($data);
  }

  /**
   * Vrati cely zasobnik jako (standartni) pole
   *
   * @uses   Storage_Client_Cookie::loadData()
   * @return array
   * @throws StorageUnavailableException
   */
  public function toArray ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data = $this->loadData();
    unset($data['_version']);
    return $data;
  }

  /**
   * Vrati iterator
   *
   * @uses   Storage_Client_Cookie::loadData()
   * @uses   Base::import()
   * @uses   MapIterator::__construct()
   * @uses   AbstractMap::$data
   * @return MapIterator  iterator pro prochazeni pole
   */
  public function getIterator ()
  {
    Base::import('Collections/MapIterator');
    $data = $this->loadData();
    unset($data['_version']);
    return new MapIterator($data);
  }

  /**
   * Vycisti zasobnik (smaze data)
   *
   * @return void
   * @throws StorageUnavailableException
   */
  public function clear ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException('Storage is not available');
    }

    $data['_version'] = self::COOKIE_VERSION;
    $this->saveData($data);
  }

  /**
   * Ukonci storage
   *
   * @return void
   */
  public function quit ()
  {
    $this->run['dying'] = true;
    $this->run['starting'] = false;

    setcookie($this->cookieName, '', time() - 60, $this->cfg['path']);
  }
}
