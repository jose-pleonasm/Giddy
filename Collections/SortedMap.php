<?php

/**
 * Interface pro setridene mapy
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: SortedMap.php, 2009-10-30 11:45 $
 */

Base::import('Collections/IMap');

/**
 * Zakladni rozhrani tridenych map
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface SortedMap extends IMap
{
  public function firstKey ();

  public function lastKey ();

  public function headMap ($toKey);

  public function subMap ($fromKey, $toKey);

  public function tailMap ($fromKey);
}
