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

class ObjectForGVTC
{
  private $p = 'v';
}


Base::import('GeneralValidator');

Base::import('PHPUnit/WebUI/TestRunner');

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class GeneralValidatorTestCase extends PHPUnit_Framework_TestCase
{
  // 1) ValidString
  public function testValidString ()
  {
    $result1 = GeneralValidator::isValid('string:2<>10', 'ok');
    $result2 = GeneralValidator::isValid('string:1<>3',  'tri');

    $this->assertTrue($result1, 'Retezec prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Retezec prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 2');
  }

  // 2) InvalidString
  public function testInvalidString ()
  {
    $result1 = GeneralValidator::isValid('string:2<>10', '1');
    $result2 = GeneralValidator::isValid('string:1<>3',  'tri+');

    $this->assertFalse($result1, 'Retezec prohlasen za odpovidajici, prestoze by nemel vyhovovat - TEST 1');
    $this->assertFalse($result2, 'Retezec prohlasen za odpovidajici, prestoze by nemel vyhovovat - TEST 2');
  }

  // 3) Integer
  public function testInteger ()
  {
    $result1 = GeneralValidator::isValid('integer:-2<>10', -2);
    $result2 = GeneralValidator::isValid('integer:2<>10', 10);
    $result3 = GeneralValidator::isValid('integer:2<>10', 11);

    $this->assertTrue($result1, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 2');
    $this->assertFalse($result3, 'Cislo prohlaseno za odpovidajici, prestoze by nemelo vyhovovat');
  }

  // 4) Float
  public function testFloat ()
  {
    $result1 = GeneralValidator::isValid('float:2<>10', 2.0);
    $result2 = GeneralValidator::isValid('float:2<>10', 10.0);
    $result3 = GeneralValidator::isValid('float:2<>10', 5.5);
    $result4 = GeneralValidator::isValid('float:0<>10', 0.1);
    $result5 = GeneralValidator::isValid('float:2<>10', 10.1);
    $result6 = GeneralValidator::isValid('float:2<>10', 5);
    $result7 = GeneralValidator::isValid('float:2<>10', '5.2');

    $this->assertTrue($result1, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 2');
    $this->assertTrue($result3, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 3');
    $this->assertTrue($result4, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 4');
    $this->assertFalse($result5, 'Cislo prohlaseno za odpovidajici, prestoze by nemelo vyhovovat - TEST 5');
    $this->assertFalse($result6, 'Cislo prohlaseno za odpovidajici, prestoze by nemelo vyhovovat - TEST 6');
    $this->assertFalse($result7, 'Cislo prohlaseno za odpovidajici, prestoze by nemelo vyhovovat - TEST 7');
  }

  // 5) Numeric
  public function testNumeric ()
  {
    $result1 = GeneralValidator::isValid('numeric:2<>10', 2.0);
    $result2 = GeneralValidator::isValid('numeric:2<>10', 10.0);
    $result3 = GeneralValidator::isValid('numeric:2<>10', 5.5);
    $result4 = GeneralValidator::isValid('numeric:2<>10', 10.1);
    $result5 = GeneralValidator::isValid('numeric:0<>1000000', '4000');
    $result6 = GeneralValidator::isValid('numeric:2<>10', '5.5');
    $result7 = GeneralValidator::isValid('numeric:0<>10', '0.15274901');

    $this->assertTrue($result1, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 2');
    $this->assertTrue($result3, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 3');
    $this->assertFalse($result4, 'Cislo prohlaseno za odpovidajici, prestoze by nemelo vyhovovat - TEST 4');
    $this->assertTrue($result5, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 5');
    $this->assertTrue($result6, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 6');
    $this->assertTrue($result7, 'Cislo prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 7');
  }

  // 6) Array
  public function testArray ()
  {
    $result1 = GeneralValidator::isValid('array:1<>3', array(1, 2));

    $this->assertTrue($result1, 'Pole prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
  }

  // 7) Object
  public function testObject ()
  {
    $o = new ObjectForGVTC();
    $result1 = GeneralValidator::isValid('object:ObjectForGVTC', $o);

    $this->assertTrue($result1, 'Object prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
  }

  // 8) Enum
  public function testEnum ()
  {
    $result1 = GeneralValidator::isValid('enum:e1<>v2<>k3', 'k3');
    $result2 = GeneralValidator::isValid('enum:e1<>v2<>k3', 'ne');
    $result3 = GeneralValidator::isValid('enum:ano', 'ano');

    $this->assertTrue($result1, 'Object prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
    $this->assertFalse($result2, 'Object prohlasen za odpovidajici, prestoze by nemel vyhovovat - TEST 2');
    $this->assertTrue($result3, 'Object prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 3');
  }

  // 9) Value
  public function testValue ()
  {
    $result1 = GeneralValidator::isValid('value:ok', 'ok');

    $this->assertTrue($result1, 'Object prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
  }

  // 10) Email
  public function testEmail ()
  {
    $result1 = GeneralValidator::isValid('email', 'petr.pan@domain.net');
    $result2 = GeneralValidator::isValid('email', 'jose_h@dom.ain.net');
    $result3 = GeneralValidator::isValid('email', 'p.e.t.r.@domain.net');
    $result4 = GeneralValidator::isValid('email', 'jose@neprehledni.cz');

    $this->assertTrue($result1, 'E-mail prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
    $this->assertTrue($result2, 'E-mail prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 2');
    $this->assertTrue($result3, 'E-mail prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 3');
    $this->assertTrue($result4, 'E-mail prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 4');
  }

  // 11) Date
  public function testDate ()
  {
    $result1 = GeneralValidator::isValid('date:1970-01-01<>2008-09-23', '1985-03-18');

    $this->assertTrue($result1, 'Datum prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
  }

  // 12) Date CZ FORMAT
  public function testDateCz ()
  {
    $result1 = GeneralValidator::isValid('date-cz', '18.3.1985');
    $result2 = GeneralValidator::isValid('date-cz', '06.12.2011');
    $result3 = GeneralValidator::isValid('date-cz:1.1.1970<>25.6.2009', '18.3.1985');

    $this->assertTrue($result1, 'Datum prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Datum prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 2');
    $this->assertTrue($result3, 'Datum prohlaseno za neodpovidajici, prestoze by melo vyhovovat - TEST 3');
  }

  // 13) Time
  public function testTime ()
  {
    $result1 = GeneralValidator::isValid('time:00-00-00<>23-59-59', '23:59:59');

    $this->assertTrue($result1, 'Cas prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
  }

  // 14) URL
  public function testURL ()
  {
    $result1 = GeneralValidator::isValid('url', 'http://www.yoda.cz');
    $result2 = GeneralValidator::isValid('url', 'http://jose.cz/');
    $result3 = GeneralValidator::isValid('url', 'ftp://198.25.63.102');
    $result4 = GeneralValidator::isValid('url', 'https://web.server.net/~user/path/index.html');
    $result5 = GeneralValidator::isValid('url', 'http://c1.server.com:80/path_to_target/');
    $result6 = GeneralValidator::isValid('url', 'http://www.web.org/path/index.php?page=home&from=www.jose.cz');
    $result7 = GeneralValidator::isValid('url', 'http://web.server.net/some-path/index.html#content');
    $result11 = GeneralValidator::isValid('url', 'http://user:pass@members.server.net/secure/');

    $result8 = GeneralValidator::isValid('url', 'http://web.ser ver.net/index.html#content');
    $result9 = GeneralValidator::isValid('url', 'http://server/~user/path/index.html');
    $result10 = GeneralValidator::isValid('url', 'http://256.25.63.102');

    $this->assertTrue($result1, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 1');
    $this->assertTrue($result2, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 2');
    $this->assertTrue($result3, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 3');
    $this->assertTrue($result4, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 4');
    $this->assertTrue($result5, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 5');
    $this->assertTrue($result6, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 6');
    $this->assertTrue($result7, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 7');
    $this->assertTrue($result11, 'URL prohlasena za neodpovidajici, prestoze by mela vyhovovat - TEST 11');
    $this->assertFalse($result8, 'URL prohlasena za odpovidajici, prestoze by nemela vyhovovat - TEST 8');
    $this->assertFalse($result9, 'URL prohlasena za odpovidajici, prestoze by nemela vyhovovat - TEST 9');
    $this->assertFalse($result10, 'URL prohlasena za odpovidajici, prestoze by nemela vyhovovat - TEST 10');
  }

  // 15) nick
  public function testNick ()
  {
    $result1 = GeneralValidator::isValid('nick', 'jose.pleonasm');
    $result2 = GeneralValidator::isValid('nick', 'min');
    $result3 = GeneralValidator::isValid('nick', 'jožin');
    $result4 = GeneralValidator::isValid('nick', 'jo se');

    $this->assertTrue($result1, 'Nick "jose.pleonasm" prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 1');
    $this->assertTrue($result2, 'Nick "min" prohlasen za neodpovidajici, prestoze by mel vyhovovat - TEST 2');
    $this->assertFalse($result3, 'Nick "jožin" prohlasen za odpovidajici, prestoze by NEmel vyhovovat - TEST 3');
    $this->assertFalse($result4, 'Nick "jo se" prohlasen za odpovidajici, prestoze by NEmel vyhovovat - TEST 4');
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('GeneralValidatorTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}

?>