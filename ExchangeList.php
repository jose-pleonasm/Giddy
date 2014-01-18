<?php

/**
 * Reprezentuje kurzovni listek
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: ExchangeList.php, 2009-11-03 10:25 $
 */

/**
 * Zpracova kurzy ze zdroj. souboru
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class ExchangeList
{
  /**
   * URL zdroje
   */
  const SOURCE = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';

  /**
   * Hodina, kdy se zdroj aktualizuje
   *
   * @var integer
   */
  protected static $breakHour = 15;

  /**
   * Koncovka nazvu souboru ve kterem je cachovan vystup s tabulkou kurzu
   *
   * @var string
   */
  protected static $cacheSuffix = '-cache';

  /**
   * Koncovka nazvu souboru ve kterem jsou meta informace o datech
   *
   * @var string
   */
  static protected $cacheInfoSuffix = '-info';

  /**
   * Nastaveni objektu
   *
   * @var array
   */
  protected $cfg;

  /**
   * Nazev/identifikator objektu
   *
   * @var string
   */
  protected $name;

  /**
   * Kurzovni list
   *
   * @var array
   */
  protected $list = NULL;

  /**
   * Nacte list z cache
   *
   * @return void
   */
  protected function loadCacheList ()
  {
    // overeni platnosti cache
    if (!$this->isActual()) {
      $this->createCache();
      return;
    }

    // nacteni cache
    @$file = fopen($this->name . self::$cacheSuffix, 'r');
    if (!$file) {
      throw new IOException('Error reading or opening cache file');
    }
    $cache = fread($file, filesize($this->name . self::$cacheSuffix));
    fclose($file);

    if (empty($cache)) {
      throw new UnexpectedValueException('Cache file is empty');
    }

    $this->list = unserialize($cache);
  }

  /**
   * Inicializace
   *
   * @param  string  $name        nazev/identifikator objektu
   * @param  array   $currencies  seznam men, ktere maji byt sledovanzy
   */
  public function __construct ($name = 'exchange', $currencies = array())
  {
    $this->name = $name;
    $this->cfg['currencies'] = !empty($currencies) ? $currencies : array('EUR');
  }

  /**
   * Overi zda je cache stale aktualni
   *
   * @return boolean  true pokud je aktualni, jinak false
   */
  public function isActual ()
  {
    // ziskani infa o aktualnim vystupu z cache
    if (!is_file($this->name . self::$cacheInfoSuffix)) {
      return false;
    }
    @$file1 = fopen($this->name . self::$cacheInfoSuffix, 'r');
    if (!$file1) {
      //trigger_error('Error reading or opening info', E_USER_NOTICE);
      return false;
    }
    $dateA = fread($file1, filesize($this->name . self::$cacheInfoSuffix));
    fclose($file1);

    list($date, $time) = explode(' ', $dateA);

    if ($date == date('d.m.Y')) {
      if (intval(date('H')) >= self::$breakHour) {
        if (substr($time, 0, 2) < self::$breakHour) {
          return false;
        }
      }
      return true;
    } else {
      return false;
    }
  }

  /**
   * Nacte data, vytvori list a ulozi do cache
   *
   * @return void
   */
  public function createCache ()
  {
    // ziskani kurzovniho listu ze zdroje
    @$file = fopen(self::SOURCE, 'r');
    if (!$file) {
      throw new IOException('Error reading or opening source');
    }
    while (!feof($file)) {
      $tmp[] = trim(fgets($file, 4096));
    }
    fclose($file);

    // ulozeni meta dat o cache
    @$file = fopen($this->name . self::$cacheInfoSuffix, 'w');
    fwrite($file, date('d.m.Y H:i'));
    fclose($file);

    // vytvoreni soukromeho listu
    $this->list = array();
    // prvni radek je informace o verzi => je treba ho preskocit, proto se zacina od jednicky
    for ($i = 1; $i < count($tmp); $i++) {
      if (empty($tmp[$i])) {
        continue;
      }
      @$cols = explode('|', $tmp[$i]);
      if (@in_array($cols[3], $this->cfg['currencies'])) {
        $this->list[$cols[3]]['rate']     = $cols[4];
        $this->list[$cols[3]]['quantity'] = $cols[2];
      }
    }

    $cache = serialize($this->list);

    // ulozeni listu do cache
    @$file = fopen($this->name . self::$cacheSuffix, 'w');
    fwrite($file, $cache);
    fclose($file);
  }

  /**
   * Vrati kurzovni list
   *
   * @return array  vlastni kurzovni list
   */
  public function getList ()
  {
    if ($this->list === NULL) {
      $this->loadCacheList();
    }

    return $this->list;
  }

  /**
   * Vrati kurz dle specifikovane meny
   *
   * @param  string  $currency  mena
   * @param  boolean $asFloat   zda vracet kurz jako datovy typ float
   * @return string  aktualni kurz
   */
  public function getRate ($currency, $asFloat = true)
  {
    if ($this->list === NULL) {
      $this->loadCacheList();
    }

    $rate = $this->list[$currency]['rate'];
    if ($asFloat) {
      $rate = (float) str_replace(',', '.', $rate);
    }
    return $rate;
  }

  /**
   * Vrati mnozstvi na kurz dle meny
   *
   * @param  string  $currency  mena
   * @return integer mnozstvi na kurz
   */
  public function getQuantity ($currency)
  {
    if ($this->list === NULL) {
      $this->loadCacheList();
    }

    return (int) $this->list[$currency]['quantity'];
  }
}
