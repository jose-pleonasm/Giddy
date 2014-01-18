<?php

/**
 * Testovaci skript
 *
 * @link      https://github.com/jose-pleonasm/Giddy
 * @category  Test
 * @package   Giddy
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '..\\..\\');
  require_once(TEST_ROOT . 'Base.php');
  Base::setRoot(TEST_ROOT);
}

Base::import('HtmlTools');

require_once('PHPUnit/Framework/TestCase.php');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class HtmlToolsTestCase extends PHPUnit_Framework_TestCase
{
  public function testHtmlTools ()
  {
    $a1 = 'str <table str <tr> str';
    $a2 = 'str <p str';
    $a3 = 'str table str';
    $a4 = 'str <undefined str';
    $a5 = 'str <div> str';
    $a6 = 'str <ol> str';
    $a7 = 'str <il> str';
    $a8 = 'str <p< str';

    $this->assertTrue((bool) HtmlTools::includesTag($a1), "Vyhodnoceni retezce '$a1' neodpovida");
    $this->assertEquals(HtmlTools::includesTag($a2), 'p', "Vyhodnoceni retezce '$a2' neodpovida");
    $this->assertFalse(HtmlTools::includesTag($a3), "Vyhodnoceni retezce '$a3' neodpovida");
    $this->assertFalse(HtmlTools::includesTag($a4), "Vyhodnoceni retezce '$a4' neodpovida");
    $this->assertEquals(HtmlTools::includesTag($a5), 'div', "Vyhodnoceni retezce '$a5' neodpovida");
    $this->assertEquals(HtmlTools::includesTag($a6), 'ol', "Vyhodnoceni retezce '$a6' neodpovida");
    $this->assertFalse(HtmlTools::includesTag($a7), "Vyhodnoceni retezce '$a7' neodpovida");
    $this->assertFalse(HtmlTools::includesTag($a8), "Vyhodnoceni retezce '$a8' neodpovida");
  }

  public function testGetIncElements ()
  {
    $s = '<ul class="novinky"><li><h3><a href="novinky/prosincove-kampane-pro-consumers-a-business.html">Prosincové kampaně pro consumers a business</a></h3>V předvánočním období ani T-Mobile nezůstal pozadu a nabídl svým klientům něco navíc. Akční  bonusy a volné minuty se rozhodl prezentovat bannerovou kampaní. Akce spočívala v rozdílných výhodách pro business zákazníky a pro fyzické osoby, proto i kreativy bannerů byly odlišné.<div class="datum"><strong>25. 1. 2010</strong><span>|</span>Novinka</div></li><li><h3><a href="novinky/slovenska-verze-webu-pilsner-urquell-je-na-svete.html">Slovenská verze webu Pilsner Urquell je na světě</a></h3>Nyní i na Slovensku mají vlastní verzi webu Pilsner Urquell. Na zdravie!<div class="datum"><strong>21. 12. 2009</strong><span>|</span> Novinka</div></li><li class="links"><a href="novinky.html">Novinky</a><span>|</span><a href="tiskove-zpravy.html">Tiskové zprávy</a></li></ul>';
    $result = HtmlTools::getIncElements($s);

    $this->assertTrue(in_array('a', $result), "Vyhodnoceni retezce neodpovida");
    $this->assertEquals(count($result), 17, "Pocet nalezenych elementu neodpovida");
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::setRoot(TEST_ROOT);
  Base::import('PHPUnit/WebUI/TestRunner');
  require_once('PHPUnit/Framework/TestSuite.php');

  $test = new PHPUnit_Framework_TestSuite('HtmlToolsTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>