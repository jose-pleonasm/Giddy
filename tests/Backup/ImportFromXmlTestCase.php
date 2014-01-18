<?php
/**
 * Testovaci trida
 *
 * @link      https://github.com/jose-pleonasm/Giddy
 * @category  Test
 * @package   Giddy
 */

if (!defined('TEST_ROOT')) {
  define('TEST_ROOT', '../../');
  require_once(TEST_ROOT . 'Base.php');
  Base::setRoot(TEST_ROOT);

  define('DB_TYPE', 'mysql');
  define('DB_HOST', 'localhost');
  define('DB_USER', 'jose');
  define('DB_PASS', 'jose');
  define('DB_ENCODING', 'utf8');
  define('DB_NAME_BACKUP', '_importTest');
  define('BACKUP_FILE_PATH', './');
}

Base::import('DBI');
Base::import('Backup/ImportFromXml');
Base::import('PHPUnit/WebUI/TestRunner');


//fce pro modifikaci dat
function modifyValue ($column, $value)
{
  if ($column == 'kategorie' && $value == 'účty') {
    return 'úprava';
  } else {
    return $value;
  }
}

//trida s metodou pro modifikaci dat
class ModifyValues
{
  private $actualID = false;

  public function go ($column, $value)
  {
    if ($column == 'id') {
      $this->actualID = $value;
    }

    if ($this->actualID == 97) {
      if ($column == 'text' || $column == 'content') {
        return 'new text';
      }
    }
    if ($column == 'kategorie' && $value == 'účty') {
      return 'úprava';
    } else {
      return $value;
    }
  }
}

//trida s metodou pro modifikaci dat - vlozi vychozi hodnotu do noveho sloupce
class ModifyValues_insertDefault
{
  private $actualID = false;
  private $pridany_sloupec = 0;

  public function go ($column, $value)
  {
    if($column == 'id') {
      $this->actualID = $value;
    }

    if($column == 'pridany_sloupec') {
      if(empty($value)) {
        $this->pridany_sloupec++;
        if($this->actualID % 2) {
          return 'lichý řádek';
        }
        else {
          return 'sudý řádek';
        }
      }
    }

    return $value;
  }

  public function __destruct ()
  {
    $this->pridany_sloupec;
  }
}


global $db, $xmlPath;
$db = DBI::factory(DB_TYPE, DB_USER, DB_PASS, DB_HOST, DB_NAME_BACKUP, DB_ENCODING);
$xmlPath = BACKUP_FILE_PATH . 'backup.xml';

/**
 * Testovaci trida
 *
 * @package   Giddy
 */
class ImportFromXmlTestCase extends PHPUnit_Framework_TestCase
{
  const TABLE = 't_wb_zaznamy';
  const TABLE_2 = 't_wb_zaznamy_2';
  const TABLE_3 = 't_wb_zaznamy_3';
  private $db;
  private $backupFile;

  private function import ($ib = false)
  {
    if($ib === false) {
      $ib = new ImportFromXml($this->db);
    }
    return $ib->import($this->backupFile);
  }

  private function truncate ($table = false)
  {
    if($table === false) {
      $table = self::TABLE;
    }
    $this->db->query('TRUNCATE TABLE `'.$table.'`');
  }

  public function __construct ()
  {
    global $db, $xmlPath;
    $this->db = $db;
    //$this->db->connect();
    $this->backupFile = $xmlPath;
  }

  public function testBegin ()
  {
    $educt = $this->import();
    $this->assertTrue($educt, 'Import test selhal!');
  }

  //1
  public function testImportData1 ()
  {
    $item = $this->db->getVar('SELECT  vytvoreno FROM '.self::TABLE.' WHERE id = 6 LIMIT 1');
    $this->assertEquals($item, '2006-04-26 20:34:00', 'Predpokladany obsah polozky neodpovida - import zrejme nebyl uspesny - TEST 1');
  }

  //2
  public function testImportData2 ()
  {
    $item = $this->db->getVar('SELECT kategorie FROM '.self::TABLE.' WHERE id = 20 LIMIT 1');
    $this->assertEquals($item, 'účty', 'Predpokladany obsah polozky neodpovida - import zrejme nebyl uspesny - TEST 2');
  }

