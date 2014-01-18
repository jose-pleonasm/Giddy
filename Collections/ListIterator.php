<?php

/**
 * Implementace iteratoru
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: ListIterator.php, 2009-10-22 18:52 $
 */

/**
 * Zakladni iterator kolekce typu List
 *
 * Implementace metod pro prochazeni objektu jako pole
 *
 * @category   Giddy
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
class ListIterator implements Iterator
{
  private $list;

  public function __construct ($array)
  {
    if (is_array($array)) {
      $this->list = $array;
    }
  }

  public function hasNext ()
  {
    if (key($this->list) === NULL) {
      return false;
    }
    return isset($this->list[key($this->list)+1]);
  }

  public function next ()
  {
    return next($this->list);
  }

  public function rewind ()
  {
    reset($this->list);
  }

  public function current ()
  {
    return current($this->list);
  }

  public function key ()
  {
    return key($this->list);
  }

  public function valid ()
  {
    return (current($this->list) !== false);
  }
}
