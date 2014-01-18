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

Base::import('System');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class SystemTestCase extends PHPUnit_Framework_TestCase
{
  // 1
  public function testIsIncludable()
  {
    $result1 = System::isIncludable(Base::getRoot() . 'Base.php');

    $this->assertTrue($result1, Base::getRoot() . 'Base.php by melo byt includovatelne');
  }

  // 2
  public function testIncludablePathsSimple()
  {
    $paths = System::isIncludable(Base::getRoot() . 'Base.php', true);

    $this->assertTrue(is_array($paths), 'Neodpovidajici cesty');
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('SystemTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>