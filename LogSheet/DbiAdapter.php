<?php

/**
 * Implementace adapteru pro LogSheet
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_LogSheet
 * @author     Josef Hrabec
 * @version    $Id: DbiAdapter, 2010-01-11 09:52 $
 */

Base::import('LogSheet/ISqlAdapter');

/**
 * Implementuje DBI adapter pro LogSheet
 *
 * @package    Giddy
 * @subpackage LogSheet
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.5
 */
class DbiAdapter implements LogSheet_ISqlAdapter
{
  /**
   * @var DBI_Common
   */
  private $db;

  /**
   * Inicializace
   *
   * @param  DBI_Common $db
   * @return void
   */
  public function __construct(DBI_Common $db)
  {
    $this->db = $db;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#tableExists()
   */
  public function tableExists()
  {
    $tables = $this->db->getResults("SHOW TABLES", DBI::RESULT_AI);
    foreach ($tables as $table) {
      if ($table[0] == LogSheet_ISqlAdapter::TABLE) {
        return true;
      }
    }
    return false;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#createTable()
   */
  public function createTable()
  {
    $query = "
CREATE TABLE " . self::TABLE . " (
  id INTEGER UNSIGNED NOT NULL,
  web_id CHAR(200) NOT NULL DEFAULT '',
  severity CHAR(10) NOT NULL DEFAULT '',
  type CHAR(10) NOT NULL DEFAULT '',
  code INTEGER NOT NULL DEFAULT 0,
  message TEXT NOT NULL,
  file CHAR(200) NOT NULL DEFAULT '',
  line CHAR(5) NOT NULL DEFAULT '',
  trace TEXT,
  ip_addr CHAR(255) NOT NULL DEFAULT '',
  domen_addr CHAR(255) NOT NULL DEFAULT '',
  user_agent CHAR(255) NOT NULL DEFAULT '',
  timestamp DATETIME NOT NULL,
  PRIMARY KEY(id)
);
    ";

    $this->query($query);
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#insertArray($array)
   */
  public function insertArray($array)
  {
    foreach ($array as $row) {
      $query  = "INSERT INTO " . self::TABLE . " (id,  severity, type, code, message, file, line, trace, ip_addr, domen_addr, user_agent, timestamp)";
      $query .= " VALUES (";
      $query .= $row['id'];
      $query .= ", '" . $this->db->escapeString($row['severity']) . "'";
      $query .= ", '" . $this->db->escapeString($row['type']) . "'";
      $query .= ", " . (is_int($row['code']) ? $row['code'] : 0) . "";
      $query .= ", '" . $this->db->escapeString($row['message']) . "'";
      $query .= ", '" . $this->db->escapeString($row['file']) . "'";
      $query .= ", '" . $this->db->escapeString($row['line']) . "'";
      $query .= ", '" . $this->db->escapeString($row['trace']) . "'";
      $query .= ", '" . $this->db->escapeString($row['ip']) . "'";
      $query .= ", '" . $this->db->escapeString($row['domen']) . "'";
      $query .= ", '" . $this->db->escapeString($row['user_agent']) . "'";
      $query .= ", '" . $this->db->escapeString($row['timestamp']) . "'";
      $query .= ")";

      $this->query($query);
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#query($query, $resultType)
   */
  public function query($query, $resultType = DBI::RESULT_AA)
  {
    return $this->db->query($query);
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#arrayQuery($query, $resultType)
   */
  public function arrayQuery($query, $resultType = DBI::RESULT_AA)
  {
    return $this->db->getResults($query, $resultType);
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/LogSheet_ISqlAdapter#clear()
   */
  public function clear()
  {
    $this->query("DROP TABLE " . self::TABLE . "");
  }
}