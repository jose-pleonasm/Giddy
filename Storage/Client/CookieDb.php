<?php

/**
 * Realizuje uloziste
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage Storage
 * @version    $Id: CookieDb.php, 2011-03-10 14:49 $
 */

Base::import('Storage/Client');

/**
 * Implementace ukladaci struktury vazane k HTTP klientovi
 * prostrednictvim cookie a DB
 *
 * Vyzaduje nasledujici tabulku:
 * <code>
 * CREATE TABLE `_client_storage` (
 *   `client_id` VARCHAR(32) NOT NULL,
 *   `key` VARCHAR(100) NOT NULL,
 *   `value` TEXT NOT NULL,
 *   `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *   `expire` TIMESTAMP NOT NULL DEFAULT 0
 * ) ENGINE = InnoDB COMMENT = 'Realizuje klientsky zasobnik'
 *   DEFAULT CHARACTER SET = utf8 COLLATE = utf8_czech_ci;
 * </code>
 *
 * @package    Giddy
 * @subpackage Storage
 * @author     Josef Hrabec
 * @version    Release: 0.9.3
 * @since      Trida je pristupna od verze 0.4
 */
class Storage_Client_CookieDb extends Storage_Client
{
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
   * Identifikator relace
   *
   * @see Storage_Client_CookieDb::generateId()
   * @var string
   */
  private $sessionId;

  /**
   * Vygenereju identifikator
   *
   * @return string
   */
  public static function generateId ()
  {
    return md5($_SERVER['REMOTE_ADDR'] . time());
  }

