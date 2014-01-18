<?php

/**
 * Implementace adapteru pro LogSheet
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy_LogSheet
 * @author     Josef Hrabec
 * @version    $Id: SqliteAdapter.php, 2010-01-12 09:53 $
 */

Base::import('LogSheet/ISqlAdapter');

/**
 * Implementuje Sqlite adapter pro LogSheet
 *
 * @package    Giddy
 * @subpackage LogSheet
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.5
 */
class SqliteAdapter implements LogSheet_ISqlAdapter
{
  /**
   * Nazev databaze/souboru
   * @var unknown_type
   */
  private $dbFile;

  /**
   * @var SQLiteDatabase
   */
  private $db;

  /**
   * Deletes a file
   *
   * @param  string  $file  cesta k souboru jez ma byt odstranen
   * @return bool    returns TRUE on success or FALSE on failure
   */
  private function removeFile($file)
  {
    $result = @ unlink($file);
    if (@ file_exists($file)) {
      $filesys = eregi_replace("/", "\\", $file);
      $result = @ exec("del $filesys");
      if (@ file_exists($file)) {
        $result = @ chmod ($file, 0775);
        $result = @ unlink($file);
        $result = @ exec("del $filesys");
      }
    }
    return (boolean) $result;
  }

  /**
   * Inicializace
   *
   * @param  string  $dbFile
   * @return void
   */
  public function __construct($dbFile)
  {
    $this->dbFile = $dbFile;
    if ($db = new SQLiteDatabase($this->dbFile, 0666, $error)) {
      $this->db = $db;
    } else {
      throw new SqliteException($error);
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/IAdapter#tableExists()
   */
  public function tableExists()
  {
    $result = $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . self::TABLE . "'");
    $name = $result->fetchSingle();
    if ($name == self::TABLE) {
      return true;
    }
    return false;
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/IAdapter#createTable()
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
   * @see LogSheet/IAdapter#insertArray($array)
   */
  public function insertArray($array)
  {
    foreach ($array as $row) {
      $query  = "INSERT INTO " . self::TABLE . " (id,  severity, type, code, message, file, line, trace, ip_addr, domen_addr, user_agent, timestamp)";
      $query .= " VALUES (";
      $query .= $row['id'];
      $query .= ", '" . sqlite_escape_string($row['severity']) . "'";
      $query .= ", '" . sqlite_escape_string($row['type']) . "'";
      $query .= ", '" . sqlite_escape_string($row['code']) . "'";
      $query .= ", '" . sqlite_escape_string($row['message']) . "'";
      $query .= ", '" . sqlite_escape_string($row['file']) . "'";
      $query .= ", '" . sqlite_escape_string($row['line']) . "'";
      $query .= ", '" . sqlite_escape_string($row['trace']) . "'";
      $query .= ", '" . sqlite_escape_string($row['ip']) . "'";
      $query .= ", '" . sqlite_escape_string($row['domen']) . "'";
      $query .= ", '" . sqlite_escape_string($row['user_agent']) . "'";
      $query .= ", '" . sqlite_escape_string($row['timestamp']) . "'";
      $query .= ")";

      $this->query($query);
    }
  }

  /**
   * (non-PHPdoc)
   * @return SQLiteResult
   * @see LogSheet/IAdapter#query($query, $resultType)
   */
  public function query($query, $resultType = SQLITE_BOTH)
  {
    if ($result = $this->db->query($query, $resultType, $error)) {
      return $result;
    } else {
      throw new SqliteException($error);
    }
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/IAdapter#arrayQuery($query, $resultType)
   */
  public function arrayQuery($query, $resultType = SQLITE_ASSOC)
  {
    return $this->db->arrayQuery($query, $resultType);
  }

  /**
   * (non-PHPdoc)
   * @see LogSheet/IAdapter#clear()
   */
  public function clear()
  {
    $this->query("DROP TABLE " . self::TABLE . "");
    $this->removeFile($this->dbFile);
  }
}
