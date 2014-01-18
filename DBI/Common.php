<?php

/**
 * DBI_Common
 *
 * Obecny navrh
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    $Id: Common.php, 2011-04-13 16:22 $
 */

Base::import('Common');

/**
 * Abstrakce databasoveho rozhrani
 *
 * Definice rozhrani trid pro DBMS
 *
 * Inspirovano ez_sql
 *
 * @package    Giddy
 * @subpackage DBI
 * @author     Josef Hrabec
 * @version    Release: 0.9.2
 * @since      Trida je pristupna od verze 0.1
 */
abstract class DBI_Common extends Common
{
  /**
   * Pocet dotazu prohnanych metodou debug
   *
   * @var integer
   */
  private $numDebugQuery = 0;

  /**
   * Zda jiz je navazano spojeni
   *
   * @var bool
   */
  protected $isConnect = false;

  /**
   * Pocet radku posledniho vysledku
   *
   * @var integer
   */
  protected $numRows;

  /**
   * Pocet ovlivnenych radku poslednim dotazem
   *
   * @var integer
   */
  protected $affectedRows;

  /**
   * Aktualni hodnota AUTO_INCREMENT posledniho SQL dotazu
   *
   * @var integer
   */
  protected $insertId;

  /**
   * Posledni (aktualni) SQL dotaz
   *
   * @var string
   */
  protected $lastQuery;

  /**
   * Posledni (aktualni) vysledek
   *
   * @var array
   */
  protected $lastResult;

  /**
   * Priznak pro automaticke volani metody DBI_Common::debug()
   *
   * @var boolean
   */
  public $debugAll = false;

  /**
   * Nastavy vychozi parametry (vyprazdni cache)
   *
   * @return void
   */
  protected function flush ()
  {
    $this->numRows      = NULL;
    $this->affectedRows = NULL;
    $this->insertId     = NULL;
    $this->lastQuery    = NULL;
    $this->lastResult   = NULL;
  }

  /**
   * Zda je pripojen
   *
   * @return bool  true pokud spojeni existuje true, jinak false
   */
  public function isConnect ()
  {
    return $this->isConnect;
  }

  /**
   * Nastavi priznak pro ladeni
   *
   * @param  bool  $flag  nova hodnota
   * @return bool  puvodni hodnota
   */
  public function debugAll ($flag = true)
  {
    $buffer = $this->debugAll;
    $this->debugAll = $flag;
    return $buffer;
  }

  /**
   * Vypise posledni provedeny dotaz a jeho vysledky
   * Ladici nastroj
   *
   * @return void
   */
  public function debug ()
  {
    echo "\n<!-- DBI DEBUG START -->";
    echo "\n<div style=\"padding: 2px; font-family: sans-serif; font-size: 13px; color: #000; text-align: left;\">";

    if ($this->numDebugQuery == 0) {
      echo "\n<h3>DBI <span style=\"font-weight: normal;\">(v" . DBI::VERSION . ")</span> debug - " . $this->getDbType() . "</h3>";
      echo "\n<style type=\"text/css\">";
      echo "\n  tbody tr:hover td {";
      echo "\n    background: #eee;";
      echo "\n  }";
      echo "\n</style>";
    }

    ++$this->numDebugQuery;

    echo "\n<h4>Query <span style=\"font-weight: normal;\">[" . $this->numDebugQuery . "]</span>:</h4>";
    echo "\n<pre style=\"font-family: monospace; font-weight: bold;\"><code>" . $this->lastQuery() . "</code></pre>";

    /* POUZE PRO MySQL
    if($this->dbo->errno) {
      echo "\n<pre><strong>Error:</strong> ".$this->dbo->errno." - ".$this->dbo->error."</pre>";
    } */

    $tbody = "";
    $thead = "";

    if ($this->lastResult) {
     $first_row = true;
     $rowNum = 0;
     $cols = 1; //zacina od 1 kvuli pridanemu sloupci (row) !
     foreach ($this->lastResult as $row) {
       $rowNum++;
       $tbody .= "\n\t<tr>";
       $tbody .= "<td style=\"padding: 2px 5px;\">" . $rowNum . "</td>";;
       foreach ($row as $name => $value) {
         if ($first_row) {
           $thead .= "<th style=\"padding: 2px 5px;\">" . $name . "</th>";
           ++$cols;
         }
         $tbody .= "<td style=\"padding: 2px 5px;\">" . $value . "</td>";
       }
       $tbody .= "</tr>";
       $first_row = false;
     }
     echo "\n<h4>Result:</h4>";
     echo "\n<table style=\"font-family: sans-serif; font-size: 13px; color: #000;\">";
     echo "\n<thead>";
     echo "\n\t<tr><th style=\"padding: 2px 5px;\">(row)</th>" . $thead . "</tr>";
     echo "\n</thead>";
     echo "\n<tfoot>";
     echo "\n\t<tr><td colspan=\"" . $cols . "\" style=\"padding: 2px 5px;\"><em>Num rows:</em> " . $this->numRows() . "</td></tr>";
     echo "\n</tfoot>";
     echo "\n<tbody>";
     echo $tbody;
     echo "\n</tbody>";
     echo "\n</table>";
    } elseif ($this->affectedRows()) {
      echo "\n<p><strong>Affected rows:</strong> " . $this->affectedRows() . "</p>";
    } else {
      echo "\n<p><strong>No results...</strong></p>";
    }

    echo "\n<hr style=\"height: 1px; border: none; background: #ddd;\" />";
    echo "\n</div>";
    echo "\n<!-- DBI DEBUG END -->\n";
  }