  /**
   * Inicializace
   *
   * @param  array  $cfg
   */
  public function __construct ($cfg)
  {
    if (!isset($cfg['db'])) {
      throw new InvalidArgumentException('DB must be set');
    }
    if (!($cfg['db'] instanceof DBI_Common)) {
      throw new InvalidArgumentException('DB must be DBI_Common object');
    }
    $this->cfg['db'] = $cfg['db'];

    $this->cfg['table']    = isset($cfg['table']) ? $cfg['table'] : '_client_storage';
    $this->cfg['lifetime'] = isset($cfg['lifetime']) ? $cfg['lifetime'] : 0;
    $this->cfg['path']     = isset($cfg['path']) ? $cfg['path'] : (dirname($_SERVER['PHP_SELF']) == DIRECTORY_SEPARATOR
                                                                    ? '/' : dirname($_SERVER['PHP_SELF']) . '/');
    $this->cookieName      = isset($cfg['sessName']) ? $cfg['sessName'] : 'UserStorage';

    if ($this->isAvailable()) {
      $this->sessionId = $_COOKIE[$this->cookieName];
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
    if (isset($this->cfg[$name])) {
      $buffer = $this->cfg[$name];
      $this->cfg[$name] = $value;

    } elseif ($name == 'sessName') {
      $buffer = $this->cookieName;
      $this->cookieName = $value;

    } else {
      throw new ArgumentOutOfRangeException();
    }

    if ($this->isAvailable()) {
      setcookie($this->cookieName, $this->sessionId, $this->cfg['lifetime'], $this->cfg['path']);
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
      $this->run['dying']    = false;
      $this->run['starting'] = true;

      $this->sessionId = self::generateId();
      setcookie($this->cookieName, $this->sessionId, $this->cfg['lifetime'], $this->cfg['path']);
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
    return isset($_COOKIE[$this->cookieName]);
  }

  /**
   * Vrati identifikator relace
   *
   * @return string
   * @throws StorageUnavailableException
   */
  public function getId ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    return $this->sessionId;
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
      throw new StorageUnavailableException();
    }

    $exists = $this->containsKey($key);

    $key   = $this->cfg['db']->escapeString($key);
    $value = $this->cfg['db']->escapeString(serialize($item));
    if ($exists) {
      $this->cfg['db']->query("UPDATE `" . $this->cfg['table'] . "`
                               SET `value` = '" . $value . "'
                               WHERE `client_id` = '" . $this->sessionId . "' AND `key` = '" . $key . "'");
    } else {
      $this->cfg['db']->query("INSERT INTO `" . $this->cfg['table'] . "`
                               VALUES ('" . $this->sessionId . "', '" . $key . "', '" . $value . "', NOW(), '" . $this->cfg['lifetime'] . "')");
    }
  }

  /**
   * Vrati hodnotu
   *
   * @param  string  $key
   * @return mixed
   * @throws StorageUnavailableException
   */
  public function get ($key)
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $value = $this->cfg['db']->getVar("SELECT `value` FROM `" . $this->cfg['table'] . "`
                                       WHERE `client_id` = '" . $this->sessionId . "' AND `key` = '" . $key . "'");
    return empty($value) ? NULL : unserialize($value);
  }

  /**
   * Odstrani hodnotu
   *
   * @param  string  $key
   * @return void
   * @throws StorageUnavailableException
   */
  public function remove ($key)
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $exists = $this->containsKey($key);
    $key = $this->cfg['db']->escapeString($key);
    if ($exists) {
      $this->cfg['db']->query("DELETE FROM `" . $this->cfg['table'] . "` WHERE `client_id` = '" . $this->sessionId . "' AND `key` = '" . $key . "'");
    }
  }

  /**
   * Vyhleda klic v zasobniku
   *
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

    $key = $this->cfg['db']->escapeString($key);
    $exists = $this->cfg['db']->getVar("SELECT `created` FROM `" . $this->cfg['table'] . "`
                                        WHERE `client_id` = '" . $this->sessionId . "' AND `key` = '" . $key . "'");

    return empty($exists) ? false : true;
  }

  /**
   * Vyhleda polozku v zasobniku
   *
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

    $value = $this->cfg['db']->escapeString(serialize($item));
    $exists = $this->cfg['db']->getVar("SELECT `created` FROM `" . $this->cfg['table'] . "`
                                        WHERE `client_id` = '" . $this->sessionId . "' AND `value` = '" . $value . "'");

    return empty($exists) ? false : true;
  }

  /**
   * Vrati klic prvniho nalezeneho specifikovaneho objektu
   *
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

    return array_search($item, $this->getValues());
  }

  /**
   * Vrati klice tohoto zasobniku
   *
   * @return array
   * @throws StorageUnavailableException
   */
  public function getKeys ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $result = $this->cfg['db']->getResults("SELECT `key` FROM `" . $this->cfg['table'] . "`
                                            WHERE `client_id` = '" . $this->sessionId . "'", DBI::RESULT_AA);
    $keys = array();
    if (!empty($result)) {
      foreach ($result as $row) {
        $keys[] = $row['key'];
      }
    }
    return $keys;
  }

  /**
   * Vrati polozky tohoto zasobniku
   *
   * @return array
   * @throws StorageUnavailableException
   */
  public function getValues ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $result = $this->cfg['db']->getResults("SELECT `value` FROM `" . $this->cfg['table'] . "`
                                            WHERE `client_id` = '" . $this->sessionId . "'", DBI::RESULT_AA);
    $values = array();
    if (!empty($result)) {
      foreach ($result as $row) {
        $values[] = unserialize($row['value']);
      }
    }
    return $values;
  }

  /**
   * Vrati cely zasobnik jako (standartni) pole
   *
   * @return array
   * @throws StorageUnavailableException
   */
  public function toArray ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $result = $this->cfg['db']->getResults("SELECT `key`, `value` FROM `" . $this->cfg['table'] . "`
                                            WHERE `client_id` = '" . $this->sessionId . "'", DBI::RESULT_AA);
    $data = array();
    foreach ($result as $row) {
      $data[$row['key']] = unserialize($row['value']);
    }
    return $data;
  }

  /**
   * Vrati iterator
   *
   * @uses   Base::import()
   * @uses   MapIterator::__construct()
   * @uses   AbstractMap::$data
   * @return MapIterator  iterator pro prochazeni pole
   */
  public function getIterator ()
  {
    Base::import('Collections/MapIterator');
    return new MapIterator($this->toArray());
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
      throw new StorageUnavailableException();
    }

    $result = $this->cfg['db']->getResults("SELECT `created` FROM `" . $this->cfg['table'] . "`
                                            WHERE `client_id` = '" . $this->sessionId . "'", DBI::RESULT_AA);
    return empty($result);
  }

  public function count ()
  {
    if (!$this->isAvailable()) {
      throw new StorageUnavailableException();
    }

    $result = $this->cfg['db']->getResults("SELECT `created` FROM `" . $this->cfg['table'] . "`
                                            WHERE `client_id` = '" . $this->sessionId . "'", DBI::RESULT_AA);
    return count($result);
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
      throw new StorageUnavailableException();
    }

    $this->cfg['db']->query("DELETE FROM `" . $this->cfg['table'] . "` WHERE `client_id` = '" . $this->sessionId . "'");
  }

  /**
   * Ukonci storage
   *
   * @return void
   */
  public function quit ()
  {
    $this->clear();
    setcookie($this->cookieName, '', time() - 60, $this->cfg['path']);

    $this->run['dying']    = true;
    $this->run['starting'] = false;
  }
}
