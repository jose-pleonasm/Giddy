<?php

/**
 * DBI_Mysql
 *
 * Implementace
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    $Id: Mysql.php, 2010-05-05 15:35 $
 */

Base::import('DBI_Common');

/**
 * Implementace DB_Common - mysql
 *
 * Databasove rozhrani pro MySQL
 * pracujici s PHP rozsirenim mysqli
 *
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI_Mysql extends DBI_Common
{
  /**
   * Uziv. jmeno
   *
   * @var string
   */
  private $user;

  /**
   * Heslo
   *
   * @var string
   */
  private $pass;

  /**
   * Host
   *
   * @var string
   */
  private $dbhost;

  /**
   * Nazev DB
   *
   * @var string
   */
  private $dbname;

  /**
   * Nazev znakove sady, ktera se ma pouzit
   * pro spojeni
   *
   * @var string
   */
  private $charset;

  /**
   * Nativni objekt pro praci s MySQL - mysqli
   *
   * @var MySQLi
   */
  private $dbo;

  /**
   * Typ DB
   *
   * @var string
   */
  private static $dbtype = 'mysql';

  /**
   * Udaje pro prijeni k MySQL
   *
   * @param  string  $user    uziv. jmeno
   * @param  string  $pass    heslo k DB
   * @param  string  $dbhost  hostitel
   * @param  string  $dbname  jmeno DB
   * @param  string  $charset retezec identifikujici pozadovane kodovani
   */
  public function __construct ($user, $pass, $dbhost, $dbname, $charset = false)
  {
    $this->user    = $user;
    $this->pass    = $pass;
    $this->dbhost  = $dbhost;
    $this->dbname  = $dbname;
    $this->charset = $charset;
  }

  /**
   * Zridi spojeni s MySQL
   *
   * @return void
   * @throws DBI_ConnectException  pokud se nepodari spojit s DB
   */
  public function connect ()
  {
    if ($this->isConnect()) {
      return true;
    }

    // vycisteni cache
    $this->flush();

    @$this->dbo = new mysqli($this->dbhost, $this->user, $this->pass, $this->dbname);
    if (mysqli_connect_errno()) {
      $this->dbo = NULL;
      throw new DBI_ConnectException(mysqli_connect_error(), mysqli_connect_errno());
    }
    if (!empty($this->charset)) {
      $this->setCharset($this->charset);
    }
    // odpovidajici nastaveni priznaku spojeni
    $this->isConnect = true;
  }

  /**
   * Nastavi kodovani spojeni
   *
   * @param  string  $charset      znakova sada
   * @return void
   * @throws DBI_CharsetException  pokud se nepodari nastavit kodovani spojeni (selhani odpovidajiciho SQL dotazu)
   */
  public function setCharset ($charset)
  {
    $this->charset = $charset;

    if ($this->isConnect()) {
      return;
    }

    $this->dbo->query("SET NAMES '" . $this->charset . "'");
    if($this->dbo->errno) {
      throw new DBI_CharsetException($this->dbo->error, $this->dbo->errno, $this->charset);
    }
  }

  /**
   * Osetri vstupni retezec pro MySQL dotaz
   *
   * @param  string  $string  vstupni retezec
   * @return string  osetreny retezec
   */
  public function escapeString ($string)
  {
    //pokud neni navazano spojeni pouzije se "pouze" mysql_escape_string
    if (!$this->isConnect()) {
      return mysql_escape_string($string);
    }

    return $this->dbo->real_escape_string($string);
  }

  /**
   * Provede SQL dotaz a provede zakladni zpracovani vysledku dotazu
   *
   * @param  string  $query  SQL dotaz
   * @return bool    true pokud dotaz probehl uspesne
   * @throws DBI_Exception  pokud nastane SQL chyba pri vykonani dotazu
   */
  public function query ($query)
  {
    if (!$this->isConnect()) {
      $this->connect();
    }

    // osetreni dotazu (pro fci preg_match)
    $query = trim($query);

    // zalogovani posledniho (aktualniho) dotazu
    $this->lastQuery = $query;
    // odstraneni predesleho vysledku (jinak by reprezentoval neaktualni stav)
    $this->lastResult = NULL;
    // nastaveni vychozich hodnot ostatnich parametru
    $this->numRows = 0;
    $this->affectedRows = 0;
    $this->insertId = NULL;

    // vykonnani dotazu a ulozeni vysledku do (docasne) promenne
    @$result = $this->dbo->query($query);
    if ($this->dbo->errno) {
      throw new DBI_Exception($this->dbo->error, $this->dbo->errno, $query);
    }

    // zjisteni, zda jde o vyberove dotazy (vraci vysledna data/radky) -  neovlivnujici data
    if (preg_match("/^(select|show|describe)\s+/i", $query)) {
      // (mezi)zpracovani vysledku a naplneni (aktualni) vlastnosti
      $numRows = 0;
      while ($row = @$result->fetch_object()) {
        // ulozeni radku/objektu do vlastniho pole
        $this->lastResult[$numRows] = $row;
        ++$numRows;
      }
    }

    // pokud je objekt = jednalo se SQL dotaz typu SELECT
    if (is_object($result)) {
      // pocet vsech vybranych radku
      if ($result->num_rows) {
        $this->numRows = $result->num_rows;
      }
      // uvolneni vysledku
      $result->free_result();
    }

    // pocet ovlivnenych radku
    $this->affectedRows = $this->dbo->affected_rows;

    // hodnota AUTO_INCREMENT posledniho INSERTu
    $this->insertId = $this->dbo->insert_id;

    if ($this->debugAll) {
      $this->debug();
    }

    return true;
  }

  /**
   * Pro vice (spojenych) dotazu
   *
   * @param  string  $query  SQL dotaz
   * @return mixed
   */
  public function multiQuery ($query)
  {
    trigger_error('This is raw version multiQuery!', E_USER_WARNING);

    if (!$this->isConnect()) {
      $this->connect();
    }

    // osetreni dotazu
    $query = trim($query);
    if (empty($query)) {
      return false;
    }

    // pripraveni prostredi
    $this->flush();
    $this->lastQuery = $query;
    $this->numRows = 0;
    $this->affectedRows = 0;

    if ($this->dbo->multi_query($query) === false) {
      throw new DBI_Exception($this->dbo->error, $this->dbo->errno, $query);
    }
    //TODO: multi query nejak nefunguje

    return $this->dbo;
  }

  /**
   * Vrati nazev zvolene DB
   *
   * @return string  nazev DB
   */
  public function getDbName ()
  {
    return $this->dbname;
  }

  /**
   * Vrati typ DBMS
   *
   * @return string  typ DB
   */
  public function getDbType ()
  {
    return self::$dbtype;
  }

  /**
   * Ukonci spojeni s DB
   *
   * @return bool
   */
  public function close ()
  {
    if ($this->isConnect()) {
      $result = $this->dbo->close();
      $this->isConnect = !$result;
      return $result;
    }
  }
}
