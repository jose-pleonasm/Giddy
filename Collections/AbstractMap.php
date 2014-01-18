<?php

/**
 * Abstraktni Mapa
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: AbstractMap.php, 2009-10-30 11:45 $
 */

Base::import('Common');
Base::import('Collections/IMap');

/**
 * Zakladni trida pro kolekce typu Map
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
abstract class AbstractMap extends Common implements IMap
{
  /**
   * Data
   *
   * @var array
   */
  protected $data = array();

  /**
   * Prevede objekt do retezce
   *
   * @uses   get_class()
   * @uses   count()
   * @uses   AbstractList::$data
   * @uses   Object::varToStr()
   * @return string
   */
  public function __toString ()
  {
    $string = "\n";

    $string .= get_class($this);
    $string .= "\n";
    foreach($this->data as $key => $var) {
      $string .= " |";
      $string .= "\n";
      $string .= " +- ";
      $string .= $key . ": \t" . parent::varToStr($var);
      $string .= "\n";
    }
    $string .= "\n";
    $string .= "total: " . count($this->data);
    $string .= "\n";

    return $string;
  }

  /**
   * Odstrani vsechny polozky z tohoto seznemu
   *
   * @return void
   */
  public function clear ()
  {
    $this->data = array();
  }

  /**
   * Vrati true pokud je tento seznam prazdny
   *
   * @uses   empty()
   * @return boolean
   */
  public function isEmpty ()
  {
    return empty($this->data);
  }

  /**
   * Vrati pocet polozek seznamu
   *
   * @uses   count()
   * @return integer
   */
  public function count ()
  {
    return count($this->data);
  }

  /**
   * Vrati cely list jako (standartni) pole
   *
   * @uses   AbstractMap::$data
   * @return array
   */
  public function toArray ()
  {
    return $this->data;
  }

  /**
   * Nacte pole do teto mapy
   *
   * @uses   empty()
   * @uses   is_array()
   * @uses   Hashtable::put()
   * @param  array  $array
   * @return void
   * @throws NullPointerException
   * @throws InvalidArgumentException
   */
  public function import ($array)
  {
    if ($array === NULL) {
      throw new NullPointerException();
    }
    if (!(is_array($array) || $array instanceof Traversable)) {
      throw new InvalidArgumentException('Argument must be traversable.');
    }
    if (!empty($array)) {
      foreach ($array as $key => $item) {
        $this->put($key, $item);
      }
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
   * Zda polozka existuje v teto mape
   *
   * @param  mixed  $key
   * @return boolean
   */
  public function offsetExists ($key)
  {
    return isset($this->data[$key]);
  }

  /**
   * Odstrani polozku ze seznamu
   *
   * @param  mixed  $key
   * @return void
   */
  public function offsetUnset ($key)
  {
    $this->remove($key);
  }
}
