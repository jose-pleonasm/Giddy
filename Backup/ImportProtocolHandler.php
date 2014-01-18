<?php

/**
 * Nastroj pro obsluhu importniho protokolu
 *
 * @link      http://jose.cz/GiddyFramework
 * @category  Giddy
 * @package   Giddy_Backup
 * @version   $Id: ImportProtocolHandler.php, 2011-02-16 12:44 $
 */

/**
 * Trida pro obsluhu importniho protokolu
 *
 * @package   Giddy_Backup
 * @author    Josef Hrabec
 * @version   Release: 0.1
 * @since     Trida je pristupna od verze 0.1
 */
class ImportProtocolHandler
{
  const SUCCESS = 'OK';
  const FAILED = 'FAILURES';

  protected $sqlDumpRoutine;

  /**
   * Vysledek importu
   *
   * @var string
   */
  protected $result;

  /**
   * Pocet vlozenych radku
   *
   * @var int
   */
  protected $total;

  /**
   * Trvani importu
   *
   * @var float
   */
  protected $time;

  /**
   * Pocet nevlozenych radku
   *
   * @var int
   */
  protected $numOfFailed;

  /**
   * Vyjimky vyvolane behem importu
   *
   * @var array
   */
  protected $exceptions;

  /**
   * Zpravy
   *
   * @var array
   */
  protected $notices;

  /**
   * Vyhodnoti typ dle obsahu specifikovane zpravy
   *
   * @param  string  $notice
   * @return string|false
   */
  public static function getTypeNotice($notice)
  {
    if (stripos($notice, '[debug]') !== false) {
      return 'debug';
    }
    if (stripos($notice, 'fail') !== false) {
      return 'error';
    }
    if (stripos($notice, 'skipped') !== false) {
      return 'skip';
    }
    if (stripos($notice, 'rename') !== false) {
      return 'renamed';
    }
    if (stripos($notice, 'isnt allowed') !== false) {
      return 'isnt-allowed';
    }
    return false;
  }

  /**
   * Upravy SQL dotaz pro report
   *
   * @param  string $query
   * @return string
   */
  public static function setUpQuery($query)
  {
    $pos = stripos($query, 'VALUES (');
    if ($pos) {
      $part1 = substr($query, 0, $pos);
      $part2 = substr($query, $pos);
      $query = $part1 .'<br />' . $part2;
    }
    return $query;
  }

  /**
   * Zpracovani protokolu
   *
   * @param  array $protocol
   * @return void
   */
  public function __construct(array $protocol)
  {
    if (empty($protocol['status'])) {
      throw new InvalidArgumentException('Specified argument is not valid import protocol');
    }

    $this->sqlDumpRoutine = 'ImportProtocolHandler::setUpQuery';

    $this->result      = $protocol['status'];
    $this->total       = (int) $protocol['total'];
    $this->time        = (float) $protocol['time'];
    $this->numOfFailed = (int) $protocol['totalFailed'];
    $this->exceptions  = isset($protocol['exceptions']) ? $protocol['exceptions'] : NULL;
    $this->notices     = isset($protocol['notice']) ? $protocol['notice'] : NULL;
  }

  public function setSqlDumpRoutine($callback)
  {
    if (is_callable($callback)) {
      $this->sqlDumpRoutine = $callback;
    } else {
      throw new InvalidArgumentException('Specified argument is not callable');
    }
  }

  /**
   * Vrati vysledek importu
   *
   * @return string
   */
  public function getSimpleResult()
  {
    return $this->result;
  }

  /**
   * Zda byl import uspesny
   *
   * @return bool
   */
  public function importSuccess()
  {
    return $this->result == self::SUCCESS;
  }

  /**
   * Zda byl import neuspesny
   *
   * @return bool
   */
  public function importFailed()
  {
    return $this->result == self::FAILED;
  }

  /**
   * Vrati pocet vlozenych radku
   *
   * @return int
   */
  public function getTotal()
  {
    return $this->total;
  }

  /**
   * Vrati cas
   *
   * @return float
   */
  public function getTime()
  {
    return $this->time;
  }

  /**
   * Vrati pocet nevlozenych radku
   *
   * @return int
   */
  public function getNumOfFailed()
  {
    return $this->numOfFailed;
  }

  /**
   * Vrati vyjimky vyvolane behem importu
   *
   * @return array
   */
  public function getExceptions()
  {
    return $this->exceptions;
  }

  /**
   * Vrati zpravy
   *
   * @return array
   */
  public function getNotices()
  {
    return $this->notices;
  }

  /**
   * Vygeneruje HTML zpravu z importu
   *
   * @return string
   */
  public function generateHtmlReport()
  {
    $htmlHead = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="content-language" content="cs" />
  <meta http-equiv="content-script-type" content="text/javascript" />
  <meta http-equiv="content-style-type" content="text/css" />
  <title>Import Protocol on ' . $_SERVER['SERVER_NAME'] . '</title>
  <style type="text/css">
  html, body {
    margin: 0;
    padding: 0;
  }
  #page {
    margin: 2em;
  }
  h2 {
    font-size: 1.5em;
    font-family: monospace;
    font-weight: normal;
  }
  table {
    margin: 0 0 1em 0;
    font-size: 1em;
    border-collapse: collapse;
  }
  th {
    padding: 1px 0.3em;
    text-align: left;
  }
  td {
    padding: 1px 0.3em;
    text-align: left;
  }
  .success {
    color: #4e9a06;
  }
  .failed {
    color: #cc0000;
  }
  .time {
    color: #f57900;
  }
  .error {
    color: red;
  }
  .skip {
    color: teal;
  }
  .renamed span {
    padding: 0 1px;
    background: #FFF9D4;
  }
  .debug {
    color: gray;
  }
  ol.list {
    margin: 0 0 1em 0;
    padding: 0 0 0 1em;
    list-style: none;
    font-family: monospace;
  }
  .list li {
    margin: 0 0 0.4em 0;
    padding: 0;
    font-size: 1em;
  }
  #exceptions strong {
    color: red;
  }
  .code {
    margin: 0.4em 0 1em 0;
    padding: 0.1em 0.3em;
    background-color: #ffffcc;
    border: solid 1px #eeeebb;
    font-family: monospace;
  }
  </style>
