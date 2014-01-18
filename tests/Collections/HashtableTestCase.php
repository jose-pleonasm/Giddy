<?php
/**
 * Testovaci skript
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Test
 * @package   Giddy
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '../../');
  require_once(TEST_ROOT . 'Base.php');
  Base::setRoot(TEST_ROOT);
}

class ObjectForHTC
{
  public $atr1;
  public $atr2;
  public function __construct ($a_atr)
  {
    $this->atr1 = $a_atr[1];
    $this->atr2 = $a_atr[2];
  }
}


Base::import('Collections/Hashtable');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class HashtableTestCase extends PHPUnit_Framework_TestCase
{
  static protected $data1 = array(1=>"1", "bbb");
  static protected $data2 = array(1=>"2", "+++");
  static protected $data3 = array(1=>"3", "zkouska");
  
  //1
  public function testBase ()
  {
    $map = new Hashtable();
    $map->put("key1", "value1");
    $this->assertEquals($map->get("key1"), "value1", "Zakladni test selhal");
  }

  //2
  public function testPut ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);
  
    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $old = $map->put("key2", $obj3);
    $this->assertEquals($map->get("key1"), $obj1, "Pridavani prvku selhalo");
    $this->assertEquals($map->get("key2"), $obj3, "Prepsani prvku selhalo - test 1");
    $this->assertEquals($old, $obj2, "Prepsani prvku selhalo - test 2");
  }

  //3
  public function testPutAll ()
  {
    $h = new Hashtable();
    $h->put('key 1', 'item #1');
    $h->put('key 2', 'item #2');
    $h->put('key 3', '6LeUtQUAAAAAAG6oZeEHGwLqN3y5tjTlqqB7agfu');

    $map = new Hashtable();
    $map->putAll($h);
    $this->assertEquals($map->get("key 2"), 'item #2', "Vlozeni vsech prvku selhalo");
  }

  //4
  public function testGet ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertEquals($map->get("key3"), $obj3, "Ziskani prvku selhalo - test 1");
    $this->assertEquals($map->get("key4"), NULL, "Ziskani prvku selhalo - test 2");
  }

  //5
  public function testRemove ()
  {
    $obj1 = new ObjectForHTC(self::$data1);

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $old = $map->remove("key1");
    $this->assertEquals($map->get("key1"), NULL, "Smazani prvku selhalo - test 1");
    $this->assertEquals($old, $obj1, "Smazani prvku selhalo - test 2");
  }

  //6
  public function testIsEmpty ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);

    $map1 = new Hashtable();
    $map2 = new Hashtable();
    $map2->put("key1", $obj1);
    $this->assertTrue($map1->isEmpty(), "Metoda isEmpty nepracuje spravne - test 1");
    $this->assertFalse($map2->isEmpty(), "Metoda isEmpty nepracuje spravne - test 2");
  }

  //7
  public function testClear ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $map->clear();
    $this->assertEquals($map->get("key1"), NULL, "Smazani vsech prvku selhalo - test 1");
    $this->assertEquals($map->get("key3"), NULL, "Smazani vsech prvku selhalo - test 2");
    $this->assertTrue($map->isEmpty(), "Metoda Clear nebo isEmpty nepracuje spravne");
  }

  //8
  public function testContainsKey ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertTrue($map->containsKey("key2"), "Hledani klice selhalo - test 1");
    $this->assertFalse($map->containsKey("key4"), "Hledani klice selhalo - test 2");
  }

  //9
  public function testContainsValue ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);
    $obj4 = '';

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertTrue($map->containsValue($obj2), "Hledani hodnoty selhalo - test 1");
    $this->assertFalse($map->containsValue($obj4), "Hledani hodnoty selhalo - test 2");
  }

  //10
  public function testEquals ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);

    $map1 = new Hashtable();
    $map1->put("key1", $obj1);
    $map2 = new Hashtable();
    $map2->put("key1", $obj1);
    $map3 = new Hashtable();
    $map3->put("key2", $obj2);
    $this->assertTrue($map1->equals($map2), "Porovnani objektu nepracuje spravne - test 1");
    $this->assertFalse($map1->equals($map3), "Porovnani objektu nepracuje spravne - test 2");
  }

  //11
  public function testCount ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $map1 = new Hashtable();
    $map1->put("key1", $obj1);
    $map1->put("key2", $obj2);
    $map1->put("key3", $obj3);
    $map2 = new Hashtable();
    $this->assertEquals($map1->count(), 3, "Velikost objekt SimpleMap neodpovida - test 1");
    $this->assertTrue(($map2->count() === 0), "Velikost objekt SimpleMap neodpovida - test 2");
  }

  //12
  public function testKeyOf ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $map = new Hashtable();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $this->assertEquals($map->keyOf($obj2), "key2", "Ziskany klic neodpovida - test 1");
    $this->assertEquals($map->keyOf($obj3), NULL, "Ziskany klic neodpovida - test 2");
  }

  //13
  public function testImport ()
  {
    $obj1 = new ObjectForHTC(self::$data1);
    $obj2 = new ObjectForHTC(self::$data2);
    $obj3 = new ObjectForHTC(self::$data3);

    $array = array("k1"=>$obj1, "k2"=>$obj2, "k3"=>$obj3);
    $map = new Hashtable();
    $map->import($array);
    $this->assertEquals($map->get("k2"), $obj2, "Import pole selhal");
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('HashtableTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>