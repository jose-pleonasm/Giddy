<?php

/**
 * Jednoducha implementace Map
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: Hashtable.php, 2009-10-30 11:44 $
 */

Base::import('Collections/AbstractMap');

/**
 * Zakladni Map imitujici asociativni pole
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
class Hashtable extends AbstractMap
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
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   Object::hashCode()
   * @uses   AbstractMap::$data
   * @param  mixed  $key
   * @param  mixed  $item
   * @return mixed  pokud byl klic obsazen vrati puvodni objekt, jinak NULL
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function put ($key, $item)
  {
    if ($key === NULL || $item === NULL) {
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
    return $buffer;
  }

  /**
   * Zkopiruje celou predanou mapu do teto mapy
   *
   * @uses   Hashtable::getIterator()
   * @uses   Hashtable::put()
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
   * Vrati hodnotu specifikovaneho klice,
   * nebo NULL, pokud neni klic namapovan
   *
   * @uses   isset()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   Object::hashCode()
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
  }

  /**
   * Odstrani objekt z teto mapy podle specifikovaneho klice
   *
   * @uses   isset()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   unset()
   * @uses   Object::hashCode()
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
  }

  /**
   * Vyhleda klic v teto mape
   *
   * @uses   array_key_exists()
   * @uses   is_object()
   * @uses   method_exists()
   * @uses   Object::hashCode()
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
    if (is_object($key)) {
      if (!method_exists($key, 'hashCode')) {
        throw new InvalidArgumentException();
      }
      $key = $key->hashCode();
    }
    return array_key_exists($key, $this->data);
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
}
