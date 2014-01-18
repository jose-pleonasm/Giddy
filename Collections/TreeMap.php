<?php

/**
 * Jednoducha implementace SortedMap
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Giddy
 * @package   Giddy_Collections
 * @version   $Id: TreeMap.php, 2010-10-13 15:39 $
 */

Base::import('Collections/AbstractMap');
Base::import('Collections/SortedMap');

/**
 * Kolekce typu SortedMap s razenim dle klice
 *
 * @package   Giddy_Collections
 * @author    Josef Hrabec
 * @version   Release: 0.4
 * @since     Rozhrani je pristupne od verze 0.1
 */
class TreeMap extends AbstractMap implements SortedMap
{
  /**
   * Inicializace
   *
   * Pokud je konstruktoru predano pole,
   * je vlozeno do mapy
   *
   * @uses   is_array()
   * @uses   Hashtable::import()
   * @uses   Hashtable::putAll()
   * @param  mixed  $items  elementy ke vlozeni (pole nebo mapa)
   */
  public function __construct ($items = NULL)
  {
    if (is_array($items)) {
      $this->import($items);
      ksort($this->data);
    }
    if ($items instanceof AbstractMap) {
      $this->putAll($items);
    }
  }

  /**
   * Pridani objektu
   *
   * Spoji specifikovanou hodnotu se specifikovanym klicem v teto mape.
   * Pokud byl klic pred tim obsazen, vrati metoda jeho puvodni hodnotu
   *
   * @uses   isset()
   * @uses   ksort()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @param  mixed  $key
   * @param  mixed  $item
   * @return mixed  pokud byl klic obsazen vrati puvodni objekt, jinak NULL
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function put ($key, $item)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (is_object($key)) {
      if (!method_exists($key, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $key = $key->hashCode();
    }
    $buffer = isset($this->data[$key]) ? $this->data[$key] : NULL;
    $this->data[$key] = $item;
    ksort($this->data);
    return $buffer;
    //ClassCastException
  }

  /**
   * Zkopiruje celou predanou mapu do teto mapy
   *
   * @uses   ksort()
   * @uses   Hashtable::getIterator()
   * @uses   Hashtable::put()
   * @param  object $map  Objekt typu Map ke vlozeni
   * @return void
   * @throws NullPointerException
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
    ksort($this->data);
    //ClassCastException
  }

  /**
   * Vrati hodnotu specifikovaneho klice,
   * nebo NULL, pokud neni klic namapovan
   *
   * @uses   isset()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @param  mixed  $key  klic
   * @return mixed  pozadovany objekt, nebo NULL
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function get ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (is_object($key)) {
      if (!method_exists($key, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $key = $key->hashCode();
    }
    return isset($this->data[$key]) ? $this->data[$key] : NULL;
    //ClassCastException
  }

  /**
   * Odstrani objekt z teto mapy podle specifikovaneho klice
   *
   * @uses   isset()
   * @uses   is_object()
   * @uses   method_exists()
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
    if (is_object($key)) {
      if (!method_exists($key, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $key = $key->hashCode();
    }
    $buffer = isset($this->data[$key]) ? $this->data[$key] : NULL;
    unset($this->data[$key]);
    return $buffer;
    //ClassCastException
  }

  /**
   * Vyhleda klic v teto mape
   *
   * @uses   array_key_exists()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @param  mixed   $key  hledany klic
   * @return boolean true pokud tato mapa obsahuje hledany klic, jinak false
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function containsKey ($key)
  {
    if ($key === NULL) {
      throw new NullPointerException();
    }
    if (is_object($key)) {
      if (!method_exists($key, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $key = $key->hashCode();
    }
    return array_key_exists($key, $this->data);
    //ClassCastException
  }

  /**
   * Vyhleda objekt v teto mape
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
   * Vrati klic prvniho nalezeneho specifikovaneho objektu
   *
   * @uses   array_search()
   * @uses   AbstractMap::$data
   * @param  mixed  $item  hledany objekt
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
   * Vrati klic posledniho nalezeneho specifikovaneho objektu
   *
   * @uses   array_keys()
   * @uses   count()
   * @uses   AbstractMap::$data
   * @param  mixed  $item  hledana hodnota
   * @return mixed  odpovidajici klic
   * @throws NullPointerException
   */
  public function lastKeyOf ($item)
  {
    if ($item === NULL) {
      throw new NullPointerException();
    }
    $buffer = array_keys($this->data, $item);
    return $buffer[count($buffer)-1];
  }

