<?php

/**
 * Comparator
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: Comparator.php, 2009-10-30 11:44 $
 */

/**
 * Comparator
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface Comparator
{
  /**
   * Porovna dva objekty
   *
   * @param  object  $o1
   * @param  objcet  $o2
   * @return integer vrati -1, 0 nebo +1, pokud je prvni argument mensi, stejny nebo vetsi jako druhy
   * @throws ClassCastException
   */
  public function compare ($o1, $o2);

  /**
   * Porovna shodnost tohoto objektu s objektem $obj
   *
   * @param  object  $obj  objekt ke srovnani
   * @return boolean true - pokud jsou shodne, jinak false
   */
  public function equals ($obj);
}
