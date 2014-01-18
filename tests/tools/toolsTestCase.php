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


Base::import('tools');

Base::import('PHPUnit/WebUI/TestRunner');

class gToolsTestCase extends PHPUnit_Framework_TestCase
{
  public function testCutText()
  {
    $text = 'Lorem ipsum dolor sit amet consectetuer Vestibulum ipsum interdum feugiat sollicitudin. Eget lacinia et justo consequat elit vitae vel tortor non Proin.';
    $html = '<div id="main"><img src="./pic1.png" alt=" " /><p>Nějaký popis obrázku nad textem, který se má zobrazit.</p></div>';
    $this->assertEquals('ein', gTools::cutText('einsturzende', 3), 'Spatne orezani slova');
    $this->assertEquals('Nějaká celá, ale krátká věta.', gTools::cutText('Nějaká celá, ale krátká věta.', 35), 'Text byl orezan i kdyz byt nemel');
    $this->assertEquals('Lorem ipsum dolor sit amet consectetuer Vestibulum ipsum interdum feugiat sollicitudin.', gTools::cutText($text, 90), 'Spatne orezani vety - ma se orezat u posledni mezery pred specifikovanym limitem');
    $this->assertEquals('Lorem ipsum dolor sit amet consectetuer Vestibulum ipsum interdum feugiat sollicitudin. <a href="#">more</a>', gTools::cutText($text, 90, ' <a href="#">more</a>'), 'Patrne nepridan retezec reprezentujici pokracovani textu');
    $this->assertEquals('Nějaký popis obrázku nad textem, který ...', gTools::cutText($html, 45, ' ...'), 'Spatne zpracovane HTML');
  }

  public function testPhpToSqlDateFormat()
  {
    $this->assertEquals('%H:%m:%i', gTools::phpToSqlDateFormat('G:m:i'), 'Spatnej prevod formatu pro casove funkce PHP => MySQL');
  }

  public function testIntToPrice()
  {
    $this->assertEquals('1 526 307,00', gTools::intToPrice(1526307), 'Spatny prevod cisla na format ceny');
    $this->assertEquals('5,600', gTools::intToPrice(5600, ',', '.', 0), 'Spatny prevod cisla na format ceny');
  }

  public function testSerialize()
  {
    $var = array('string'=>'some text','pi'=>3.15);
    $this->assertEquals($var, gTools::unserialize(gTools::serialize($var)), 'Spatna serializace/unserializace');
  }

  public function testTruepath()
  {
    $this->assertEquals('/www/web/',
                        gTools::truepath('/www/web/', '/'),
                        'Spatny prevedeni adresy - test 1');
    // ----------------------------------------------------------
    $this->assertEquals('.' . DIRECTORY_SEPARATOR,
                        gTools::truepath('.' . DIRECTORY_SEPARATOR),
                        'Spatny prevedeni adresy - test 2');
    // ----------------------------------------------------------
    $this->assertEquals('http://localhost/prace/servisni-db/aplikace/data/logo/logoYodaM.jpg',
                        gTools::truepath('http://localhost/prace/servisni-db/aplikace/./data/logo/logoYodaM.jpg', '/'),
                        'Spatny prevedeni adresy - test 3');
    // ----------------------------------------------------------
    $this->assertEquals('http://localhost/prace/servisni-db/data/logo/logoYodaM.jpg',
                        gTools::truepath('http://localhost/prace/servisni-db/aplikace/../data/logo/logoYodaM.jpg', '/'),
                        'Spatny prevedeni adresy - test 4');
    // ----------------------------------------------------------
    $this->assertEquals('http://localhost/prace/data/logo/logoYodaM.jpg',
                        gTools::truepath('http://localhost/prace/servisni-db/aplikace/../../data/logo/logoYodaM.jpg', '/'),
                        'Spatny prevedeni adresy - test 5');
    // ----------------------------------------------------------
    $this->assertEquals('http://prace/servisni-db/data/logo/logoYodaM.jpg',
                        gTools::truepath('http://localhost/../prace/servisni-db/aplikace/../data/logo/logoYodaM.jpg', '/'),
                        'Spatny prevedeni adresy - test 6');
    // ----------------------------------------------------------
    $this->assertEquals('/var/www/prace/index.php',
                        gTools::truepath('/var/www/data/../prace/./index.php', '/'),
                        'Spatny prevedeni adresy - test 7');
    // ----------------------------------------------------------
    $this->assertEquals('e:\home\jose\www\prace\index.php',
                        gTools::truepath('e:/home/jose/www/data/../prace/./index.php', '\\'),
                        'Spatny prevedeni adresy - test 8');
    // ----------------------------------------------------------
    $this->assertEquals('http://prace/servisni-db/data/index.php?page=2&amp;id=3',
                        gTools::truepath('http://localhost/../prace/servisni-db/aplikace/../data/index.php?page=2&amp;id=3', '/'),
                        'Spatny prevedeni adresy - test 9');
    // ----------------------------------------------------------
    $this->assertEquals('/',
                        gTools::truepath('/home/../bin/../', '/'),
                        'Spatny prevedeni adresy - test 10');
  }
}


if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('gToolsTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}
