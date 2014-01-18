<?php

/**
 * Zalohavaci mechanizmus
 *
 * Modul pro export do XML
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_Backup
 * @version    $Id: ExportIntoXml.php, 2010-08-19 14:44 $
 */

/**
 * Modul pro export
 *
 * Exportuje data z DB do XML dokumentu
 * (Jednoducha trida pro vytvoreni XML dokumentu)
 *
 * @package    Giddy_Backup
 * @author     Josef Hrabec
 * @version    Release: 0.8.1
 * @since      Trida je pristupna od verze 0.1
 */
class ExportIntoXml
{
  /**
   * Verze generovaneho XML dokumentu
   */
  const XML_DOC_VER = '1.0';

  /**
   * Vlastnosti XML dokumentu
   *  + charset: kodovani dat
   *
   * @var array
   */
  protected $properties;

  /**
   * Inicializace
   *
   * @param  array  $properties  parametry dokumentu
   */
  public function __construct ($properties = array())
  {
    $this->properties['charset'] = isset($properties['charset']) ? $properties['charset'] : 'UTF-8';
  }

  /**
   * Ziska data, pripravi je do pole a necha vygenerovat XML dokument
   *
   * @param  DBI_Common  $db      implementecni objekt rozhrani DBI_General
   * @param  array       $tables  pole s nazvy tabulek
   * @param  string      $source  nazev zdroje/puvod dat
   * @param  string      $desc    blizsi popis
   * @return string  vygenerovany XML dokumentu
   */
  public function export (DBI_Common $db, $tables, $source = NULL, $desc = '')
  {
    if (is_array($tables)) {
      foreach ($tables as $table) {
        $data[$table] = self::getData($db, $table);
      }
    } elseif (is_string($tables)) {
      $data[$tables] = self::getData($db, $tables);
    } else {
      throw new InvalidArgumentException('Table(s) not specified');
    }

    if ($source === NULL) {
      $source = $_SERVER['SERVER_NAME'] . '/' . $db->getDbName();
    }
    return self::generateXml($data, $source, $desc);
  }

  /**
   * Vygeneruje XML dokument
   *
   * @param  array   $data    pole reprezentujici tabulku
   *                          (struktura odpovidajici tabulce)
   * @param  string  $source  nazev zdroje/puvod dat
   * @param  string  $desc    blizsi popis
   * @return string  zdroj. kod XML dokumentu
   */
  public function generateXml ($data, $source, $desc = "")
  {
    if (!empty($desc)) {
      $descParam = " desc=\"" . $desc . "\"";
    } else {
      $descParam = "";
    }
    $xml  = "<?xml version=\"1.0\" encoding=\"" . $this->properties['charset'] . "\"?>";
    $xml .= "<backup version=\"" . self::XML_DOC_VER . "\" source=\"" . $source . "\" timestamp=\"" . time() . "\"" . $descParam . ">";
    //jednotlive polozky - hodnoty sloupcu
    foreach ($data as $secName=>$values) {
      if (empty($values)) {
        continue;
      }
      foreach ($values as $rowName=>$rowValues) {
        $xml .= "<" . $secName . ">";
        foreach ($rowValues as $name=>$value) {
          $xml .= "<" . $name . ">" . self::handleDataForXml($value) . "</" . $name . ">";
        }
        $xml .= "</" . $secName . ">";
      }
    }
    $xml .= "</backup>";

    return $xml;
  }

  /**
   * Osetri data pro pouziti v XML
   *
   * @uses   preg_replace()
   * @param  string  $text  vstupni text
   * @return string  osetreny text
   */
  public static function handleDataForXml ($data)
  {
    if ($data === NULL) {
      return 'NULL';
    }
    $data = preg_replace('/<!\[CDATA\[/', '&lt;![CDATA[', $data);
    $data = preg_replace('/\]\]>/', ']]&gt;', $data);

    return '<![CDATA[' . $data . ']]>';
  }

  /**
   * Nacte data s DB pres rozhrani DBI
   *
   * @param  DBI_Common  $db
   * @param  string      $table
   * @return array
   */
  public static function getData (DBI_Common $db, $table)
  {
     //sestaveni SQL dotazu
    $dotaz = "SELECT * FROM `$table`";
    $data = $db->getResults($dotaz, DBI::RESULT_AA);
    return $data;
  }
}
