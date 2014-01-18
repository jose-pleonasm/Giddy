<?php

/**
 * Nejruznejsi pomocne funkce
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: tools.php, 2011-02-02 16:43 $
 */


/**
 * Pomocne funkce
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8
 * @since      Trida je pristupna od verze 0.8
 * @static
 */
final class gTools
{
  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * Naplni definovane casti (pr: %naplnit%) z $subject odpovidajici hodnotou z $data (tj: $data['naplnit'])
   *
   * @param  string  $subject
   * @param  array   $data
   * @param  bool    $useFullFormat  @since 2010-02-10 12:25
   * @return string
   */
  public static function imbue($subject, $data, $useFullFormat = true)
  {
    if ($useFullFormat) {
      preg_match_all('/%([a-z0-9_-]*)%/i', $subject, $matches);
    } else {
      preg_match_all('/%([a-zA-Z0-9]+)/', $subject, $matches);
    }
    if (!empty($matches)) {
      for ($i = 0; $i < count($matches[0]); $i++) {
        $buffer = isset($data[$matches[1][$i]]) ? $data[$matches[1][$i]] : $matches[0][$i];
        $subject = str_replace($matches[0][$i], $buffer, $subject);
      }
    }
    return $subject;
  }

  /**
   * Zakladni kontrola korektnosti nazvu souboru
   *
   * @param  string  $fileName
   * @return bool
   */
  public static function isCorrectFileName($fileName)
  {
    return eregi("^[a-z0-9\._-]+\.[a-z0-9]{3}$", $fileName);
  }

  /**
   * PHP => MySQL symboly pro casove funkce
   *
   * Prevede zastupne znaky casu z PHP (napr. fce date) do MySQL (napr. fce date_format) formatu
   *
   * @param  string  $phpDateFormat
   * @return string
   */
  public static function phpToSqlDateFormat($phpDateFormat)
  {
    $map = array(
      'd' => '%d',
      'l' => '%W',
      'n' => '%c',
      'm' => '%m',
      'j' => '%e',
      'H' => '%H',
      'G' => '%H',
      'i' => '%i',
      's' => '%s',
      'Y' => '%Y',
      'u' => '%f',
      'h' => '%h',
      'a' => '%p',
      'A' => '%p'
    );
  
    $result = $phpDateFormat;
    foreach ($map as $phpFormat => $mysqlFormat) {
      $result = str_replace($phpFormat, $mysqlFormat, $result);
    }
    return $result;
  }

  /**
   * Prevede datum z CZ formatu do SQL formatu
   *
   * @param  string $czDate
   * @return string
   */
  public static function czToSqlDate($czDate)
  {
    list($day, $month, $year) = explode('.', $czDate);
    return $year . '-' . $month . '-' . $day;
  }

  /**
   * Prevede datum v SQL formatu do ceskeho "plneho" formatu
   *
   * @param  string  $sqlDate
   * @return string
   */
  public static function sqlToFullCzDate($sqlDate)
  {
    $months = array(
      1 => 'ledna',
      2 => 'února',
      3 => 'března',
      4 => 'dubna',
      5 => 'května',
      6 => 'června',
      7 => 'července',
      8 => 'srpna',
      9 => 'září',
      10 => 'října',
      11 => 'listopadu',
      12 => 'prosince'
    );
    list($date, $time) = explode(' ', $sqlDate);
    list($year, $month, $day) = explode('-', $date);
    list($hour, $minute, $second) = explode(':', $time);
    if (strpos($month , '0') === 0) {
      $month = substr($month, 1);
    }
    if (strpos($day , '0') === 0) {
      $day = substr($day, 1);
    }
    if (strpos($hour , '0') === 0) {
      $hour = substr($hour, 1);
    }
    return $day . '.&nbsp;' . $months[$month] . '&nbsp;' . $year . ', ' . $hour . ':' . $minute . ':' . $second;
  }

  /**
   * Prevede cislo na cenu = upravy format
   *
   * @param  integer $int          vstupni cislo formatu: 1526307
   * @param  string  $thousandsSep oddelovac tisicu
   * @param  string  $decPoint     desetina carka/cokoliv
   * @return string  vystupni (stejne) cislo formatu napr: 1 526 307,00
   */
  public static function intToPrice($int, $thousandsSep = ' ', $decPoint = ',', $numDecimals = 2)
  {
    if (isset($int)) {
      return number_format($int, $numDecimals, $decPoint, $thousandsSep);
    } else {
      return '-';
    }
  }

  /**
   * Zkrati text
   *
   * Odstrani tagy a co nejsetrneji zkrati retez na (maximalne) specifikovanou delku
   *
   * @param  string  $text           text
   * @param  string  $length         max delka textu
   * @param  boolean $continueSymbol zda/jaky pridat text symbolizujici zkraceni textu
   * @return string  zkraceny text
   */
  public static function cutText($text, $length = 80, $continueSymbol = false)
  {
    if ($continueSymbol) {
      $continue = is_string($continueSymbol) ? $continueSymbol : ' ...';
    } else {
      $continue = '';
    }

    $text = strip_tags($text);

    if (strlen($text) <= $length) {
      return $text;
    } else {
      $spacePos = strrpos(substr($text, 0, $length), ' ');
      if ($spacePos !== false) {
        return substr($text, 0, $spacePos) . $continue;
      }
    }
    return substr($text, 0, $length) . $continue;
  }

