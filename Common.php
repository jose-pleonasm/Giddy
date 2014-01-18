<?php

/**
 * Zakladni objekt frameworku
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Common.php, 2011-04-20 12:20 $
 */

/**
 * Zakladni predek vsech instancovatelnych trid frameworku
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.4
 * @since      Trida je pristupna od verze 0.1
 */
abstract class Common
{
  /**
   * Prevede promennou na popisny retezec
   *
   * @uses   is_bool()
   * @uses   is_int()
   * @uses   is_float()
   * @uses   is_string()
   * @uses   is_array()
   * @uses   is_object()
   * @uses   get_class()
   * @uses   is_resource()
   * @uses   get_object_vars()
   * @uses   get_resource_type()
   * @uses   Object::varToStr()
   * @param  mixed  $var
   * @return string
   */
  protected static function varToStr($var)
  {
    if (is_bool($var)) {
      return "bool(" . ($var ? 'true' : 'false') . ")";
    } elseif ($var === NULL) {
      return "NULL";
    } elseif (is_int($var)) {
      return "int($var)";
    } elseif (is_float($var)) {
      return "float($var)";
    } elseif (is_string($var)) {
      return "string($var)";
    } elseif (is_array($var)) {
      $s = "array(";
      $_first = true;
      foreach ($var as $k => $v) {
        if (!$_first) {
          $s .= ",";
        }
        $s .= $k . "=>" . self::varToStr($v);
        $_first = false;
      }
      $s .= ")";
      return $s;
    } elseif (is_object($var)) {
      $s = get_class($var) . "(";
      $var_vars = get_object_vars($var);
      $_first = true;
      foreach ($var_vars as $var_k => $var_v) {
        if (!$_first) {
          $s .= ";";
        }
        $s .= $var_k . ":" . self::varToStr($var_v);
        $_first = false;
      }
      $s .= ")";
      return $s;
    } elseif (is_resource($var)) {
      return "resource(" . get_resource_type($var) . ")";
    } else {
      return "unknown type";
    }
  }

  /**
   * Prevede objekt na popisny retezec
   *
   * @uses   get_class()
   * @uses   get_object_vars()
   * @uses   Object::varToStr()
   * @param  object  $var
   * @return string
   */
  protected static function objToStr($obj)
  {
    $s = get_class($obj) . '@';
    $vars = get_object_vars($obj);
    $_first = true;
    foreach ($vars as $name => $var) {
      if (!$_first) {
        $s .= '#';
      }
      $s .= $name . ':' . self::varToStr($var);
      $_first = false;
    }
    return $s;
  }

  /**
   * Vrati jmeno tridy tohoto objektu
   *
   * @return string
   */
  final public function getClass()
  {
    return  get_class($this);
  }

  /**
   * Vrati reflexni objekt tohoto objektu
   *
   * @uses   ReflectionObject
   * @return ReflectionObject
   */
  final public function getReflection()
  {
    return new ReflectionObject($this);
  }

  /**
   * Porovna specifikovany objekt s timto, zda jsou shodne
   *
   * @param  mixed   $object  specificky objekt
   * @return boolean          true jsou shodne, jinak false
   */
  public function equals($object)
  {
    return ($this == $object);
  }

  /**
   * Vrati hash (crc32) tohoto objektu
   *
   * @uses   function_exists()
   * @uses   md5()
   * @uses   spl_object_hash()
   * @return integer  hash tohoto objektu
   */
  public function hashCode()
  {
    //return crc32(self::objToStr($this));
    if (!function_exists('spl_object_hash')) {
      return md5((string) $this);
    } else {
      return spl_object_hash($this);
    }
  }

  /**
   * Vrati tento objekt jako retezec
   *
   * @uses   Object::objToStr()
   * @return string
   */
  public function __toString()
  {
    return self::objToStr($this);
  }
}
