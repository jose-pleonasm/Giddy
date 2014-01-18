<?php

/**
 * Kolekce - obecny pro kolekce typu List
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: IList.php, 2009-10-30 11:45 $
 */

Base::import('Collections/Collection');

/**
 * Zakladni rozhrani list
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface IList extends Collection
{
  public function add ($index, $item = NULL);

  public function addAll ($index, $items = NULL);

  public function get ($index);

  public function set ($index, $item);

  public function remove ($index);

  public function removeRange ($fromIndex, $toIndex);

  public function subList ($formIndex, $toIndex);

  public function contains ($item);

  public function indexOf ($item);

  public function lastIndexOf ($item);
}
