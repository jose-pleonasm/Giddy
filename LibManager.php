<?php

/**
 * Funkcionalita pro spravu knihoven
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: LibManager.php, 2010-10-11 17:18 $
 */

/**
 * Nacita knihovny
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8.3
 * @since      Trida je pristupna od verze 0.1
 */
class LibManager
{
  /**
   * Cesta ke knihovnam
   *
   * @var string
   */
  protected $dir = '';

  /**
   * Seznam registrovanych knihoven
   *
   * @var array
   */
  protected $libList = array();

  /**
   * Instance tridy
   *
   * @var object
   */
  private static $instance = NULL;

  /**
   * Vytvori instanci
   *
   * Realizace singletonu
   *
   *  @return LibManager
   */
  static public function getInstance ()
  {
    if (self::$instance == NULL) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Nastavy adresar, kde jsou knihovny
   *
   * @param  string  $dir  odpovidajici adresar
   * @return string  vrati puvodni adresar
   */
  public function setDir ($dir)
  {
    $dir = trim($dir);
    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }

    $oldDir = $this->dir;
    $this->dir = $dir;
    return $oldDir;
  }

  /**
   * Zinicializuje spravce knihoven
   *
   * @param  string  $dir  adresar s knihovnami
   * @return LibManager
   */
  public function init ($dir)
  {
    $this->setDir($dir);
    if (is_file($this->dir . '__init__.php')) {
      require_once($this->dir . '__init__.php');
    }
    return $this;
  }

  /**
   * Zaregistruje knihovnu / prida udaje do seznamu
   *
   * @param  string  $libName  nazev knihovny
   * @param  string  $libFile  skript, ktery ji reprezentuje
   * @return boolean neuspech, pokud je knihovna stejneho nazvu jiz registrovana
   */
  public function registerLib ($libName, $libFile)
  {
    $libName = strtolower($libName);
    if (in_array($libName, array_keys($this->libList))) {
      return NULL;
    } else {
      $this->libList[$libName] = $libFile;
      return true;
    }
  }

  /**
   * Nacte knihovnu
   *
   * @param  string  $libName  nazev knihovny (musi byt zaregistrovana)
   * @return void
   * @throws RuntimeException
   */
  public function loadLib ($libName)
  {
    $libName = strtolower($libName);
    if (!in_array($libName, array_keys($this->libList))) {
      throw new RuntimeException('Pozadovana neznama knihovna ' . $libName);
    }

    $include = $this->dir . $this->libList[$libName];
    if (!is_file($include)) {
      throw new FileNotFoundException('Pozadovany soubor neexistuje: ' . $include);
    }
    require_once($include);
  }

  /**
   * Test na existenci knihovny
   *
   * @param  string  $libName  nazev knihovny
   * @return bool    zda je specifikovana knihovna zaregistrovana
   */
  public function isLib ($libName)
  {
    return in_array(strtolower($libName), array_keys($this->libList));
  }

  /**
   * Vrati pole nazvu definovanych knihoven
   *
   * @return array  seznam knihoven
   */
  public function getLibList ()
  {
    if (is_array($this->libList)) {
      return array_keys($this->libList);
    } else {
      return false;
    }
  }

  /**
   * Vrati cestu ke skriptu pro specifikovanou knihovnu
   *
   * @param  string  $libName  nazev knihovny (musi byt zaregistrovana)
   * @return string  skript, ktery reprezentuje specifikovanou knihovnu
   * @throws RuntimeException
   */
  public function getLibPath ($libName)
  {
    $libName = strtolower($libName);
    if (!in_array($libName, array_keys($this->libList))) {
      throw new RuntimeException('Pozadovana neznama knihovna ' . $libName);
    }

    return $this->dir . $this->libList[$libName];
  }
}
