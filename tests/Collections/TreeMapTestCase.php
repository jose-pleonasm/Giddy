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

class ObjectForTMTC
{
  public $atr1;
  public $atr2;
  public function __construct ($a_atr)
  {
    $this->atr1 = $a_atr[1];
    $this->atr2 = $a_atr[2];
  }
}


Base::import('Collections/TreeMap');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class TreeMapTestCase extends PHPUnit_Framework_TestCase
{
  static protected $data1 = array(1=>"1", "bbb");
  static protected $data2 = array(1=>"2", "+++");
  static protected $data3 = array(1=>"3", "zkouska");
  
  //1
  public function testTMBase ()
  {
    $map = new TreeMap();
    $map->put("key1", "value1");
    $this->assertEquals($map->get("key1"), "value1", "Zakladni test selhal");
  }

  //2
  public function testPut ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
  
    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $old = $map->put("key2", $obj3);
    $this->assertEquals($map->get("key1"), $obj1, "Pridavani prvku selhalo");
    $this->assertEquals($map->get("key2"), $obj3, "Prepsani prvku selhalo - test 1");
    $this->assertEquals($old, $obj2, "Prepsani prvku selhalo - test 2");
  }

  //3
  public function testGet ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertEquals($map->get("key3"), $obj3, "Ziskani prvku selhalo - test 1");
    $this->assertEquals($map->get("key4"), NULL, "Ziskani prvku selhalo - test 2");
  }

  //4
  public function testRemove ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $old = $map->remove("key1");
    $this->assertEquals($map->get("key1"), NULL, "Smazani prvku selhalo - test 1");
    $this->assertEquals($old, $obj1, "Smazani prvku selhalo - test 2");
  }

  //5
  public function testIsEmpty ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);

    $map1 = new TreeMap();
    $map2 = new TreeMap();
    $map2->put("key1", $obj1);
    $this->assertTrue($map1->isEmpty(), "Metoda isEmpty nepracuje spravne - test 1");
    $this->assertFalse($map2->isEmpty(), "Metoda isEmpty nepracuje spravne - test 2");
  }

  //6
  public function testClear ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $map->clear();
    $this->assertEquals($map->get("key1"), NULL, "Smazani vsech prvku selhalo - test 1");
    $this->assertEquals($map->get("key3"), NULL, "Smazani vsech prvku selhalo - test 2");
    $this->assertTrue($map->isEmpty(), "Metoda Clear nebo isEmpty nepracuje spravne");
  }

  //7
  public function testContainsKey ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertTrue($map->containsKey("key2"), "Hledani klice selhalo - test 1");
    $this->assertFalse($map->containsKey("key4"), "Hledani klice selhalo - test 2");
  }

  //8
  public function testContainsValue ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = '';

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $map->put("key3", $obj3);
    $this->assertTrue($map->containsValue($obj2), "Hledani hodnoty selhalo - test 1");
    $this->assertFalse($map->containsValue($obj4), "Hledani hodnoty selhalo - test 2");
  }

  //9
  public function testEquals ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);

    $map1 = new TreeMap();
    $map1->put("key1", $obj1);
    $map2 = new TreeMap();
    $map2->put("key1", $obj1);
    $map3 = new TreeMap();
    $map3->put("key2", $obj2);
    $this->assertTrue($map1->equals($map2), "Porovnani objektu nepracuje spravne - test 1");
    $this->assertFalse($map1->equals($map3), "Porovnani objektu nepracuje spravne - test 2");
  }

  //10
  public function testCount ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $map1 = new TreeMap();
    $map1->put("key1", $obj1);
    $map1->put("key2", $obj2);
    $map1->put("key3", $obj3);
    $map2 = new TreeMap();
    $this->assertEquals($map1->count(), 3, "Velikost objekt SimpleMap neodpovida - test 1");
    $this->assertTrue(($map2->count() === 0), "Velikost objekt SimpleMap neodpovida - test 2");
  }

  //11
  public function testKeyOf ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $map = new TreeMap();
    $map->put("key1", $obj1);
    $map->put("key2", $obj2);
    $this->assertEquals($map->keyOf($obj2), "key2", "Ziskany klic neodpovida - test 1");
    $this->assertEquals($map->keyOf($obj3), NULL, "Ziskany klic neodpovida - test 2");
  }

  /* =============================================================================== */

  public function testFirstKey ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = new ObjectForTMTC(self::$data3);

    $ksm = new TreeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);
    $ksm->put('c', $obj4);
    $this->assertEquals($ksm->firstKey(), 'a', "Vracen neodpovidajici prvni klic");
  }

  public function testLastKey ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = new ObjectForTMTC(self::$data3);

    $ksm = new TreeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);
    $ksm->put('c', $obj4);
    $this->assertEquals($ksm->lastKey(), 'z', "Vracen neodpovidajici posledni klic");
  }

  public function testHeadMap ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = new ObjectForTMTC(self::$data3);

    $ksm = new treeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);
    $ksm->put('c', $obj4);
    $new_ksm = $ksm->headMap('c');
    $this->assertEquals($new_ksm->firstKey(), 'a', "Vracen neodpovidajici prvni klic vrchni Mapy");
    $this->assertEquals($new_ksm->lastKey(), 'b', "Vracen neodpovidajici posledni klic vrchni Mapy");
  }

  public function testSubMap ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = new ObjectForTMTC(self::$data3);

    $ksm = new treeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);
    $ksm->put('c', $obj4);
    $new_ksm = $ksm->subMap('b', 'c');
    $this->assertEquals($new_ksm->firstKey(), 'b', "TEST 1 -Vracen neodpovidajici prvni klic sub Mapy");
    $this->assertEquals($new_ksm->lastKey(), 'b', "TEST 1 - Vracen neodpovidajici posledni klic sub Mapy");
    $new_ksm = $ksm->subMap('b', 'z');
    $this->assertEquals($new_ksm->firstKey(), 'b', "TEST 2 -Vracen neodpovidajici prvni klic sub Mapy");
    $this->assertEquals($new_ksm->lastKey(), 'c', "TEST 2 - Vracen neodpovidajici posledni klic sub Mapy");
  }

  public function testTailMap ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);
    $obj4 = new ObjectForTMTC(self::$data3);

    $ksm = new treeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);
    $ksm->put('c', $obj4);
    $new_ksm = $ksm->tailMap('c');
    $this->assertEquals($new_ksm->firstKey(), 'c', "Vracen neodpovidajici prvni klic spodni Mapy");
    $this->assertEquals($new_ksm->lastKey(), 'z', "Vracen neodpovidajici posledni klic spodni Mapy");
  }

  public function testPutAll ()
  {
    $obj1 = new ObjectForTMTC(self::$data1);
    $obj2 = new ObjectForTMTC(self::$data2);
    $obj3 = new ObjectForTMTC(self::$data3);

    $ksm = new treeMap();
    $ksm->put('b', $obj1);
    $ksm->put('z', $obj2);
    $ksm->put('a', $obj3);

    $newKsm = new treeMap();
    $newKsm->put('c', '---');
    $newKsm->putAll($ksm);

    $this->assertEquals($newKsm->firstKey(), 'a', "Vracen neodpovidajici prvni klic nove Mapy");
    $this->assertEquals($newKsm->lastKey(), 'z', "Vracen neodpovidajici posledni klic nove Mapy");
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('TreeMapTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>