  /**
   * Vrati klice teto mapy
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
   * Vrati objekty teto mapy
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
   * Vrati prvni (nejvyzsi) klic teto mapy
   *
   * @uses   AbstractMap::$data
   * @return mixed  prvni klic teto mapy
   */
  public function firstKey ()
  {
    // rychlejsi
    foreach ($this->data as $key => $item) {
      return $key;
    }
    /*
    $buffer = array_keys($this->data);
    return $buffer[0];
    */
  }

  /**
   * Vrati posledni (nejnizsi) klic teto mapy
   *
   * @uses   AbstractMap::$data
   * @return mixed  posledni klic teto mapy
   */
  public function lastKey ()
  {
    // (pravdepodobne) rychlejsi
    foreach ($this->data as $key => $item) {
      $lKey = $key;
    }
    return $lKey;
    /*
    $buffer = array_keys($this->data);
    return $buffer[count($buffer)-1];
    */
  }

  /**
   * Vrati zacatek mapy az po $toKey (nezahrnuty)
   *
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @uses   TreeMap::__construct()
   * @uses   TreeMap::put()
   * @param  mixed      $toKey  klic, po ktery se ma mapa vratit - vyjma tohoto
   * @return SortedMap  vrchni cast teto mapy
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function headMap ($toKey)
  {
    if ($toKey === NULL) {
      throw new NullPointerException();
    }
    if (is_object($toKey)) {
      if (!method_exists($toKey, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $toKey = $toKey->hashCode();
    }
    $nMap = new TreeMap();
    foreach ($this->data as $key => $item) {
      if ($key == $toKey) {
        return $nMap;
      }
      $nMap->put($key, $item);
    }
  }

  /**
   * Vrati cast teto mapy od klice $fromKey (vcetne)
   * po $toKey (nezahrnuty)
   *
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @uses   TreeMap::__construct()
   * @uses   TreeMap::put()
   * @param  mixed  $fromKey  klic, specifikujici zacatek nove mapy - vcetne tohoto
   * @param  mixed  $toKey    klic, specifikujici konec nove mapy - vyjma tohoto
   * @return SortedMap        cast teto mapy
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function subMap ($fromKey, $toKey)
  {
    if ($fromKey === NULL) {
      throw new NullPointerException();
    }
    if ($toKey === NULL) {
      throw new NullPointerException();
    }
    if (is_object($fromKey)) {
      if (!method_exists($fromKey, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $fromKey = $fromKey->hashCode();
    }
    if (is_object($toKey)) {
      if (!method_exists($toKey, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $toKey = $toKey->hashCode();
    }
    $isSubMap = false;
    $nMap = new TreeMap();
    foreach ($this->data as $key => $item) {
      if ($key == $toKey) {
        return $nMap;
      }
      if ($key == $fromKey) {
        $isSubMap = true;
      }
      if ($isSubMap) {
        $nMap->put($key, $item);
      }
    }
  }

  /**
   * Vrati konec mapy of $fromKey (vcetne)
   *
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   AbstractMap::$data
   * @uses   TreeMap::__construct()
   * @uses   TreeMap::put()
   * @param  mixed      $fromKey  klic, od ktereho se ma mapa vratit - vcetne tohoto
   * @return SortedMap  spodni cast teto mapy
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function tailMap ($fromKey)
  {
    if ($fromKey === NULL) {
      throw new NullPointerException();
    }
    if (is_object($fromKey)) {
      if (!method_exists($fromKey, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $fromKey = $fromKey->hashCode();
    }
    $isSubMap = false;
    $nMap = new TreeMap();
    foreach ($this->data as $key => $item) {
      if ($key == $fromKey) {
        $isSubMap = true;
      }
      if ($isSubMap) {
        $nMap->put($key, $item);
      }
    }
    return $nMap;
  }
}
