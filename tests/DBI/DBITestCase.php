<?php
/**
 * Testovaci skript
 *
 * @link      https://github.com/jose-pleonasm/Giddy
 * @category  Test
 * @package   Giddy
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '../../');
  require_once(TEST_ROOT . 'Base.php');
  Base::setRoot(TEST_ROOT);

  define('DB_TYPE', 'mysql');
  define('DB_HOST', 'localhost');
  define('DB_USER', 'jose');
  define('DB_PASS', 'jose');
  define('DB_ENCODING', 'utf8');
  define('DB_NAME_DBI', '_test');
}

Base::import('DBI');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class DBITestCase extends PHPUnit_Framework_TestCase
{
  const DBMS    = DB_TYPE;
  const USER    = DB_USER;
  const PASS    = DB_PASS;
  const HOST    = DB_HOST;
  const DBNAME  = DB_NAME_DBI;
  const CHARSET = DB_ENCODING;

  private static $lastId;

  public function testConnect ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $db->connect();
    $this->assertTrue($db->isConnect(), 'metoda connect: kontrola negativni');
  }

  public function testEscapeString ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME, self::CHARSET);
    $escapeString = $db->escapeString("Mark's nick");
    if(self::DBMS == 'mysql') {
      $goodString = "Mark\'s nick";
    }
    elseif(self::DBMS == 'pgsql') {
      $goodString = "Mark''s nick";
    }
    $this->assertEquals($escapeString, $goodString, 'metoda escapeString: spatny navrat');
  }

  public function testQuery ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $db->query("UPDATE t_data SET updated = NOW() WHERE id > 2");
    $this->assertEquals($db->affectedRows(), 2, 'metoda query: pocet ovlivnenych radku je spatny');
  }

  public function testResults ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $results = $db->getResults("SELECT * FROM t_data ORDER BY id DESC", DBI::RESULT_AA);
    //fce trim kvuli postgresql - doplnuje retezec do "plne" delky
    $this->assertEquals(trim($results[3]['txt']), 'row 1', 'metoda getResults: spatny navrat');
  }

  public function testVar ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $this->assertEquals($db->getVar("SELECT max(id) FROM t_data"), 4, 'metoda getVar: spatny navrat');
  }

  public function testVarEmpty ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $this->assertEquals($db->getVar("SELECT id FROM t_data WHERE id > 4"), NULL, 'metoda getVar: spatny navrat');
  }

  public function testInsert ()
  {
    $txt = "nějaký text a '";
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME, 'utf8');
    // definovano budouci datum aby pri klauzuli "ORDER BY updated DESC" byl tento zaznam vracen jako prvni
    $db->query("INSERT INTO t_data (txt, updated) VALUES ('" . $db->escapeString($txt) . "', '2038-01-19 03:14:07')");
    self::$lastId = $db->insertId();
    $this->assertEquals($db->affectedRows(), 1, 'metoda query: spatny insert');
    $this->assertEquals($db->getVar("SELECT txt FROM t_data ORDER BY updated DESC LIMIT 1"), $txt, 'metoda query: vlozena hodnota neni korektni');
  }

  public function testDelete ()
  {
    if (empty(self::$lastId)) {
      trigger_error('Last ID not specified', E_USER_WARNING);
    }

    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME, 'utf8');
    $db->query("DELETE FROM t_data WHERE id = " . self::$lastId);
    $this->assertEquals($db->affectedRows(), 1, 'metoda query: spatny delete');
  }

  public function testDBIntegrity ()
  {
    $db = DBI::factory(self::DBMS, self::USER, self::PASS, self::HOST, self::DBNAME);
    $results = $db->getResults("SELECT * FROM t_data ORDER BY updated DESC, id", DBI::RESULT_AA);
    $this->assertEquals(trim($results[0]['txt']), 'row 3', 'integrita DB zrejme narusena nekorektni interpretaci INSERT/DELETE');
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('DBITestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>