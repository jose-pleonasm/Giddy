<?php

/**
 * Reprezentuje konfiguraci
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    $Id: Cfg.php, 2009-12-10 20:26 $
 */

Base::import('Collections_AbstractMap');

/**
 * Chyba pro pokusu o prepsani
 *
 * @package    Giddy
 */
class NotRewritableException extends RuntimeException {}

/**
 * Jednoducha implementace konfiguracni struktury
 *
 * Umoznuje nastavovani hodnot
 * pomoci dynamickych atributu objektu
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class Cfg extends AbstractMap
{
  /**
   * Urcuje zda lze hodnoty prepisovat
   *
   * @var bool
   */
  public $isRewritable = false;

  /**
   * Vrati odpovidajici hodnotu pro Cfg::expand()
   *
   * @uses   empty()
   * @uses   Cfg::get()
   * @see    Cfg::expand()
   * @param  array  $matches  hodnoty z preg_replace
   * @return mixed  odpovidajici hodnota
   */
  private function expandGet ($matches)
  {
    if (empty($matches[1])) {
      return '%';
    }
    return $this->get($matches[1]);
  }

  /**
   * Inicializace
   *
   * Pokud je konstruktoru predano pole,
   * je vlozeno do mapy
   *
   * @uses   is_array()
   * @uses   Cfg::import()
   * @uses   Cfg::putAll()
   * @param  mixed  $items  elementy ke vlozeni (pole nebo mapa)
   */
  public function __construct ($items = NULL)
  {
    if (is_array($items)) {
      $this->import($items);
    } elseif ($items instanceof AbstractMap) {
      $this->putAll($items);
    }
  }

  public function __set ($key, $item)
  {
    $this->put($key, $item);
  }

  public function __get ($key)
  {
    return $this->get($key);
  }

  public function __isset ($key)
  {
    $this->containsKey($key);
  }

  public function __unset ($key)
  {
    $this->remove($key);
  }

  /**
   * Pridani/nastaveni hodnoty 
   *
   * @uses   Cfg::set()
   * @param  mixed  $key
   * @param  mixed  $item
   * @return void
   */
  public function put ($key, $item)
  {
    $this->set($key, $item);
  }

  /**
   * Zkopiruje cely predany objekt do tohoto
   *
   * @uses   AbstractMap::getIterator()
   * @uses   Cfg::put()
   * @param  object $map  Objekt typu Map ke vlozeni
   * @return void
   */
  public function putAll (Collection $map)
  {
    if ($map === NULL) {
      throw new NullPointerException();
    }

    $map = $map->getIterator();
    foreach ($map as $key => $item) {
      $this->put($key, $item);
    }
  }

  /**
   * Pridani/nastaveni hodnoty
   *
   * @uses   isset()
   * @uses   AbstractMap::$data
   * @param  mixed  $key
   * @param  mixed  $item
   * @return mixed  pokud byl klic obsazen vrati puvodni objekt, jinak NULL
   * @throws NullPointerException
   * @throws NotRewritableException
   */
  public function set ($key, $item)
  {
    if ($key === NULL || $item === NULL) {
      throw new NullPointerException();
    }

    if (isset($this->data[$key])) {
      if ($this->isRewritable) {
        $buffer = isset($this->data[$key]) ? $this->data[$key] : NULL;
        $this->data[$key] = $item;
        return $buffer;
      } else {
        throw new NotRewritableException('This object is not rewritable');
      }
    }
    $this->data[$key] = $item;
  }

  /**
   * Vrati hodnotu specifikovaneho parametru,
   * pokud neni parametr namapovan vyvola vyjimku
   *
   * @uses   isset()
   * @uses   AbstractMap::$data
   * @param  mixed  $key  klic
   * @return mixed  pozadovany objekt
   * @throws NullPointerException
   * @throws ArgumentOutOfRangeException
   */
  public function get ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }

    if (isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      throw new ArgumentOutOfRangeException('Param ' . $key . ' is not set');
    }
  }

  /**
   * Vrati hodnotu specifikovaneho parametru,
   * nebo nastavi a vrati $default, pokud neni parametr namapovan
   *
   * @uses   isset()
   * @uses   AbstractMap::$data
   * @param  mixed  $key     klic
   * @param  mixed  $default vychozi hodnota
   * @return mixed  pozadovany objekt, nebo $default
   * @throws NullPointerException
   */
  public function extort ($key, $default = NULL)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }

    if (isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return $this->data[$key] = $default;
    }
  }

  /**
   * Odstrani hodnotu z tohoto objektu podle specifikovaneho nazvu
   *
   * @uses   isset()
   * @uses   unset()
   * @uses   AbstractMap::$data
   * @param  mixed  $key
   * @return mixed  objekt, ktera pod danym klicem byla
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function remove ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }

    $buffer = isset($this->data[$key]) ? $this->data[$key] : NULL;
    unset($this->data[$key]);
    return $buffer;
  }

  /**
   * Vyhleda nazev parametru tohoto objektu
   *
   * @uses   array_key_exists()
   * @uses   AbstractMap::$data
   * @param  mixed   $key  hledany klic
   * @return boolean       true pokud tato mapa obsahuje hledany klic, jinak false
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function containsKey ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }

    return array_key_exists($key, $this->data);
  }

  /**
   * Vyhleda hodnotu v tomto objektu
   *
   * @uses   in_array()
   * @uses   AbstractMap::$data
   * @param  mixed   $item  hledany objekt
   * @return boolean true pokud tato mapa obsahuje hledany objekt, jinak false
   * @throws NullPointerException
   */
  public function containsValue ($item)
  {
    if ($item === NULL) {
      throw new NullPointerException();
    }

    return in_array($item, $this->data);
  }

  /**
   * Vrati klic prvni nalezene specifikovane hodnoty
   *
   * @uses   array_search()
   * @uses   AbstractMap::$data
   * @param  mixed  $value  hledany objekt
   * @return mixed  odpovidajici klic
   * @throws NullPointerException
   */
  public function keyOf ($item)
  {
    if ($item === NULL) {
      throw new NullPointerException();
    }

    return array_search($item, $this->data);
  }

  /**
   * Vrati nazvy parametru
   *
   * @uses   array_keys()
   * @uses   AbstractMap::$data
   * @return array
   */
  public function getKeys ()
  {
    return array_keys($this->data);
  }

  /**
   * Vrati hodnoty
   *
   * @uses   array_values()
   * @uses   AbstractMap::$data
   * @return array
   */
  public function getValues ()
  {
    return array_values($this->data);
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
    Base::import('Collections_MapIterator');
    return new MapIterator($this->data);
  }

  /**
   * Rozvine hodnotu dle specifikovaneho klice
   *
   * Nahradi metaznaky odpividajicimi hodnotami tohoto objektu
   *
   * @uses   isset()
   * @uses   preg_replace_callback
   * @uses   AbstractMap::$data
   * @uses   Cfg::expandGet()
   * @param  mixed  $key
   * @return mixed
   * @throws ArgumentOutOfRangeException
   */
  public function expand ($key)
  {
    if (!isset($this->data[$key])) {
      throw new ArgumentOutOfRangeException('Param ' . $key . ' is not set');
    }

    return $this->data[$key] = preg_replace_callback('/%([a-z0-9_-]*)%/i',
                                                     array($this, 'expandGet'),
                                                     $this->data[$key]);
  }

  /**
   * Rozvine hodnoty vsech nastavenych polozek
   *
   * Nahradi metaznaky odpividajicimi hodnotami tohoto objektu
   *
   * @uses   preg_replace_callback
   * @uses   AbstractMap::$data
   * @uses   Cfg::expandGet()
   * @return void
   */
  public function expandAll ()
  {
    foreach ($this->data as $key => $value) {
      // kopiruje funkcionalitu Cfg::expand()
      $this->data[$key] = preg_replace_callback('/%([a-z0-9_-]*)%/i',
                                                array($this, 'expandGet'),
                                                $value);
    }
  }

  /**
   * Vytvori konstanty
   *
   * Z nastavenych polozek definuje konstanty
   *
   * @uses   define()
   * @uses   AbstractMap::$data
   * @return void
   */
  public function defineConstants ()
  {
    foreach ($this->data as $key => $value) {
      define(strtoupper($key), $value);
    }
  }
}
