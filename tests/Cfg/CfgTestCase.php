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
}

Base::import('Cfg');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class CfgTestCase extends PHPUnit_Framework_TestCase
{
  public function testCfgBase ()
  {
    $cfg = new Cfg();
    $cfg->db = 'mysql';
    $cfg->pi = 3.14159;

    $this->assertEquals($cfg->db, 'mysql', 'Neodpovidajici hodnota - TEST 1');
    $this->assertEquals($cfg->pi, 3.14159, 'Neodpovidajici hodnota - TEST 2');
  }

  public function testConstructor1 ()
  {
    $array = array(
      'param1' => 'value1',
      'param2' => 'value2',
      'pi'     => 3.14159
    );
    $cfg = new Cfg($array);

    $this->assertEquals($cfg->param2, $array['param2'], 'Neodpovidajici hodnota - TEST 1');
  }

  public function testConstructor2 ()
  {
    Base::import('Collections/Hashtable');
    $map = new Hashtable();
    $map->put('string', '...text...');
    $map->put('float', 3.14159);
    $map->put('array', array(1, 2));
    $cfg = new Cfg($map);

    $this->assertEquals($cfg->float, $map->get('float'), 'Neodpovidajici hodnota - TEST 1');
  }

  public function testGet ()
  {
    $cfg = new Cfg();
    $cfg->set('user_cfg', new Cfg());
    $result = $cfg->get('user_cfg');
    $exc = false;
    try {
      $result = $cfg->get('foo');
    }
    catch (ArgumentOutOfRangeException $e) {
      $exc = true;
    }

    $this->assertTrue($exc, 'Pri pokusu o ziskani nenastavene hodnoty melo dojit k vyjimce ArgumentOutOfRangeException');
    $this->assertEquals($result, new Cfg(), 'Neodpovidajici hodnota');
  }

  public function testExtort ()
  {
    $cfg = new Cfg();
    $cfg->set('klic', 'hodnota');
    $result1 = $cfg->extort('klic', 'vychozi');
    $result2 = $cfg->extort('nomap1', 'default1');
    $cfg->extort('nomap2', 'default2');

    $this->assertEquals($result1, 'hodnota', 'Neodpovidajici hodnota - TEST 1');
    $this->assertEquals($result2, 'default1', 'Neodpovidajici hodnota - TEST 2');
    $this->assertEquals($cfg->nomap2, 'default2', 'Neodpovidajici hodnota - TEST 3');
    $this->assertEquals($cfg->extort('nomap3', 'default3'), 'default3', 'Neodpovidajici hodnota - TEST 4');
    $this->assertEquals($cfg->extort('neco'), NULL, 'Neodpovidajici hodnota - TEST 5');
  }

  public function testRedeclare1 ()
  {
    $cfg = new Cfg();
    $cfg->foo = 'puvodni';
    $exc = false;
    try {
      $cfg->foo = 'nova';
    }
    catch (Exception $e) {
      if ($e instanceof NotRewritableException) {
        $exc = true;
      }
    }

    $this->assertTrue($exc, 'Pri pokusu o prepsani hodnoty melo dojit k vyjimce NotRewritableException');
    $this->assertEquals($cfg->foo, 'puvodni', 'Neodpovidajici hodnota');
  }

  public function testRedeclare2 ()
  {
    $cfg = new Cfg();
    $cfg->isRewritable = true;
    $cfg->foo = 'puvodni';
    $cfg->foo = 'nova';

    $this->assertEquals($cfg->foo, 'nova', 'Neodpovidajici hodnota');
  }

  public function testRemove ()
  {
    $cfg = new Cfg();
    $cfg->param = 'test';
    $cfg->remove('param');
    $exc = false;
    try {
      $result = $cfg->param;
    }
    catch (ArgumentOutOfRangeException $e) {
      $exc = true;
    }
    $cfg->foo = 'puvodni';
    $cfg->remove('foo');
    $cfg->foo = 'nova';

    $this->assertTrue($exc, 'Pri pokusu o ziskani odstranenne hodnoty melo dojit k vyjimce ArgumentOutOfRangeException');
    $this->assertEquals($cfg->foo, 'nova', 'Neodpovidajici hodnota');
  }

  public function testEmpty ()
  {
    $cfg = new Cfg();
    $cfg->value1 = '---';
    $cfg->value2 = '***';
    $cfg->value3 = '///';
    $result = $cfg->isEmpty();
    $cfg->clear();

    $this->assertFalse($result, 'Toho casu nema byt objekt prazdny');
    $this->assertTrue($cfg->isEmpty(), 'Objekt ma byt prazdny');
  }

  public function testCount ()
  {
    $cfg = new Cfg();
    $cfg->isRewritable = true;
    $cfg->value1 = '---';
    $cfg->value2 = '***';
    $cfg->value3 = '///';
    $cfg->value4 = '$$$';
    $cfg->remove('value4');
    $cfg->set('#4', 'v1');
    $cfg->set('#4', 'v2');

    $this->assertFalse($cfg->isEmpty(), 'Objekt nema byt prazdny');
    $this->assertEquals(count($cfg), 4, 'Neodpovidajici pocet polozek v objektu');
  }

  public function testArrayBehaviour ()
  {
    $cfg = new Cfg();
    $cfg['nejaky nazev'] = 'nejaka hodnota';
    $cfg['stredni'] = '***tajne***';
    $cfg['pi'] = 3.15;
    $ok = false;
    foreach ($cfg as $key => $value) {
      if ($key == 'stredni') {
        $ok = ($value == '***tajne***');
      }
    }

    $this->assertTrue($ok, 'Zrejme nezvlada foreach');
    $this->assertEquals($cfg['nejaky nazev'], 'nejaka hodnota', 'Neodpovidajici vysledek');
    $this->assertEquals($cfg->pi, 3.15, 'Neodpovidajici hodnota');
  }

  public function testCfgTree ()
  {
    $cfg = new Cfg();
    $db  = new Cfg();
    $db->host = 'localhost';
    $db->user = 'root';
    $db->pass = 'pass';
    $cfg->db = $db;

    $this->assertEquals($cfg->db->user, 'root', 'Neodpovidajici hodnota');
  }

  public function testExpand ()
  {
    $cfg = new Cfg();
    $cfg->root = '/home/jose/www/Apl';
    $cfg->libsDir = '%root%/include/libraries';
    $cfg->apl_prefix = 'apl';
    $cfg->tg_prefix = 'tg';
    $cfg->table = '%apl_prefix%_%tg_prefix%_table';
    $cfg->expand('table');

    $this->assertEquals($cfg->expand('libsDir'), '/home/jose/www/Apl/include/libraries', 'Neodpovidajici hodnota - TEST 1');
    $this->assertEquals($cfg->table, 'apl_tg_table', 'Neodpovidajici hodnota - TEST 2');
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('CfgTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>