<?php

/**
 * LiveUser
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: LiveUser.php, 2011-03-10 14:49 $
 */

Base::import('Common');
Base::import('Storage/Client');

/**
 * Reprezentuje aktualniho uzivatele
 *
 * Umoznuje udruzeni stavu mezi jednotlivymi
 * HTTP pozadavky
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.3
 * @since      Trida je pristupna od verze 0.1
 */
class LiveUser extends Common
{
  const ALL = NULL;

  const ROLE_ANONYMOUS  = 0;
  const ROLE_USER       = 1;
  const ROLE_SUPERUSER  = 2;
  const ROLE_ADMIN      = 3;
  const ROLE_SUPERADMIN = 4;

  const RIGHT_VIEW   = 1; // pravo na zobrazeni
  const RIGHT_EDIT   = 2; // pravo na editaci, implikuje LiveUser::RIGHT_VIEW
  const RIGHT_REMOVE = 3; // pravo na mazani, implikuje LiveUser::RIGHT_EDIT

  /**
   * Prefix pouzivany v Storage pro tuto tridu
   *
   * @var string
   */
  protected static $prefix = 'liveUser_';

  /**
   * Postaveni uzivatele
   *
   * @var integer
   */
  protected $role = self::ROLE_ANONYMOUS;

  /**
   * Prava uzivatele
   *
   * @var array
   */
  protected $privileges;

  /**
   * Prostredek pro realizovani stavu mezi HTTP pozadavky
   *
   * @var Storage_Client
   */
  protected $storage;

  /**
   * Inicializuje objekt
   *
   * @param  mixed   $method  metoda ukladani stavu mezi jednotlivymi HTTP pozadavky
   * @param  array   $cfg     nastaveni
   */
  public function __construct ($method = 'session', $cfg = array())
  {
    if ($method instanceof Storage_Client) {
      $this->storage = $method;
    } else {
      $this->storage = Storage_Client::factory($method, $cfg);
    }

    if ($this->storage->isAvailable()) {
      $this->role       = $this->storage->exists(self::$prefix . 'role')
                        ? $this->storage->get(self::$prefix . 'role') : self::ROLE_ANONYMOUS;
      $this->privileges = $this->storage->exists(self::$prefix . 'privileges')
                        ? $this->storage->get(self::$prefix . 'privileges') : NULL;
    }
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
    return $this->storage->changeCfg($name, $value);
  }

  /**
   * Zaznamena prihlaseni - vytvori relaci
   *
   * @param  integer $id
   * @param  integer $role
   * @param  array   $privileges
   * @return void
   */
  public function login ($id, $role = self::ROLE_USER, $privileges = NULL)
  {
    if (!$this->storage->isAvailable()) {
      $this->storage->init();
    }
    $this->storage->put(self::$prefix . 'userId', $id);
    $this->role = $role;
    $this->storage->put(self::$prefix . 'role', $this->role);
    if (is_array($privileges)) {
      $this->privileges = $privileges;
      $this->storage->put(self::$prefix . 'privileges', $this->privileges);
    }
  }

  /**
   * Odhlasi - relaci bud ponecha nebo zrusi podle parametru $totally
   *
   * @param  boolean $totally  ukoncit i relaci (s pripadnymi ulozenymi daty)
   * @return void
   */
  public function logout ($totally = false)
  {
    $this->role = self::ROLE_ANONYMOUS;
    $this->privileges = NULL;

    if ($totally) {
      $this->storage->quit();
    } else {
      // vyprazdneni hodnot reprezentujicich prihlaseni
      $this->storage->remove(self::$prefix . 'userId');
      $this->storage->put(self::$prefix . 'role', self::ROLE_ANONYMOUS);
      $this->storage->remove(self::$prefix . 'privileges');
    }
  }

  /**
   * Vrati ID prihlaseneho uzivatele
   *
   * @return integer
   */
  public function getUserId ()
  {
    return $this->storage->get(self::$prefix . 'userId');
  }

  /**
   * Vrati postaveni uzivatele
   *
   * @return integer
   */
  public function getRole ()
  {
    return $this->role;
  }

  /**
   * Proveri platnost prihlaseni
   *
   * Pokud prihlaseni existuje ale je vyhodnoceno jako neplatne
   * je uzivatel odhlasen
   *
   * @return bool
   */
  public function checkAuth ()
  {
    if ($this->storage->isAvailable()) {
      if ($this->storage->get(self::$prefix . 'userId') !== NULL) {
        return true;
      } else {
        $this->logout();
        return false;
      }
    }

    return false;
  }

  /**
   * Proveri zda je uzivatel opravnen k dane akci
   *
   * @param  integer  $needRole       potrebne postaveni uzivatele/typ uctu
   * @param  array    $needPrivilege  potrebne opravneni
   * @return bool
   */
  public function isAllowed ($needRole = self::ALL, $needPrivilege = self::ALL)
  {
    if ($needRole !== self::ALL) {
      if ($this->role < $needRole) {
        return false;
      }
    }
    if ($needPrivilege !== self::ALL) {
      if (!is_array($needPrivilege)) {
        throw new InvalidArgumentException();
      }
      if (!key_exists('subject', $needPrivilege) || !key_exists('right', $needPrivilege)) {
        throw new InvalidArgumentException();
      }

      $subject = $needPrivilege['subject'];
      if (!isset($this->privileges[$subject])) {
        return false;
      }
      if ($this->privileges[$subject] >= $needPrivilege['right']) {
        return true;
      } else {
        return false;
      }
    }

    return true;
  }

  /**
   * Priradi parametr k uzivateli
   *
   * @since  metoda je pristupna od verze 0.9
   * @param  string  $key
   * @param  mixed   $value
   * @return void
   */
  public function __set ($key, $value)
  {
    if (!$this->storage->isAvailable()) {
      $this->storage->init();
    }
    $this->storage->put($key, $value);
  }

  /**
   * Vrati parametr uzivatele
   *
   * @since  metoda je pristupna od verze 0.9
   * @param  string  $key
   * @return mixed
   */
  public function __get ($key)
  {
    if (!$this->storage->isAvailable()) {
      return NULL;
    }
    return $this->storage->get($key);
  }

  /**
   * Ukonci celou relaci
   *
   * @since  metoda je pristupna od verze 0.9
   * @return void
   */
  public function quit ()
  {
    $this->logout(true);
  }
}
