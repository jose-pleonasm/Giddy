<?php
/**
 * Mechanizmus pro automatizovani hromadnych testu
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: TestHarness.php, 2009-09-30 18:30 $
 */

require_once('PHPUnit/Framework/TestSuite.php');

/**
 * Automatizuje spousteni hromadnych testu
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class TestHarness extends PHPUnit_Framework_TestSuite
{
  /**
   * Seznam jiz znamych trid
   *
   * @var array
   */
  private $seen = array();

  /**
   * Zaznamena jiz deklarovane tridy
   */
  public function __construct ()
  {
    parent::__construct();

    foreach (get_declared_classes() as $class) {
      $this->seen[$class] = 1;
    }
  }

  /**
   * Registrace trid
   *
   * Vlozi specifikovany soubor a vyhleda
   * nove testovaci tridy (podle konce nazvu).
   * Ty pak prida do test. kolekce
   *
   * @param  string  $file  nazev souboru
   */
  public function register ($file)
  {
    require_once($file);
    foreach (get_declared_classes() as $class) {
      if (array_key_exists($class, $this->seen)) {
        continue;
      }
      $this->seen[$class] = 1;
      if (strtolower(substr($class, -8, 8)) == 'testcase') {
        //echo "adding $class\n";
        $this->addTestSuite($class);
      }
    }
  }
}
