<?php

Base::import('Collections_Comparable');

class InvalidDateException extends DomainException {}

class Date extends Common implements Comparable
{
  /**
   * Den v sekundach
   *
   * @var integer
   */
  const DAY_SEC = 86400;

  /**
   * Reprezentuje datum
   *
   * @var integer
   */
  protected $timestamp;

  /**
   * Inicializace
   *
   * @param  mixed    $day    den, nebo text urcujici datum (viz. strToTime()), nebo objekt Date
   * @param  integer  $month  mesic, nebo pocet dnu k posunuti (jen pokud je $day objektem Date)
   * @param  integer  $year   rok
   * @throws InvalidDateException
   */
  public function __construct ($day = NULL, $month = NULL, $year = NULL)
  {
    if (is_int($day) && $day > 31 && empty($month) && empty($year)) {
      $this->timestamp = $day;
      return;
    }
    if (is_string($day) && empty($month) && empty($year)) {
      $this->timestamp = strToTime($day);
      return;
    }
    if ($day instanceof Date) {
      $this->timestamp = $day->getTimestamp();
      if (is_int($month)) {
        $this->shiftOfDays($month);
      }
      return;
    }

    if (empty($day)) {
      $day = date('d');
    }
    if (empty($month)) {
      $month = date('m');
    }
    if (empty($year)) {
      $year = date('Y');
    }

    if (!checkdate($month, $day, $year)) {
      throw new InvalidDateException('Given date is not valid');
    }

    $this->timestamp = strToTime($year . '-' . $month . '-' . $day);
  }

  /**
   * Nastavy datum primo jako timestamp
   *
   * @param  integer  $timestamp  nova hodnota
   * @return integer  puvodni hodnota
   */
  public function setTimestamp ($timestamp)
  {
    $buffer = $this->timestamp;
    $this->timestamp = $timestamp;
    return $buffer;
  }

  /**
   * Posune datum o dny
   *
   * @param  integer  $days
   * @return void
   */
  public function shiftOfDays ($days)
  {
    $this->timestamp = $this->timestamp + $days * self::DAY_SEC;
  }

  /**
   * Vrati Unix timestamp tohoto datumu
   *
   * @return integer
   */
  public function getTimestamp ()
  {
    return $this->timestamp;
  }

  /**
   * Vrati den
   *
   * @return integer
   */
  public function getDay ()
  {
    return date('d', $this->timestamp);
  }

  /**
   * Vrati masic
   *
   * @return integer
   */
  public function getMonth ()
  {
    return date('m', $this->timestamp);
  }

  /**
   * Vrati rok
   *
   * @return integer
   */
  public function getYear ()
  {
    return date('Y', $this->timestamp);
  }

  /**
   * Vrati datum v specifikovanem formatu
   *
   * @param  string  $endian 
   * @param  string  $separator
   * @return string
   */
  public function getDate ($endian = 'big', $separator = '-')
  {
    if ($endian == 'little') {
      return date('d', $this->timestamp) . $separator
           . date('m', $this->timestamp) . $separator
           . date('Y', $this->timestamp);
    } elseif ($endian == 'big') {
      return date('Y', $this->timestamp) . $separator
           . date('m', $this->timestamp) . $separator
           . date('d', $this->timestamp);
    // middle
    } else {
      return date('m', $this->timestamp) . $separator
           . date('d', $this->timestamp) . $separator
           . date('Y', $this->timestamp);
    }
  }

  /**
   * Vrati datum dle specifikovaneho formatu (viz. date())
   *
   * @param  string  $format
   * @return string
   */
  public function getFormatDate ($format = 'd.m.Y')
  {
    return date($format, $this->timestamp);
  }

  /**
   * Interpretuje metaznaky, ostatni text zachovan
   *
   * Metaznaky jsou stejne jako pro (viz) date(), ale jsou obaleny znakem '%', pr: %d%
   *
   * @param  string  $string
   * @return string
   */
  public function execute ($string)
  {
    preg_match_all('(%([a-zA-Z]){1}%)', $string, $regs);
    $lambda = create_function('$f', 'return date("$f", ' . $this->timestamp . ');');
    $results = array_map($lambda, $regs[1]);
    $string = str_replace($regs[0], $results, $string);
    return $string;
  }

  /**
   * Porovna toto datum se specifikovanym
   *
   * @param  Date  $obj
   * @return boolean
   * @throws ClassCastException
   */
  public function compareTo ($obj)
  {
    if (!($obj instanceof Date)) {
      throw new ClassCastException('Given object not comparable as Date');
    }
    if ($this->equals($obj)) {
      return 0;
    }
    return ($this->timestamp < $obj->timestamp) ? -1 : 1;
  }

  public function hashCode()
  {
    return $this->timestamp;
  }

  /**
   * Vrati datum ve formatu ISO 8601
   *
   * @return string
   */
  public function __toString ()
  {
    return $this->getDate('big', '-');
  }
}