  //3
  public function testImportData3 ()
  {
    $item = $this->db->getVar('SELECT predmet FROM '.self::TABLE.' WHERE id = 98 LIMIT 1');
    $this->assertEquals($item, 'router ovislink', 'Predpokladany obsah polozky neodpovida - import zrejme nebyl uspesny - TEST 3');
  }

  public function testEnd ()
  {
    $this->truncate();
  }

  //4
  public function testImportWithModify ()
  {
    $useClass = true;
    if($useClass) {
      $obj = new ModifyValues();
      $regFce = array($obj, 'go');
    }
    else {
      $regFce = 'modifyValue';
    }

    $ib = new ImportFromXml($this->db);
    $ib->setModifyFunction($regFce);
    $this->import($ib);
    $item1 = $this->db->getVar('SELECT kategorie FROM '.self::TABLE.' WHERE kategorie = "úprava" LIMIT 1');
    $item2 = $this->db->getVar('SELECT  vytvoreno FROM '.self::TABLE.' WHERE id = 6 LIMIT 1');
    $item3 = $this->db->getVar('SELECT predmet FROM '.self::TABLE.' WHERE id = 98 LIMIT 1');
    if($useClass) {
      $item4 = $this->db->getVar('SELECT text FROM '.self::TABLE.' WHERE id = 97 LIMIT 1');
    }
    $this->truncate();

    $this->assertEquals($item1, 'úprava', 'Predpokladany obsah polozky neodpovida - modifikace zrejme nebyla uspesna - TEST 1');
    $this->assertEquals($item2, '2006-04-26 20:34:00', 'Predpokladany obsah polozky neodpovida - modifikace zrejme nebyla uspesna - TEST 2');
    $this->assertEquals($item3, 'router ovislink', 'Predpokladany obsah polozky neodpovida - modifikace zrejme nebyla uspesna - TEST 3');
    if($useClass) {
      $this->assertEquals($item4, 'new text', 'Zmena hodnoty dle ID neuspesna (chyba modifikace)');
    }
  }

  //5
  public function testTransformImport ()
  {
    $tables = array(self::TABLE=>self::TABLE_2);
    $attrs[self::TABLE_2] = array(
      'id' => 'id',
      'kategorie' => 'kategorie',
      'predmet' => 'title',
      'text' => 'content',
      'public' => 'public',
      'public_edit' => 'public_edit',
      'format_text' => 'format_text',
      'vytvaret_odkazy' => 'vytvaret_odkazy',
      'id_uzivatele' => 'id_uzivatele',
      'vytvoreno' => 'added',
      'posledni_zmena' => 'posledni_zmena'
    );

    //import
    $ib = new ImportFromXml($this->db);
    $ib->transformImport($this->backupFile, $tables, $attrs);

    //ziskani hodnot k testum
    $item1 = $this->db->getVar('SELECT  added FROM '.self::TABLE_2.' WHERE id = 6 LIMIT 1');
    $item2 = $this->db->getVar('SELECT kategorie FROM '.self::TABLE_2.' WHERE id = 20 LIMIT 1');
    $item3 = $this->db->getVar('SELECT  title FROM '.self::TABLE_2.' WHERE id = 98 LIMIT 1');

    //uvolneni tabulky
    $this->truncate(self::TABLE_2);

    //testy
    $this->assertEquals($item1, '2006-04-26 20:34:00', 'Predpokladany obsah polozky neodpovida - transformovany(!) import zrejme nebyl uspesny - TEST 1');
    $this->assertEquals($item2, 'účty', 'Predpokladany obsah polozky neodpovida - transformovany(!) import zrejme nebyl uspesny - TEST 2');
    $this->assertEquals($item3, 'router ovislink', 'Predpokladany obsah polozky neodpovida - transformovany(!) import zrejme nebyl uspesny - TEST 3');
  }

