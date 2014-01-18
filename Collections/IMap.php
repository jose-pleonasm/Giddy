<?php

/**
 * Kolekce - obecny pro kolekce typu Map
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: IMap.php, 2009-10-30 11:45 $
 */

Base::import('Collections/Collection');

/**
 * Zakladni rozhrani map
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface IMap extends Collection
{
  public function put ($key, $item);

  public function putAll (Collection $map);

  public function get ($key);

  public function remove ($key);

  public function containsKey ($key);

  public function containsValue ($item);

  public function getValues ();
}
