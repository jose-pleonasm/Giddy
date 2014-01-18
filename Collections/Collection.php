<?php

/**
 * Kolekce - obecny interface
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: Collection.php, 2009-10-30 11:45 $
 */

/**
 * Zakladni rozhrani kolekci
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
  public function equals ($object);
  public function hashCode ();
  public function clear ();
  public function isEmpty ();
  //function IteratorAggregate::getIterator();
  //function Countable::count();
  //function ArrayAccess::offsetGet($offset)
  //function ArrayAccess::offsetSet($offset, $item)
  //function ArrayAccess::offsetExists($offset)
  //function ArrayAccess::offsetUnset($offset)
}
