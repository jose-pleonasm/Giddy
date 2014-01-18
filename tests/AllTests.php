<?php
/**
 * Spusteni vsech testovacich trid
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Test
 * @package   Giddy
 * @version   $Id: AllTests.php, 2009-11-04 14:58 $
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '..\\');
}

require_once(TEST_ROOT . 'Base.php');
Base::setRoot(TEST_ROOT);


/**
 * Konstanty pro DBI
 */
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'jose');
define('DB_PASS', 'jose');
define('DB_ENCODING', 'utf8');
define('DB_NAME_DBI', '_test');
define('DB_NAME_BACKUP', '_importTest');

/**
 * Cesta k testovacimu souboru pro Backup
 */
define('BACKUP_FILE_PATH', './Backup/');

/**
 * Sobor s logy
 */
define('LOG_FILE', './LogSheet/log-2.2.xml');

/* Nastaveni */
$arguments = array();
//$arguments['testdoxHTMLFile'] = './reports/dox.html';  // seznam spustenych metod
//$arguments['graphvizLogfile'] = './reports/dox.graphviz'; // vytvori predpis pro Graphviz
$arguments['storyHTMLFile'] = './reports/last-test.html'; // html report
//$arguments['jsonLogfile'] = './reports/json.txt'; // json export
//$arguments['tapLogfile'] = './reports/tap.txt'; // TAP report
$arguments['xmlLogfile'] = './reports/report.xml'; // XML report
// code coverage report
//todo: treba nacist XDebug
//$arguments['reportDirectory'] = 'reports/code-coverage';
//$arguments['reportCharset'] = 'UTF-8';
//$arguments['reportYUI'] = true;
//$arguments['reportHighlight'] = true;
//$arguments['reportLowUpperBound'] = 35;
//$arguments['reportHighLowerBound'] = 70;




Base::import('PHPUnit/WebUI/TestRunner');
Base::import('TestHarness');

$suite = new TestHarness();
$suite->register(TEST_ROOT . 'tests/Base/BaseTestCase.php');
$suite->register(TEST_ROOT . 'tests/tools/toolsTestCase.php');
$suite->register(TEST_ROOT . 'tests/System/SystemTestCase.php');
$suite->register(TEST_ROOT . 'tests/Collections/ArrayListTestCase.php');
$suite->register(TEST_ROOT . 'tests/Collections/HashtableTestCase.php');
$suite->register(TEST_ROOT . 'tests/Collections/TreeMapTestCase.php');
$suite->register(TEST_ROOT . 'tests/GeneralValidator/GeneralValidatorTestCase.php');
$suite->register(TEST_ROOT . 'tests/Cfg/CfgTestCase.php');
$suite->register(TEST_ROOT . 'tests/DBI/DBITestCase.php');
$suite->register(TEST_ROOT . 'tests/Backup/ImportFromXmlTestCase.php');

PHPUnit_WebUI_TestRunner::run($suite, $arguments);

?>