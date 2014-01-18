<?php

/**
 * Obecny mechanizmus validace dat
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: GeneralValidator.php, 2011-07-27 16:38 $
 */

/**
 * Nespravny format popisujici strukturu dat urcenych k validaci
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class InvalidRuleException extends InvalidArgumentException {} 

/**
 * Validator
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class GeneralValidator
{
  /**
   * Regularni vyraz pro nick (uzivatelske jmeno)
   *
   * @var string
   */
  protected static $patternNick = '/^[a-z0-9][a-z0-9\._-]{2,20}$/';

  /**
   * Regularni vyraz pro email adresu
   *
   * vice na http://php.vrana.cz/kontrola-e-mailove-adresy.php
   *
   * @var string
   */
  protected static $patternEmail = '/^([[:alnum:]._-]+)@([[:alnum:]._-]+)\.([[:alpha:]]{2,4})$/';

  /**
   * Regularni vyraz pro URL adresu
   *
   * zdroj: http://www.regularnivyrazy.info/url.html (upraveny pro pripad: http://jose.cz/)
   *
   * @var string
   */
  protected static $patternUrl = ';^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&%\$\-]+)*@)?((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.[a-zA-Z]{2,4})(\:[0-9]+)?(/)?(/[^/][a-zA-Z0-9\.\,\?\'\\/\+&%\$#\=~_\-@]*)*$;';

  /**
   * Regularni vyraz pro datum (MySQL format)
   *
   * @var string
   */
  protected static $patternDate = '/^[0-9]{4}-((0?[0-9])|(1[012]))-(([0-2]?[0-9])|(3[01]))$/';

  /**
   * Regularni vyraz pro datum (CZ format)
   *
   * @var string
   */
  protected static $patternDateCz = '/^(([0-2]?[0-9])|(3[01]))\.((0?[0-9])|(1[012]))\.[0-9]{4}$/';

  /**
   * Regularni vyraz pro cas (format HH:mm:ss)
   *
   * @var string
   */
  protected static $patternTime = '/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/';

  /**
   * Seznam pmoznych typu k validaci
   *
   * @var array
   */
  protected static $availableTypes = array(
    'string',
    'integer',
    'float',
    'numeric',
    'rational',
    'boolean',
    'array',
    'object',
    'enum',
    'value',
    'date',
    'date-cz',
    'time',
    'nick',
    'email',
    'url'
  );

  /**
   * Popisuje pozadovane vlastnosti
   * validovanych dat
   *
   * @var array
   */
  protected static $validAs;

  /**
   * Nacte pravidla pro validaci specifikovanych dat
   *
   * @param  string  $rule  retez urcujici pravidla pro validaci dat
   * @throws InvalidRuleException
   */
  protected static function parseRule ($rule)
  {
    if (strpos($rule, ':') !== false) {
      list($type, $parametersString) = explode(':', $rule);

      if (strpos($parametersString, '<>') !== false) {
        self::$validAs['parameters'] = explode('<>', $parametersString);
      } else {
        self::$validAs['parameters'] = $parametersString;
      }
    } else {
      $type = $rule;
      self::$validAs['parameters'] = NULL;
    }

    if (!self::isAvailableType($type)) {
      throw new InvalidRuleException('Type ' . $type . ' not supported!');
    }
    self::$validAs['type'] = $type;
  }

  /**
   * Provede validaci dat podle specifikovanych validacnich pravidel
   *
   * @param  string  $rule  retez urcujici pravidla pro validaci dat
   * @param  mixed   $subject     data
   * @return boolean zda data odpovidaji specifikovanym pravidlum
   */
  public static function isValid ($rule, $subject)
  {
    self::parseRule($rule);

    // string
    if (self::$validAs['type'] == 'string') {
      if (strlen($subject) >= self::$validAs['parameters'][0]
          && strlen($subject) <= self::$validAs['parameters'][1]) {
        return true;
      } else {
        return false;
      }
    }
    // boolean
    elseif (self::$validAs['type'] == 'boolean') {
      if ($subject === true || $subject === false) {
        return true;
      } else {
        return false;
      }
    }
    // integer
    elseif (self::$validAs['type'] == 'integer') {
      if (preg_match('/^-?[0-9]+$/', $subject)) {
        if ($subject >= self::$validAs['parameters'][0]
            && $subject <= self::$validAs['parameters'][1]) {
          return true;
        }
      }
      return false;
    }
    // float (strict)
    elseif (self::$validAs['type'] == 'float') {
      if (is_float($subject)) {
        if ($subject >= self::$validAs['parameters'][0]
            && $subject <= self::$validAs['parameters'][1]) {
          return true;
        }
      }
      return false;
    }
    // numeric (integer | float)
    elseif (self::$validAs['type'] == 'numeric') {
      if (preg_match('/^-?[0-9]+(.[0-9]+)?$/', $subject)) {
        if ($subject >= self::$validAs['parameters'][0]
            && $subject <= self::$validAs['parameters'][1]) {
          return true;
        }
      }
      return false;
    }
    // rational (integer | float)
    elseif (self::$validAs['type'] == 'rational') {
      trigger_error('This way is condemnation! Use rule numeric', E_USER_NOTICE);
      if (preg_match('/^-?[0-9]+(.[0-9]+)?$/', $subject)) {
        if ($subject >= self::$validAs['parameters'][0]
            && $subject <= self::$validAs['parameters'][1]) {
          return true;
        }
      }
      return false;
    }
    // array
    elseif (self::$validAs['type'] == 'array') {
      if (!is_array($subject)) {
        return false;
      }
      if (count($subject) >= self::$validAs['parameters'][0]
          && count($subject) <= self::$validAs['parameters'][1]) {
        return true;
      } else {
        return false;
      }
    }
    // object
    elseif (self::$validAs['type'] == 'object') {
      if (!is_object($subject)) {
        return false;
      }
      if (!empty(self::$validAs['parameters'])) {
        return (boolean) ($subject instanceof self::$validAs['parameters']);
      }
      return true;
    }
    // enum
    elseif (self::$validAs['type'] == 'enum') {
      if (!is_array(self::$validAs['parameters'])) {
        return self::$validAs['parameters'] == $subject;
      }
      $find = false;
      foreach (self::$validAs['parameters'] as $parameter) {
        if ($subject == $parameter) {
          $find = true;
        }
      }
      return $find;
    }
    // const
    elseif (self::$validAs['type'] == 'value') {
      return (boolean) ($subject == self::$validAs['parameters']);
    }
    // user nick
    elseif (self::$validAs['type'] == 'nick') {
      return preg_match(self::$patternNick, $subject) === 1;
    }
    // email address
    elseif (self::$validAs['type'] == 'email') {
      return (boolean) preg_match(self::$patternEmail, $subject);
    }
    // MySQL date
    elseif (self::$validAs['type'] == 'date') {
      if (preg_match(self::$patternDate, $subject)) {
        if (is_array(self::$validAs['parameters'])) {
          list($lYear, $lMon, $lDay) = explode('-', self::$validAs['parameters'][0]);
          list($uYear, $uMon, $uDay) = explode('-', self::$validAs['parameters'][1]);
          list($aYear, $aMon, $aDay) = explode('-', $subject);
          $lTime = mktime(0, 0, 0, $lMon, $lDay, $lYear);
          $uTime = mktime(0, 0, 0, $uMon, $uDay, $uYear);
          $aTime = mktime(0, 0, 0, $aMon, $aDay, $aYear);
          if ($lTime <= $aTime && $aTime <= $uTime) {
            return true;
          } else {
            return false;
          }
        } else {
          return true;
        }
      } else {
        return false;
      }
    }
    // CZ date
    elseif (self::$validAs['type'] == 'date-cz') {
      if (preg_match(self::$patternDateCz, $subject)) {
        if (is_array(self::$validAs['parameters'])) {
          list($lDay, $lMon, $lYear) = explode('.', self::$validAs['parameters'][0]);
          list($uDay, $uMon, $uYear) = explode('.', self::$validAs['parameters'][1]);
          list($aDay, $aMon, $aYear) = explode('.', $subject);
          $lTime = mktime(0, 0, 0, $lMon, $lDay, $lYear);
          $uTime = mktime(0, 0, 0, $uMon, $uDay, $uYear);
          $aTime = mktime(0, 0, 0, $aMon, $aDay, $aYear);
          if ($lTime <= $aTime && $aTime <= $uTime) {
            return true;
          } else {
            return false;
          }
        } else {
          return true;
        }
      } else {
        return false;
      }
    }
    // time
    elseif (self::$validAs['type'] == 'time') {
      if (preg_match(self::$patternTime, $subject)) {
        if (is_array(self::$validAs['parameters'])) {
          list($lHour, $lMin, $lSec) = explode('-', self::$validAs['parameters'][0]);
          list($uHour, $uMin, $uSec) = explode('-', self::$validAs['parameters'][1]);
          list($aHour, $aMin, $aSec) = explode(':', $subject);
          $lTime = mktime($lHour, $lMin, $lSec, 0, 0, 0);
          $uTime = mktime($uHour, $uMin, $uSec, 0, 0, 0);
          $aTime = mktime($aHour, $aMin, $aSec, 0, 0, 0);
          if ($lTime <= $aTime && $aTime <= $uTime) {
            return true;
          } else {
            return false;
          }
        } else {
          return true;
        }
      } else {
        return false;
      }
    }
    // URL
    elseif (self::$validAs['type'] == 'url') {
      return (boolean) preg_match(self::$patternUrl, $subject);
    }
  }

  /**
   * Zda je specifikovany typ mozne validovat pomoci teto tridy
   *
   * @param  string  $type  nazev typu
   * @return bool
   */
  public static function isAvailableType ($type)
  {
    return in_array($type, self::$availableTypes);
  }

  /**
   * Vrati seznam moznych typu
   *
   * @return array
   */
  public static function getAvailableTypes ()
  {
    return self::$availableTypes;
  }

  /**
   * Konstruktor objektu
   *
   * Vytvareni objektu teto tridy neni treba
   *
   * @deprecated
   */
  public function __construct ()
  {
    trigger_error('This way is condemnation! Use as static class', E_USER_NOTICE);
  }
}
