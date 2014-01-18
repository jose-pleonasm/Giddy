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

class ObjectForALTC
{
  public $atr1;
  public $atr2;
  public function __construct ($a_atr)
  {
    $this->atr1 = $a_atr[1];
    $this->atr2 = $a_atr[2];
  }
}


Base::import('Collections/ArrayList');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class ArrayListTestCase extends PHPUnit_Framework_TestCase
{
  static private $data1 = array(1=>"1", "bbb");
  static private $data2 = array(1=>"2", "+++");
  static private $data3 = array(1=>"3", "zkouska");
  static private $data4 = array(1=>"4", "buG&^TfVBdrcg IhVds  eh");

  //1
  public function testConstruct ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);

    $l = new ArrayList(array($example1, $example2, $example3));
    $this->assertEquals($l->get(2), $example3, "Chyba konstruktoru: objekt neodpovida");
  }

  //2
  public function testAddAll ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);

    $l = new ArrayList();
    $l->addAll(array($example1, $example2, $example3));
    $this->assertEquals($l->get(1), $example2, "Objekt neodpovida");
  }

  //3
  public function testAddAll2 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data1);
    $example5 = new ObjectForALTC(self::$data2);
    $example6 = new ObjectForALTC(self::$data3);

    $l = new ArrayList();
    $l->addAll(array($example1, $example2, $example3));
    $l->addAll(3, array($example4, $example5, $example6));
    $this->assertEquals($l->get(1), $example2, "Spatne posunute pole (nekolizni index) - test 1");
    $this->assertEquals($l->get(3), $example4, "Spatne posunute pole (nekolizni index) - test 2");
  }

  //4
  public function testAddAll3 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data1);
    $example5 = new ObjectForALTC(self::$data2);
    $example6 = new ObjectForALTC(self::$data3);

    $l = new ArrayList();
    $l->addAll(array($example1, $example2, $example3));
    $l->addAll(2, array($example4, $example5, $example6));
    $this->assertEquals($l->get(0), $example1, "Spatne posunute pole (kolizni index) - test 1");
    $this->assertEquals($l->get(2), $example4, "Spatne posunute pole (kolizni index) - test 1");
    $this->assertEquals($l->get(4), $example6, "Spatne posunute pole (kolizni index) - test 1");
    $this->assertEquals($l->get(5), $example3, "Spatne posunute pole (kolizni index) - test 1");
  }

  //5
  public function testSimplyAdd ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);

    $l = new ArrayList();
    $l->add($example1);
    $l->add($example2);
    $l->add($example1);
    $this->assertEquals($l->get(2), $example1, "Pridavani prvku selhalo");
  }

  //6
  public function testData ()
  {
    $example1 = new ObjectForALTC(self::$data1);

    $l = new ArrayList();
    $l->add($example1);
    $this->assertEquals($l->get(0)->atr1, $example1->atr1, "Neodpovidajici hodnota objektu");
  }

  //7
  public function testAdd ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $this->assertEquals($l->get(2), $example3, "Neodpovidajici objekt");
  }

  //8
  public function testAdd2 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add($example1);
    $l->add($example2);
    $l->add($example3);
    $l->add(1, $example4);
    $this->assertEquals($l->get(0), $example1, "Spatne posunuti pri vlozeni prvku - test 1");
    $this->assertEquals($l->get(1), $example4, "Spatne posunuti pri vlozeni prvku - test 2");
    $this->assertEquals($l->get(2), $example2, "Spatne posunuti pri vlozeni prvku - test 3");
    $this->assertEquals($l->get(3), $example3, "Spatne posunuti pri vlozeni prvku - test 4");
  }

  //9
  public function testAdd3 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(1, $example4);
    $this->assertEquals($l->get(3), $example3, "Spatne posunuti pri vlozeni prvku pri klicovanem vkladani");
  }

  //10
  public function testRemove ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);

    $l = new ArrayList();
    $l->add($example1);
    $l->add($example2);
    $l->add($example3);
    $old = $l->remove(1);
    $this->assertEquals($l->get(1), $example3, "Spatne posunuti pri smazani prvku");
    $this->assertEquals($old, $example2, "Zadny nebo spatny vraceny element pri odstraneni");
  }

  //11
  public function testRemove2 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(3, $example4);
    $l->remove(1);
    $this->assertEquals($l->get(2), $example4, "Spatne posunuti pri smazani prvku pri klicovanem vkladani");
  }

  //12
  public function testSet ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add($example1);
    $l->add($example2);
    $l->add($example3);
    $l->set(1, $example4);
    $this->assertEquals($l->get(1), $example4, "Spatne nastaveni prvku - test 1");
    $this->assertEquals($l->get(2), $example3, "Spatne nastaveni prvku - test 2");
  }

  //13
  public function testSize ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add($example1);
    $l->add($example2);
    $l->add($example3);
    $l->add(2, $example4);
    $this->assertEquals($l->count(), 4, "Neodpovidajici pocet prvku");
  }

  //14
  public function testContains ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);
  
    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $this->assertTrue($l->contains($example3), "Spatne vyhodnoceni obsahu - test 1");
    $this->assertFalse($l->contains($example4), "Spatne vyhodnoceni obsahu - test 2");
  }

  //15
  public function testIndexOf ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);
  
    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $this->assertEquals($l->indexOf($example3), 2, "Spatny index hledaneho elementu - test 1");
    $this->assertEquals($l->indexOf($example4), -1, "Spatny index hledaneho elementu - test 2");
  }

  //16
  public function testRemoveRange ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);
  
    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(3, $example4);
    $l->removeRange(1, 2);
    $this->assertEquals($l->get(0), $example1, "Spatny vysledek pri odstraneni seznamu od-do - test 1");
    $this->assertEquals($l->get(1), $example3, "Spatny vysledek pri odstraneni seznamu od-do - test 2");
    $this->assertEquals($l->get(2), $example4, "Spatny vysledek pri odstraneni seznamu od-do - test 3");
  }

  //17
  public function testRemoveRange2 ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(3, $example4);
    $l->removeRange(1, 3);
    $this->assertEquals($l->get(0), $example1, "Spatny vysledek pri odstraneni seznamu od-do - test 1");
    $this->assertEquals($l->get(1), $example4, "Spatny vysledek pri odstraneni seznamu od-do - test 3");
    $this->assertEquals($l->count(), 2, "Spatny vysledek pri odstraneni seznamu od-do - neodpovidajici velikost");
  }

  //18
  public function testRemoveRangeEmpty ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(3, $example4);
    $oldSize = $l->count();
    $l->removeRange(1, 1);
    $this->assertEquals($l->count(), $oldSize, "Spatny vysledek pri odstraneni 0 polozek - neodpovidajici velikost");
    $this->assertEquals($l->get(0), $example1, "Spatny vysledek pri odstraneni 0 polozek  - test 1");
    $this->assertEquals($l->get(1), $example2, "Spatny vysledek pri odstraneni 0 polozek - test 2");
    $this->assertEquals($l->get(3), $example4, "Spatny vysledek pri odstraneni 0 polozek - test 3");
  }

  //19
  public function testSubList ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example2);
    $l->add(2, $example3);
    $l->add(3, $example4);
    $sl = $l->subList(1, 3);
    $this->assertEquals($sl->get(0), $example2, "Spatny vysledek pri vraceni casti seznamu od-do - test 1");
    $this->assertEquals($sl->get(1), $example3, "Spatny vysledek pri vraceni casti seznamu od-do - test 2");
  }

  //20
  public function testLastIndexOf ()
  {
    $example1 = new ObjectForALTC(self::$data1);
    $example2 = new ObjectForALTC(self::$data2);
    $example3 = new ObjectForALTC(self::$data3);
    $example4 = new ObjectForALTC(self::$data4);

    $l = new ArrayList();
    $l->add(0, $example1);
    $l->add(1, $example3);
    $l->add(2, $example3);
    $this->assertEquals($l->lastIndexOf($example3), 2, "Spatny posledni index hledaneho elementu - test 1");
    $this->assertEquals($l->lastIndexOf($example4), -1, "Spatny posledni index hledaneho elementu - test 2");
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('ArrayListTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>