  //6
  public function testTransformImportWithModify ()
  {
    $useClass = true;
    if($useClass) {
      $obj = new ModifyValues();
      $regFce = array($obj, 'go');
    }
    else {
      $regFce = 'modifyValue';
    }

    //'instrukce' pro transformaci struktury
    $tables = array(self::TABLE=>self::TABLE_2);
    $attrs[self::TABLE_2] = array(
      'id' => 'id',
      'kategorie' => 'kategorie',
      'predmet' => 'title',
      'text' => 'content',
      'public' => 'public',
      'public_edit' => 'public_edit',
      'format_text' => 'format_text',
      'vytvaret_odkazy' => 'vytvaret_odkazy',
      'id_uzivatele' => 'id_uzivatele',
      'vytvoreno' => 'added',
      'posledni_zmena' => 'posledni_zmena'
    );

    //import
    $ib = new ImportFromXml($this->db);
    $ib->setModifyFunction($regFce);
    $ib->transformImport($this->backupFile, $tables, $attrs);

    //ziskani hodnot k testum
    $item1 = $this->db->getVar('SELECT  added FROM '.self::TABLE_2.' WHERE id = 6 LIMIT 1');
    $item2 = $this->db->getVar('SELECT  vytvaret_odkazy FROM '.self::TABLE_2.' WHERE id = 20 LIMIT 1');
    $item3 = $this->db->getVar('SELECT  title FROM '.self::TABLE_2.' WHERE id = 98 LIMIT 1');
    $item4 = $this->db->getVar('SELECT kategorie FROM '.self::TABLE_2.' WHERE kategorie = "úprava" LIMIT 1');
    if($useClass) {
      $item5 = $this->db->getVar('SELECT content FROM '.self::TABLE_2.' WHERE id = 97 LIMIT 1');
    }

    //uvolneni tabulky
    $this->truncate(self::TABLE_2);

    //testy
    $this->assertEquals($item1, '2006-04-26 20:34:00', 'Polozka neodpovida - transformovany(!) import s transformaci zrejme nebyl uspesny - TEST 1');
    $this->assertEquals($item2, '1', 'Polozka neodpovida - transformovany(!) import s transformaci zrejme nebyl uspesny - TEST 2');
    $this->assertEquals($item3, 'router ovislink', 'Polozka neodpovida - transformovany(!) import s transformaci zrejme nebyl uspesny - TEST 3');
    $this->assertEquals($item4, 'úprava', 'Polozka neodpovida - transformovany(!) import s transformaci zrejme nebyl uspesny - TEST 4');
    if($useClass) {
      $this->assertEquals($item5, 'new text', 'Zmena hodnoty dle ID v transformovanem importu neuspesna (chyba modifikace)');
    }
  }

  //7
  public function testTransformImportWithModify_insertDefault ()
  {
    $obj = new ModifyValues_insertDefault();
    $regFce = array($obj, 'go');

    //'instrukce' pro transformaci struktury
    $tables = array(self::TABLE=>self::TABLE_2);
    $attrs[self::TABLE_2] = array(
      'id' => 'id',
      'kategorie' => 'kategorie',
      'predmet' => 'title',
      'text' => 'content',
      'public' => 'public',
      'public_edit' => 'public_edit',
      'format_text' => 'format_text',
      'vytvaret_odkazy' => 'vytvaret_odkazy',
      'id_uzivatele' => 'id_uzivatele',
      'vytvoreno' => 'added',
      'posledni_zmena' => 'posledni_zmena',
      'pridany_sloupec' => 'pridany_sloupec');

    //import
    $ib = new ImportFromXml($this->db);
    $ib->setModifyFunction($regFce);
    $ib->transformImport($this->backupFile, $tables, $attrs);

    $item1 = $this->db->getVar('SELECT  title FROM '.self::TABLE_2.' WHERE id = 98 LIMIT 1');
    $item2 = $this->db->getVar('SELECT  pridany_sloupec FROM '.self::TABLE_2.' WHERE id = 34 LIMIT 1');

    //uvolneni tabulky
    $this->truncate(self::TABLE_2);

    $this->assertEquals($item1, 'router ovislink', 'Polozka neodpovida - transformovany(!) import s transformaci (insert default) zrejme nebyl uspesny - TEST 1');
    $this->assertEquals($item2, 'sudý řádek', 'Polozka neodpovida - transformovany(!) import s transformaci (insert default) zrejme nebyl uspesny - TEST 2');
  }

