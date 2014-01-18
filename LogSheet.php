<?php

/**
 * Reprezentuje LogSheet
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    $Id: LogSheet.php, 2010-01-04 18:58 $
 */

/**
 * Abstraktni trida LogSheet
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.5
 */
abstract class LogSheet
{
  /**
   * Vrati odpovidajici objekt reprezentujici specifikovany log sheet
   *
   * @param  string  $source
   * @param  string  $type
   * @param  mixed   $params
   * @return LogSheet
   */
  public static function factory($source, $type = 'xml', $params = NULL)
  {
    $type = ucfirst(strtolower($type));
    $classname = "LogSheet_$type";

    Base::import('LogSheet/' . $type);
    return new $classname($source, $params);
  }

  /**
   * Vrati verzi dokumentu logu
   *
   * @return string
   */
  abstract public function getVersion();

  /**
   * Vrati informaci o zdroji
   *
   * @param  string  $subject  specifikace informace
   * @return string
   */
  abstract public function getMeta($subject);

  /**
   * Vrati zaznamy v logu
   *
   * @param  array
   * @return array
   */
  abstract public function getRecords($restriction = NULL);

  /**
   * Vrati zaznam z logu specifikovany dle ID
   *
   * @param  mixed $id
   * @return array
   */
  abstract public function getRecord($id);

  /**
   * Vrati posledni zaznamu logu
   *
   * @return array
   */
  abstract public function getLastRecord();

  /**
   * Celkovy pocet zaznamu v logu
   *
   * @return int
   */
  abstract public function count();

  /**
   * Zpristupni SQL (poku je to mozne)
   *
   * @param  mixed  $params
   * @return bool
   */
  abstract public function enableSql($params = NULL);

  /**
   * Zda je SQL mod k dispozici
   *
   * @return bool
   */
  abstract public function isSqlAvailable();

  /**
   * Provede SQL dotaz
   *
   * @param  string  $query
   * @return mixed
   */
  abstract public function sqlQuery($query);
}
