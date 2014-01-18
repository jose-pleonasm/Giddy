<?php

/**
 * Abstraktni List
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: AbstractList.php, 2009-10-30 11:44 $
 */

Base::import('Common');
Base::import('Collections/IList');

/**
 * Zakladni trida pro kolekce typu List
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
abstract class AbstractList extends Common implements IList
{
  protected $base = 0;

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
   * @uses   count()
   * @return boolean
   */
  public function isEmpty ()
  {
    return count($this->data) == 0;
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
   * @uses   AbstractList::$data
   * @return array
   */
  public function toArray ()
  {
    return $this->data;
  }

  /**
   * Vlozi polozku
   *
   * @param  integer  $index
   * @return mixed
   */
  public function offsetGet ($index)
  {
    return $this->get($index);
  }

  /**
   * Nastavy polozku
   *
   * @param  integer  $index
   * @param  mixed    $item
   * @return void
   */
  public function offsetSet ($index, $item)
  {
    $this->set($index, $item);
  }

  /**
   * Zda polozka existuje v seznamu
   *
   * @param  integer  $index
   * @return boolean
   */
  public function offsetExists ($index)
  {
    return isset($this->data[$index]);
  }

  /**
   * Odstrani polozku ze seznamu
   *
   * @param  integer  $index
   * @return void
   */
  public function offsetUnset ($index)
  {
    $this->remove($index);
  }
}
