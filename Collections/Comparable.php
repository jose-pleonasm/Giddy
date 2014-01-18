<?php

/**
 * Comparable
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: Comparable.php, 2009-10-30 11:44 $
 */

/**
 * Comparable
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
interface Comparable
{
  /**
   * Porovna tento objekt se specifikovanym objektem
   *
   * @param  object  $o1
   * @return integer vrati -1, 0 nebo +1, pokud je tento objekt mensi, stejny nebo vetsi jako specifikovany parametr
   * @throws ClassCastException
   */
  public function compareTo ($obj);
}
