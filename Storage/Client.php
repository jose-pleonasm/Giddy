<?php

/**
 * Storage_Client
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @subpackage Storage
 * @version    $Id: Client.php, 2009-10-30 13:00 $
 */

Base::import('Storage');

/**
 * Abstrakce pro klientsky zasobnik
 *
 * @package    Giddy
 * @subpackage Storage
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.4
 */
abstract class Storage_Client extends Storage
{
  /**
   * Vrati objekt pozadovaneho typu
   *
   * @param  string  $mode  zpusob zaznamenani stavu
   * @param  string  $id    identifikator zasobniku
   * @return object  specifikovana instance
   */
  public static function factory ($mode, $settings = array())
  {
    $mode = strtolower($mode);
    if ($mode == 'session') {
      Base::import('Storage/Client/Session');
      return Storage_Client_Session::getInstance($settings);
    } elseif ($mode == 'cookie') {
      Base::import('Storage/Client/Cookie');
      return new Storage_Client_Cookie($settings);
    } elseif ($mode == 'cookiedb') {
      Base::import('Storage/Client/CookieDb');
      return new Storage_Client_CookieDb($settings);
    } else {
      throw new InvalidArgumentException('Mode ' . $mode . ' not supported');
    }
  }
}