</head>
<body>
  <div id="page">
';
    $htmlFoot = '
  </div><!-- div#page -->
</body>
</html>
';

    $body = '';
    $body .= '<table>';
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Result:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="' . ($this->importSuccess() ? 'success' : 'failed') . '">' . $this->result . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    //
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Time:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="time">' . $this->time . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    //
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Total:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="">' . $this->total . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    //
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Failed:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="">' . $this->numOfFailed . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    //
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Notices:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="">' . count($this->notices) . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    //
    $body .= '<tr>';
    $body .= '<th>';
    $body .= 'Exceptions:';
    $body .= '</th>';
    $body .= '<td>';
    $body .= '<span class="">' . count($this->exceptions) . '</span>';
    $body .= '</td>';
    $body .= '</tr>';
    $body .= '</table>';

    if ($this->importFailed()) {
      $body .= '<h2 onclick="return !toggle(this, \'report\')">[<span>+</span>] Report</h2>';
      $body .= '<div id="report" class="collapsed">';
      $body .= '<ol class="list">';
      for ($row = 1; $row <= $this->getTotal(); $row++) {
        $body .= '<li>';
        $body .= '<strong>Row ' . $row . ':</strong> ';
        if (isset($this->exceptions[$row])) {
          $e = $this->exceptions[$row];
          $body .= '<span class="failed">FAILED:</span> ' . $e->getMessage();
          if (method_exists($e, 'getQuery')) {
            $query = $e->getQuery();
          }
          if (method_exists($e, 'getSql')) {
            $query = $e->getSql();
          }
          if (isset($query)) {
            if ($this->sqlDumpRoutine) {
              $query = call_user_func($this->sqlDumpRoutine, $query);
            }
            $body .= '<div class="code"> ' . $query . '</div>';
          }
        } else {
          $body .= '<span class="success">OK</span>';
        }
        $body .= '</li>';
      }
      $body .= '</ol>';
      $body .= '</div>';
    }

    if (!empty($this->notices)) {
      $body .= '<h2 onclick="return !toggle(this, \'notices\')">[<span>+</span>] Notices</h2>';
      $body .= '<div id="notices" class="collapsed">';
      $body .= '<ol class="list">';
      foreach ($this->notices as $notice) {
        $body .= '<li class="' . (string) self::getTypeNotice($notice) . '"><span>' . $notice . '</span></li>';
      }
      $body .= '</ol>';
      $body .= '</div>';
    }

    if (!empty($this->exceptions)) {
      $body .= '<h2 onclick="return !toggle(this, \'exceptions\')">[<span>+</span>] Exceptions</h2>';
      $body .= '<div id="exceptions" class="collapsed">';
      $body .= '<ol class="list">';
      foreach ($this->exceptions as $e) {
        $body .= '<li><span><strong>' . get_class($e) . ' #' . (string) $e->getCode() .': </strong> ' . $e->getMessage() . '</span></li>';
      }
      $body .= '</ol>';
      $body .= '</div>';
    }

    $body .= '
    <script type="text/javascript">
    /* <![CDATA[ */
      document.write(\'<style type="text/css"> .collapsed { display: none; } </style>\');
  
      function toggle(link, panelId)
      {
        var span = link.getElementsByTagName(\'span\')[0];
        var panel = document.getElementById(panelId);
        var collapsed = panel.currentStyle ? panel.currentStyle.display == \'none\' : getComputedStyle(panel, null).display == \'none\';
  
        span.innerHTML = collapsed ? \'-\' : \'+\';
        panel.style.display = collapsed ? \'block\' : \'none\';
  
        return true;
      }
    /* ]]> */
    </script>
';

    return $htmlHead . $body . $htmlFoot;
  }

  /**
   * Vypis reportu
   *
   * @return string
   */
  public function __toString()
  {
    $colorMeta   = '#888a85';
    $colorNull   = '#3465a4';
    $colorText   = '#000000';
    $colorResult = '#cc0000';
    $colorNum    = '#4e9a06';
    $colorTime   = '#f57900';

    $s = "<span style=\"font-family: monospace;\">";
    $s .= "\n status <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorResult;\">$this->result</span>";
    $s .= "\n time <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorTime;\">$this->time</span>";
    $s .= "\n total <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorNum;\">$this->total</span>";
    $s .= "\n failed <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorNum;\">$this->numOfFailed</span>";
    $s .= "\n exceptions ";
    if (empty($this->exceptions)) {
      $s .= "<span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorNull;\">NULL</span>";
    } else {
      foreach ($this->exceptions as $e) {
        $s .= "\n  <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorText;\">" . $e->getMessage() . "</span>";
      }
    }
    $s .= "\n notices ";
    if (empty($this->notices)) {
      $s .= "<span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorNull;\">NULL</span>";
    } else {
      foreach ($this->notices as $notice) {
        $s .= "\n  <span style=\"color: $colorMeta;\">=></span> <span style=\"color: $colorText;\">" . $notice . "</span>";
      }
    }
    $s .= "</span>";

    return $s;
  }
}
