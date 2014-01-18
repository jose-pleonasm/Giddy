<?php
/**
 * Testovaci skript
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Test
 * @package   Giddy
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '..\\..\\');
}


require_once(TEST_ROOT . 'Base.php');
require_once(TEST_ROOT . 'System.php');
Base::setRoot(TEST_ROOT);
Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class BaseTestCase extends PHPUnit_Framework_TestCase
{
  static protected $frameworkPath = TEST_ROOT;
  static protected $frameworkAbsolutePath = 'E:\\www\\Giddy\\';

  public function testBase ()
  {
    Base::init();

    $this->assertEquals(self::$frameworkAbsolutePath, Base::getRoot(), 'Vracena neodpovidajici hodnota FrameworkRoot');
  }

  public function testSetRoot ()
  {
    Base::setRoot(self::$frameworkPath);
    $returnPath = Base::setRoot('/path/to/framework/');

    $this->assertEquals(self::$frameworkPath, $returnPath, 'Vracena neodpovidajici hodnota FrameworkRoot - test1');
    $this->assertEquals('\\path\\to\\framework\\', Base::getRoot(), 'Vracena neodpovidajici hodnota FrameworkRoot - test2');
  }

  public function testSetRoot2 ()
  {
    Base::setRoot('/path/to/framework');
    $correctRoot = DIRECTORY_SEPARATOR . 'path'
                 . DIRECTORY_SEPARATOR .'to'
                 . DIRECTORY_SEPARATOR .'framework'
                 . DIRECTORY_SEPARATOR;

    $this->assertEquals($correctRoot, Base::getRoot(), 'Vracena neodpovidajici hodnota FrameworkRoot');
  }

  public function testImport ()
  {
    $class = 'GiddyFramework';

    Base::setRoot(self::$frameworkPath);
    Base::import($class);

    $this->assertTrue(in_array($class, get_declared_classes()), "Trida $class neexistuje");
  }

  public function testHighImport ()
  {
    Base::setRoot(self::$frameworkPath);
    Base::import('Debug as Debugger', true);
    Base::import(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Foo', true);
    Base::import('Debug
as  NewClass', true);
    Base::import('e:\www\my-framework\* [^Base,^DBI,^Printer,^TestHarness,^System,^Cfg,^DBI-1.7,^GeneralValidator]', true);

    $this->assertTrue(in_array('Debugger', get_declared_classes()), "Trida Debugger neexistuje");
    $this->assertTrue(in_array('Foo', get_declared_classes()), "Trida Foo neexistuje");
    $this->assertTrue(in_array('NewClass', get_declared_classes()), "Trida NewClass neexistuje");
    $this->assertTrue(in_array('ExchangeList', get_declared_classes()), "Trida ExchangeList neexistuje");
  }

  public function testImportException ()
  {
    $class = 'NotExistsClass';
    $result = false;

    Base::setRoot(self::$frameworkPath);
    try {
      Base::import($class);
    }
    catch(Exception $e) {
      $result = $e;
    }

    $this->assertTrue($result instanceof ImportException, "Chybne vyhodnoceni importu neexistujici tridy - TEST 1");
    $this->assertEquals($class, $result->getClassName(), "Chybne vyhodnoceni importu neexistujici tridy - TEST 2");
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::setRoot(TEST_ROOT);
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('BaseTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>