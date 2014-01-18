<?php

/**
 * DBI_Pgsql
 *
 * Implementace
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    $Id: Mysql.php, 2010-02-24 13:12 $
 */

Base::import('DBI_Common');

/**
 * Implementace DB_Common - pgsql
 *
 * Databasove rozhrani pro PostgreSQL
 *
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.1
 */
class DBI_Pgsql extends DBI_Common
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
   * Odkaz pro nativni fce pro praci s PostgreSQL
   *
   * @var resource
   */
  private $dbo;

  /**
   * Typ DB
   *
   * @var string
   */
  private static $dbtype = 'pgsql';

  /**
   * Udaje pro prijeni k PostgreSQL
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

    @$this->dbo = pg_connect('host=' . $this->dbhost . '
                              dbname=' . $this->dbname . '
                              user=' . $this->user . '
                              password=' . $this->pass);

    if(!$this->dbo) {
      throw new DBI_ConnectException('Spojení s PostgreSQL DB ' . $this->dbname . ' se nezdařilo');
    }
    // odpovidajici nastaveni priznaku spojeni
    $this->isConnect = true;
  }

  /**
   * Tato metoda u PostgreSQL postrada vyznam, asi!
   */
  public function setCharset ($charset)
  {
    return false;
  }

  /**
   * Osetri vstupni retezec pro PostgreSQL dotaz
   *
   * @param  string  $string  vstupni retezec
   * @return string  osetreny retezec
   */
  public function escapeString ($string)
  {
    if(!$this->isConnect()) {
      // jednoducha - zrejme nefunkcni nahrada!
      trigger_error('No correct escape method', E_USER_WARNING);
      return str_replace("'", "''", $string);
    }

    return pg_escape_string($this->dbo, $string);
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
    @$result = pg_query($this->dbo, $query);
    if ($result === false) {
      throw new DBI_Exception(pg_last_error($this->dbo), 0, $query);
    }

    //TODO: nasledujici prikazy overit (az po konec metody)!
    // zjisteni, zda jde o vyberove dotazy (vraci vysledna data/radky) -  neovlivnujici data
    if(preg_match("/^(select|show|describe)\s+/i", $query)) {
      // (mezi)zpracovani vysledku a naplneni (aktualni) vlastnosti
      $numRows = 0;
      while($row = @pg_fetch_object($result)) {
        // ulozeni radku/objektu do vlastniho pole
        $this->lastResult[$numRows] = $row;
        ++$numRows;
      }
    }

    // pocet vsech vybranych radku
    $this->numRows = @pg_num_rows($result);

    // pocet ovlivnenych radku
    $this->affectedRows = pg_affected_rows($result);

    // hodnota AUTO_INCREMENT posledniho INSERTu
    $this->insertId = @pg_last_oid($result);
    //TODO: ohlidat funkcnost
    if (preg_match("/^(insert)\s+/i", $query)) if (!$this->insertId) {
      $loid_r = @pg_query($this->dbo, 'SELECT lastval()');
      $loid   = @pg_fetch_assoc($loid_r);
      $this->insertId = $loid['lastval'];
      @pg_free_result($loid_r);
    }

    // uvolneni vysledku (jen pokud se jednalo o SELECT apod. = vysledek je mysqli objekt)
    if (!empty($result)) {
      @pg_free_result($result);
    }

    if ($this->debugAll) {
      $this->debug();
    }

    return true;
  }

  /**
   * Pri vice (spojenych) dotazech
   *
   * @param  string  $query  SQL dotaz
   * @return mixed
   */
  public function multiQuery ($query)
  {
    throw new BadMethodCallException('multiQuery is not available for PostgreSQL');
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
      $result = pg_close($this->dbo);
      $this->isConnect = !$result;
      return $result;
    }
  }
}