  /**
   * Osetri vstupni text pro vystup jako HTML
   * 
   * (aby se zobrazoval v "stejne" podobe jako text)
   *
   * @param  string  $text
   * @return string
   */
  public static function htmlEncode($text)
  {
    return nl2br(htmlspecialchars($text));
  }

  /**
   * Osetri retezec pro URL
   *
   * @param  string  $string
   * @return string
   */
  public static function urlEncode($text)
  {
    return rawurlencode($text);
  }

  /**
   * Upravy text pro pouziti v javascriptu jako retezec (musi byt na jedne radce)
   *
   * @param  string  $text
   * @return string
   */
  public static function textToJsString($text)
  {
    $text = str_replace("\r\n", '\n', $text);
    $text = str_replace("\n", '\n', $text);
    return $text;
  }

  /**
   * Prevede adresu do absolutniho formatu
   *
   * @param  string  $path    adresa
   * @param  string  $dirSep  separator
   * @return string  normalizovana adresa
   */
  public static function truepath($path, $dirSep = DIRECTORY_SEPARATOR)
  {
    // filtry
    if ($path == './') {
      return '.' . $dirSep;
    } elseif ($path == '.\\') {
      return '.' . $dirSep;
    } elseif ($path == '../') {
      return '..' . $dirSep;
    } elseif ($path == '..\\') {
      return '..' . $dirSep;
    }
    // pomocne operace
    $pre  = '';
    $post = '';
    if (substr($path, 0, 1) == '/') {
      $pre = $dirSep;
    } elseif (substr($path, 0, 7) == 'http://') {
      $path = substr($path, 7);
      $pre = 'http://';
    } elseif (substr($path, 0, 2) == './'
              || substr($path, 0, 2) == '.\\') {
      $pre = '.' . $dirSep;
    } elseif (substr($path, -2) == '../'
              || substr($path, -2) == '..\\') {
      $pre = '..' . $dirSep;
    }
    if (substr($path, -1) == '/'
        || substr($path, -1) == '\\') {
      $post = $dirSep;
    }
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('\\', '/'), $dirSep, $path);
    $parts = array_filter(explode($dirSep, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.'  == $part) {
        continue;
      }
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }
    $path = $pre . implode($dirSep, $absolutes) . $post;
    // konecne upravy
    if ($path == $dirSep.$dirSep) {
      $path = $dirSep;
    }

    return $path;
  }

  /**
   * Serializacni metoda
   *
   * @param  mixed  $data
   * @return string
   */
  public static function serialize($data)
  {
    return json_encode($data);
  }

  /**
   * Inverzni metoda k self::serialize()
   *
   * @param  string  $string
   * @return mixed
   */
  public static function unserialize($string)
  {
    return json_decode($string, true);
  }

  /**
   * Vrati nazev prohlizece/bota
   *
   * @param  string  $userAgent
   * @return string
   */
  public static function getAgentName($userAgent)
  {
    $userAgent = strtolower($userAgent);
  
    if ($userAgent == '') {
      return '-none-';
    } elseif (strstr($userAgent, 'firefox')) {
      return 'Firefox';
    } elseif (strstr($userAgent, 'opera')) {
      return 'Opera';
    } elseif (strstr($userAgent, 'msie')) {
      return 'IE';
    } elseif (strstr($userAgent, 'konqueror')) {
      return 'Konqueror';
    } elseif (strstr($userAgent, 'lynx')) {
      return 'Lynx';
    } elseif (strstr($userAgent, 'chrome')) {
      return 'Chrome';
    } elseif (strstr($userAgent, 'safari')) {
      return 'Safari';
    } elseif (strstr($userAgent, 'nautilus')) {
      return 'Nautilus';
    } elseif (strstr($userAgent, 'googlebot')) {
      return 'GoogleBot';
    } elseif (strstr($userAgent, 'yahoo')) {
      return 'Yahoo!';
    } elseif (strstr($userAgent, 'seznam')) {
      return 'SeznamBot';
    } elseif (strstr($userAgent, 'morfeo')) {
      return 'CentrumBot';
    } elseif (strstr($userAgent, 'cuil')) {
      return 'Cuil robot';
    } elseif (strstr($userAgent, 'msn')) {
      return 'MSN';
    } elseif (strstr($userAgent, 'imagewalker')) {
      return 'ImageWalker';
    } elseif (strstr($userAgent, 'intelix')) {
      return 'Intelix';
    } elseif (strstr($userAgent, 'psbot')) {
      return 'psbot';
    } elseif (strstr($userAgent, 'java')) {
      return 'JAVA';
    } elseif (strstr($userAgent, 'cazoodlebot')) {
      return 'CazoodleBot';
    } elseif (strstr($userAgent, 'symbian')) {
      return 'Symbian OS (mobil)';
    } elseif (strstr($userAgent, 'gigabot')) {
      return 'GigaBot';
    } elseif (strstr($userAgent, 'surveybot')) {
      return 'SurveyBot';
    }  elseif (strstr($userAgent, 'dotbot')) {
      return 'DotBot';
    } elseif (strstr($userAgent, 'msrbot')) {
      return 'MSRBOT';
    } elseif (strstr($userAgent, 'mj12bot')) {
      return 'Majestic';
    } elseif (strstr($userAgent, 'netscape')) {
      return 'Netscape';
    } elseif (strstr($userAgent, 'mozilla')) {
      return 'Mozilla';
    } else {
      return '-other-';
    }
  }
}