  /**
   * Vrati html vypis posledniho provedeneho dotazu a jeho vysledky
   * Ladici nastroj
   *
   * @return void
   * @since  Metoda je pristupna od verze 0.9.4
   */
  public function debugToString ()
  {
    $s = "";
    $s .= "\n<!-- DBI DEBUG START -->";
    $s .= "\n<div style=\"padding: 2px; font-family: sans-serif; font-size: 13px; color: #000; text-align: left;\">";

    if ($this->numDebugQuery == 0) {
      $s .= "\n<h3>DBI <span style=\"font-weight: normal;\">(v" . DBI::VERSION . ")</span> debug - " . $this->getDbType() . "</h3>";
      $s .= "\n<style type=\"text/css\">";
      $s .= "\n  tbody tr:hover td {";
      $s .= "\n    background: #eee;";
      $s .= "\n  }";
      $s .= "\n</style>";
    }

    ++$this->numDebugQuery;

    $s .= "\n<h4>Query <span style=\"font-weight: normal;\">[" . $this->numDebugQuery . "]</span>:</h4>";
    $s .= "\n<pre style=\"font-family: monospace; font-weight: bold;\"><code>" . $this->lastQuery() . "</code></pre>";

    /* POUZE PRO MySQL
    if($this->dbo->errno) {
      $s .= "\n<pre><strong>Error:</strong> ".$this->dbo->errno." - ".$this->dbo->error."</pre>";
    } */

    $tbody = "";
    $thead = "";

    if ($this->lastResult) {
     $first_row = true;
     $rowNum = 0;
     $cols = 1; //zacina od 1 kvuli pridanemu sloupci (row) !
     foreach ($this->lastResult as $row) {
       $rowNum++;
       $tbody .= "\n\t<tr>";
       $tbody .= "<td style=\"padding: 2px 5px;\">" . $rowNum . "</td>";;
       foreach ($row as $name => $value) {
         if ($first_row) {
           $thead .= "<th style=\"padding: 2px 5px;\">" . $name . "</th>";
           ++$cols;
         }
         $tbody .= "<td style=\"padding: 2px 5px;\">" . $value . "</td>";
       }
       $tbody .= "</tr>";
       $first_row = false;
     }
     $s .= "\n<h4>Result:</h4>";
     $s .= "\n<table style=\"font-family: sans-serif; font-size: 13px; color: #000;\">";
     $s .= "\n<thead>";
     $s .= "\n\t<tr><th style=\"padding: 2px 5px;\">(row)</th>" . $thead . "</tr>";
     $s .= "\n</thead>";
     $s .= "\n<tfoot>";
     $s .= "\n\t<tr><td colspan=\"" . $cols . "\" style=\"padding: 2px 5px;\"><em>Num rows:</em> " . $this->numRows() . "</td></tr>";
     $s .= "\n</tfoot>";
     $s .= "\n<tbody>";
     $s .= $tbody;
     $s .= "\n</tbody>";
     $s .= "\n</table>";
    } elseif ($this->affectedRows()) {
      $s .= "\n<p><strong>Affected rows:</strong> " . $this->affectedRows() . "</p>";
    } else {
      $s .= "\n<p><strong>No results...</strong></p>";
    }

    $s .= "\n<hr style=\"height: 1px; border: none; background: #ddd;\" />";
    $s .= "\n</div>";
    $s .= "\n<!-- DBI DEBUG END -->\n";

    return $s;
  }

