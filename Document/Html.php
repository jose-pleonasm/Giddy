<?php

/**
 * Reprezentuje HTML dokument
 * 
 * Experimentalni modul
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Html.php, 2010-10-12 18:13 $
 */

Base::import('Document/interfaces');

/**
 * Reprezentuje HTML
 */
class Document_Html extends Document implements Tabular, Structural
{
  /**
   * Nazev vychoziho builderu
   *
   * @var string
   */
  private static $defaultBuilderName = 'Document_Html_DefaultBuilder';

  /**
   * Zvoleny builder
   *
   * @var Document_Builder
   */
  private $builder;

  /**
   * Ukazatel aktualniho radku v tabulce
   *
   * @var int
   */
  private $tableRowPointer = 0;

  /**
   * Uloziste dat dokumentu
   *
   * @var array
   */
  protected $body;

  /**
   * Uloziste dat tabulky
   *
   * @var array
   */
  protected $table;

  /**
   * Vlozi element
   *
   * @param  string  $type
   * @param  string  $value
   * @param  array   $classes
   */
  protected function addElement($type, $value, $classes = NULL)
  {
    $this->body[] = array(
      'type' => $type,
      'value' => $value,
      'classes' => $classes
    );
  }

  /**
   * Vlozi nadpis
   *
   * @param  string  $text
   * @param  int     $level
   */
  public function addHeadline($text, $level = 1)
  {
    if (0 > $level || $level > 6) {
      throw new InvalidArgumentException('Headline level is 1 - 6');
    }
    $this->addElement('h' . $level, $text);
  }

  /**
   * Vlozi odstavec
   *
   * @param  string  $text
   */
  public function addP($text)
  {
    $this->addElement('p', $text);
  }

  /**
   * Nastavy konkretni kolonku tabulky
   *
   * @param  int   $column
   * @param  int   $row
   * @param  mixed $value
   * @return mixed vrati predchozi hodnotu kolonky
   */
  public function setCellValue($column, $row, $value)
  {
    $previous = isset($this->table[$row][$column]) ? $this->table[$row][$column] : NULL;
    $this->table[$row][$column] = $value;
    return $previous;
  }

  /**
   * Nastavy nasledujici kolonku/kolonky tabulky
   *
   * @param  mixed  $value
   */
  public function addTableValue($value)
  {
    if (is_array($value)) {
      foreach ($value as $item) {
        $this->table[$this->tableRowPointer][] = $item;
      }
    } else {
      $this->table[$this->tableRowPointer][] = $value;
    }
  }

  /**
   * Presun na dalsi radek tabulky
   *
   * Posune ukazatel na nasledujici radek
   */
  public function tableRowMove()
  {
    $this->tableRowPointer++;
  }

  /**
   * Vrati telo dokuentu
   *
   * @return array
   */
  public function getBody()
  {
    return $this->body;
  }

  /**
   * Vrati data tabulky
   *
   * @return array
   */
  public function getTable()
  {
    return $this->table;
  }

  /**
   * Nasatvy builder
   *
   * @param Document_Builder $builder
   */
  public function setBuilder(Document_Builder $builder)
  {
    $this->builder = $builder;
  }

  /**
   * Vrati builder
   *
   * @return Document_Builder
   */
  public function getBuilder()
  {
    if (empty($this->builder)) {
      return new self::$defaultBuilderName;
    }
    return $this->builder;
  }

  /**
   * Sestavy dokument a vratiho jako retezec
   *
   * @return string
   */
  public function __toString()
  {
    $builder = $this->getBuilder();
    return $builder->build($this);
  }
}

/**
 * Vygenreuje HTMl dokument ze zdroje
 */
class Document_Html_DefaultBuilder extends Document_Builder
{
  /**
   * Vlastni dokument
   *
   * @var Document
   */
  protected $document;

  /**
   * CSS specifikace
   *
   * @var string
   */
  protected $style;

  /**
   * Definice vychoziho stylu
   *
   * @var string
   */
  public static $defaultStyle = '
html {
  margin: 0;
  padding: 0;
}
body {
  margin: 0;
  padding: 1em;
  background: #fff;
  color: #000;
  font-size: 86%;
  font-family: sans-serif;
}
h1, h2, h3, h4, h5, h6,
p, ol, ul, dl, table, form, fieldset {
  margin: 0 0 1em 0;
  padding: 0;
}
';

  /**
   * Vytvori telo dokumentu
   *
   * @param  array  $body
   * @return string
   */
  protected function buildBody($body)
  {
    $s = '';
    foreach ($body as $element) {
      $s .= '<' . $element['type'];
      if (!empty($element['classes'])) {
        $s .= ' class="' . implode(' ', $element['classes']) . '"';
      }
      if ($element['type'] == 'img') {
        $s .= ' src="' . $element['value'] . '"';
        $s .= ' alt="' . $element['value'] . '"';
        $s .= ' />';
      } else {
        $s .= '>';
        $s .= $element['value'];
        $s .= '</' . $element['type'] . '>';
      }
    }
    return $s;
  }

  /**
   * Vytvori tabulku
   *
   * @param  array  $table
   * @return string
   */
  protected function buildTable($table)
  {
    $totalColumns = 0;
    foreach ($table as $row) {
      $totalColumns = count($row) > $totalColumns ? count($row) : $totalColumns;
    }

    $s = '<table>';
    for ($row = 0; $row < count($table); $row++) {
      $s .= '<tr>';
      for ($column = 0; $column < $totalColumns; $column++) {
        if (isset($table[$row][$column])) {
          $s .= '<td>' . $table[$row][$column] . '</td>';
        } else {
          $s .= '<td></td>';
        }
      }
      $s .= '</tr>';
    }
    $s .= '</table>';
    return $s;
  }

  /**
   * Vrati hlavicku dokumentu
   *
   * @return string
   */
  public function getHtmlHeader()
  {
    if (empty($this->document)) {
      throw new InvalidStateException('Document must be specified');
    }
    $s = '<?xml version="1.0" encoding="' . $this->document->getCharset() . '"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
  <meta http-equiv="content-type" content="text/html; charset=' . $this->document->getCharset() . '" />
  <meta http-equiv="content-language" content="cs" />
';
    if ($this->style) {
      $s .= '<style type="text/css">';
      $s .= $this->style;
      $s .= '</style>';
    }
    $s .= '
  <title>' . $this->document->getTitle() . '</title>
</head>
<body>';

    return $s;
  }

  /**
   * Vrati paticku dokumentu
   *
   * @return string
   */
  public function getHtmlFooter()
  {
    return '</body></html>';
  }

  /**
   * Pouzije styl
   *
   * @param  string|NULL  $style
   */
  public function useStyle($style = NULL)
  {
    if (empty($style)) {
      $this->style = self::$defaultStyle;
    } else {
      $this->style = $style;
    }
  }

  /**
   * Vygeneruje dokument
   *
   * @param  Document  $document
   * @param  bool      $completeDocument
   * @return string
   */
  public function build(Document $document, $completeDocument = true)
  {
    $this->document = $document;
    $s = '';
    if ($completeDocument) {
      $s .= $this->getHtmlHeader();
    }
    $body  = $document->getBody();
    if (!empty($body)) {
      $s .= $this->buildBody($body);
    }
    $table = $document->getTable();
    if (!empty($table)) {
      $s .= $this->buildTable($table);
    }
    if ($completeDocument) {
      $s .= $this->getHtmlFooter();
    }
    return $s;
  }
}