  //8
  public function testRegulatedImport ()
  {
    
    $alteredTables = array(self::TABLE => self::TABLE_3);
    $alteredColumns[self::TABLE_3] = array('vytvoreno' => 'vlozeno');

    //import
    $ib = new ImportFromXml($this->db);
    $ib->regulatedImport($this->backupFile, $alteredTables, $alteredColumns);

    //ziskani hodnot k testum
    $item1 = $this->db->getVar('SELECT  vlozeno FROM '.self::TABLE_3.' WHERE id = 6 LIMIT 1');
    $item2 = $this->db->getVar('SELECT kategorie FROM '.self::TABLE_3.' WHERE id = 20 LIMIT 1');
    $item3 = $this->db->getVar('SELECT  predmet FROM '.self::TABLE_3.' WHERE id = 98 LIMIT 1');

    //uvolneni tabulky
    $this->truncate(self::TABLE_3);

    //testy
    $this->assertEquals($item1, '2006-04-26 20:34:00', 'Predpokladany obsah polozky neodpovida - regulovany import zrejme nebyl uspesny - TEST 1');
    $this->assertEquals($item2, 'účty', 'Predpokladany obsah polozky neodpovida - regulovany import zrejme nebyl uspesny - TEST 2');
    $this->assertEquals($item3, 'router ovislink', 'Predpokladany obsah polozky neodpovida - regulovany import zrejme nebyl uspesny - TEST 3');
    
  }

  //9
  public function testBackupInfo ()
  {
    $backupTimestamp = ImportFromXml::getBackupInfo($this->backupFile, 'timestamp');
    $this->assertEquals($backupTimestamp, '1197385956', 'Hodnota atributu timestamp zalohy nesouhlasi - TEST 1');
  }
}

if(realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  Base::import('PHPUnit/WebUI/TestRunner');

  $test = new PHPUnit_Framework_TestSuite('ImportFromXmlTestCase');
  PHPUnit_WebUI_TestRunner::run($test);
}


/*
============================================
          POUZITE TABULKY
============================================

# TABLE

CREATE TABLE `t_wb_zaznamy` (
  `id` INTEGER UNSIGNED NOT NULL auto_increment,
  `kategorie` VARCHAR(30) NOT NULL DEFAULT '',
  `predmet` VARCHAR(50) NOT NULL,
  `text` text NOT NULL,
  `public` TINYINT(1) NOT NULL DEFAULT 0,
  `public_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `format_text` TINYINT(1) NOT NULL DEFAULT 1,
  `vytvaret_odkazy` TINYINT(1) NOT NULL DEFAULT 1,
  `id_uzivatele` TINYINT UNSIGNED NOT NULL,
  `vytvoreno` datetime NOT NULL,
  `posledni_zmena` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FOREIGN KEY(`id_uzivatele`)
    REFERENCES `uzivatele`(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
) TYPE=MyISAM COMMENT='Vlozene zaznamy jednotlivych uzivatelu'
  DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;


# TABLE_2

CREATE TABLE `t_wb_zaznamy_2` (
  `id` INTEGER UNSIGNED NOT NULL auto_increment,
  `kategorie` VARCHAR(30) NOT NULL DEFAULT '',
  `title` VARCHAR(50) NOT NULL,
  `content` text NOT NULL,
  `public` TINYINT(1) NOT NULL DEFAULT 0,
  `public_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `format_text` TINYINT(1) NOT NULL DEFAULT 1,
  `vytvaret_odkazy` TINYINT(1) NOT NULL DEFAULT 1,
  `id_uzivatele` TINYINT UNSIGNED NOT NULL,
  `added` datetime NOT NULL,
  `posledni_zmena` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pridany_sloupec` VARCHAR(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  FOREIGN KEY(`id_uzivatele`)
    REFERENCES `uzivatele`(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
) TYPE=MyISAM COMMENT='Vlozene zaznamy jednotlivych uzivatelu'
  DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;

# TABLE_3

CREATE TABLE `t_wb_zaznamy_3` (
  `id` INTEGER UNSIGNED NOT NULL auto_increment,
  `kategorie` VARCHAR(30) NOT NULL DEFAULT '',
  `predmet` VARCHAR(50) NOT NULL,
  `text` text NOT NULL,
  `public` TINYINT(1) NOT NULL DEFAULT 0,
  `public_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `format_text` TINYINT(1) NOT NULL DEFAULT 1,
  `vytvaret_odkazy` TINYINT(1) NOT NULL DEFAULT 1,
  `id_uzivatele` TINYINT UNSIGNED NOT NULL,
  `vlozeno` datetime NOT NULL,
  `posledni_zmena` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FOREIGN KEY(`id_uzivatele`)
    REFERENCES `uzivatele`(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
) TYPE=MyISAM COMMENT='Vlozene zaznamy jednotlivych uzivatelu'
  DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;

*/

?>