  /**
   * Ziska vysledky na danny dotaz
   *
   * @param  string  $query   SQL dotaz
   * @param  string  $output  typ vystupu, viz konstanty DBI::RESULT_O, DBI::RESULT_AA, DBI::RESULT_AI
   * @return mixed   vysledek dotazu (typ urcen parametrem $output)
   */
  public function getResults ($query, $output = DBI::RESULT_AA)
  {
    $this->query($query);

    if ($output == DBI::RESULT_O) {
      return $this->lastResult;
    } elseif ($output == DBI::RESULT_AA || $output == DBI::RESULT_AI) {
      if ($this->lastResult) {
        $i = 0;
        foreach ($this->lastResult as $row) {
          $newArray[$i] = get_object_vars($row);
          if ($output == DBI::RESULT_AI) {
            $newArray[$i] = array_values($newArray[$i]);
          }
          ++$i;
        }

        return $newArray;
      } else {
        return NULL;
      }
    }
  }

  /**
   * Ziska jeden (prvni) radek dle dotazu
   *
   * @param  string  $query   SQL dotaz
   * @param  string  $output  typ vystupu, viz konstanty DBI::RESULT_O, DBI::RESULT_AA, DBI::RESULT_AI
   * @return mixed   vysledek dotazu - jeden radek (typ urcen parametrem $output)
   */
  public function getRow ($query, $output = DBI::RESULT_AA)
  {
    $this->query($query);

    if ($output == DBI::RESULT_O) {
      return $this->lastResult[0];
    } elseif ($output == DBI::RESULT_AA) {
      return $this->lastResult[0] ? get_object_vars($this->lastResult[0]) : NULL;
    } else {
      return $this->lastResult[0] ? array_values(get_object_vars($this->lastResult[0])) : NULL;
    }
  }

  /**
   * Vrati jednu (prvni) hodnotu dle dotazu
   *
   * @param  string  $query  SQL dotaz
   * @return mixed   pozadovana hodnota, jinak NULL
   */
  public function getVar ($query)
  {
    $this->query($query);

    if (($this->lastResult[0])) {
      $values = array_values(get_object_vars($this->lastResult[0]));
      return $values[0];
    }

    return NULL;
  }

  /**
   * Vytvori pole dvojic dle dotazu
   *
   * Z prvnich dvou specifikovanych sloupcu dotazu
   * vytvori dvojice pro asociativni pole
   *
   * @param  string  $query
   * @return array
   * @since  Metoda je pristupna od verze 1.9.2
   */
  public function getPairByRow ($query)
  {
    $this->query($query);

    if ($this->lastResult) {
      foreach ($this->lastResult as $row) {
        $row_values = array_values(get_object_vars($row));
        $new_array[$row_values[0]] = $row_values[1];
      }

      return $new_array;
    } else {
      return NULL;
    }
  }

  /**
   * Vytvori seznam hodnot
   *
   * Vytvori indexovane pole hodnot z prvniho specifikovaneho sloupce
   *
   * @param  string  $query
   * @return array
   * @since  Metoda je pristupna od verze 0.9.2
   */
  public function getSimpleList ($query)
  {
    $this->query($query);

    if ($this->lastResult) {
      $new_array = array();
      foreach ($this->lastResult as $row) {
        $row_values = array_values(get_object_vars($row));
        $new_array[] = $row_values[0];
      }

      return $new_array;
    } else {
      return NULL;
    }
  }

  /**
   * Pocet radku vracenych na posledni (aktualni) dotaz
   * U dotazu SELECT
   *
   * @return int  pocet vracenych radku
   */
  public function numRows ()
  {
    return $this->numRows;
  }

  /**
   * Pocet radku ovlivnenych poslednim (aktualnim) dotazem
   * U dotazu typu UPDATE, DELETE atd.
   *
   * @return int  pocet ovlivnenych radku
   */
  public function affectedRows ()
  {
    return $this->affectedRows;
  }

  /**
   * Aktualni hodnota AUTO_INCREMENT posledniho SQL dotazu
   * U doatzu INSERT
   *
   * @return int  id (nebo tak) posledniho vlozeneho radku
   */
  public function insertId ()
  {
    return $this->insertId;
  }

  /**
   * Posledni (aktualni) SQL dotaz
   *
   * @return  string  dotaz
   */
  public function lastQuery ()
  {
    return $this->lastQuery;
  }

  abstract public function connect ();

  abstract public function setCharset ($charset);

  abstract public function escapeString ($string);

  abstract public function getDbType ();

  abstract public function getDbName ();

  abstract public function query ($query);

  abstract public function multiQuery ($query);

  abstract public function close ();
}
