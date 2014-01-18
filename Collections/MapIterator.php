<?php

/**
 * Implementace iteratoru
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: MapIterator.php, 2009-09-09 14:35 $
 */

/**
 * Zakladni iterator kolekce typu Map
 *
 * Implementace metod pro prochazeni objektu jako pole
 *
 * @category   Giddy
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
class MapIterator implements Iterator
{
  private $list;

  public function __construct ($array)
  {
    if(is_array($array)) {
      $this->list = $array;
    }
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
