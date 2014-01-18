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

Base::import('Document');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class DocumentTestCase extends PHPUnit_Framework_TestCase
{
  // 1
  public function testDocBase()
  {
    $htmlDoc = Document::factory();

    $this->assertTrue($htmlDoc instanceof Document_Html, 'Neodpovidajici objekt');
  }

  // 2
  public function testAuthor()
  {
    $doc = Document::factory();
    $doc->setAuthor('Jose');

    $this->assertEquals($doc->getAuthor(), 'Jose', 'Neodpovidajici autor');
  }

  // 3
  public function testRender()
  {
    $doc = Document::factory();
    $doc->setAuthor('Jose');

    echo $doc;
  }

  // 4
  public function testTable()
  {
    $doc = Document::factory();
    $doc->addTableValue('A1');
    $doc->addTableValue('A2');
    $doc->addTableValue(array('A3', 'A4'));
    $doc->tableRowMove();
    $doc->addTableValue('B1');
    $doc->addTableValue('B2');
    $doc->addTableValue('B3');

    
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('DocumentTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>