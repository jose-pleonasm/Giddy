<?php

/**
 * Rozhrani pro praci s DBMS
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    $Id: DBI.php, 2011-03-03 10:38 $
 */


/**
 * Zakladni vyjimka DBI
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.2
 * @since      Trida je pristupna od verze 0.5
 */
class DbException extends RuntimeException
{
  /**
   * SQL dotaz vazici se k vyjimce
   *
   * @var string
   */
  protected $query;

  /**
   * Nastavba nad beznou metodou s parametrem $query
   *
   * @param  string  $message
   * @param  mixed   $code
   * @param  string  $query
   */
  public function __construct ($message, $code = 0, $query = NULL)
  {
    parent::__construct($message, (int) $code);
    $this->query = $query;
  }

  /**
   * Vrati specifikovany dotaz
   *
   * @return string odpovidajici query dotaz
   */
  public function getQuery ()
  {
    return $this->query;
  }
}

/**
 * DBI obal nad DbException
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI_Exception extends DbException {}

/**
 * Vyjimka pri selhani pripojeni k DBMS
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI_ConnectException extends DBI_Exception
{
  public function __construct ($message, $code = 0)
  {
    parent::__construct($message, (int) $code);
  }
}

/**
 * Vyjimka znakove sady
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI_CharsetException extends DBI_Exception
{
  protected $charset;

  public function __construct ($message, $code, $charset)
  {
    parent::__construct($message, (int) $code);
    $this->charset = $charset;
  }

  public function getCharset ()
  {
    return $this->charset;
  }
}


/**
 * Jednoducha trida pro inicializaci konkretni tridy
 * dle zvoleneho DBMS
 *
 * Inspirovano PEAR::DB
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI
{
  const VERSION = '1.9.2';

  const RESULT_O  = 'OBJECT';
  const RESULT_AI = 'ARRAY_N';
  const RESULT_AA = 'ARRAY_A';

  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct ()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * Vrati instanci odpovidaji implementace DBI_General
   *
   * @param  string  $type    pozadovany zdroj
   * @param  string  $user    pristupove jmeno
   * @param  string  $pass    odpovidajici heslo
   * @param  string  $dbhost  hostitelsky stroj
   * @param  string  $dbname  nazev databaze
   * @param  string  $charset kodovani spojeni
   * @return DBI_Common  odpovidajici objekt
   */
  public static function factory ($type, $user, $pass, $dbhost, $dbname, $charset = false)
  {
    $type = ucfirst(strtolower($type));
    $classname = "DBI_${type}";

    Base::import('DBI/' . $type);
    return new $classname($user, $pass, $dbhost, $dbname, $charset);
  }
}
