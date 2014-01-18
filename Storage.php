<?php

/**
 * Abstrakce ukladaci struktury
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Session.php, 2011-03-10 14:49 $
 */

Base::import('Common');
Base::import('Collections/IMap');

/**
 * Zasobnik neni pripraven/pridelen
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.3
 * @since      Trida je pristupna od verze 0.1
 */
class StorageUnavailableException extends RuntimeException {}

/**
 * Abstraktni model pro zasobnik
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.3
 * @since      Trida je pristupna od verze 0.4
 */
abstract class Storage extends Common implements IMap
{
  /**
   * Nacte pole do teto mapy
   *
   * @uses   IMap::put()
   * @param  array  $array
   * @return void
   */
  public function fromArray ($array)
  {
    if ($array === NULL) {
      throw new NullPointerException();
    }
    foreach ($array as $key => $item) {
      $this->put($key, $item);
    }
  }

  /**
   * Zkopiruje celou predanou mapu do teto mapy
   *
   * @uses   IMap::getIterator()
   * @uses   IMap::put()
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
   * Vrati polozku
   *
   * @param  mixed  $key
   * @return mixed
   */
  public function offsetGet ($key)
  {
    return $this->get($key);
  }

  /**
   * Nastavy polozku
   *
   * @param  mixed  $key
   * @param  mixed  $item
   * @return void
   */
  public function offsetSet ($key, $item)
  {
    $this->put($key, $item);
  }

  /**
   * Zda polozka existuje v tomto zasobniku
   *
   * @param  mixed  $key
   * @return boolean
   */
  public function offsetExists ($key)
  {
    return $this->exists($key);
  }

  /**
   * Odstrani polozku ze zasobniku
   *
   * @param  mixed  $key
   * @return void
   */
  public function offsetUnset ($key)
  {
    $this->remove($key);
  }

  abstract public function init();
  abstract public function quit();
  abstract public function isAvailable();
}
