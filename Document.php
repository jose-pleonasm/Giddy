<?php

/**
 * Reprezentuje dokument nejruznejsiho typu
 * 
 * Experimentalni modul
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Document.php, 2010-10-12 17:56 $
 */

trigger_error('This modul is experimental!', E_USER_NOTICE);

Base::import('Common');

/**
 * Abstrakce dokumentu
 */
abstract class Document extends Common
{
  /**
   * Meta informace o dokumentu
   *
   * @var array
   */
  protected $meta;

  /**
   * Nastavi autora
   *
   * @param  string  $author
   * @return string
   */
  public function setAuthor($author)
  {
    $buffer = $this->meta['author'];
    $this->meta['author'] = $author;
    return $buffer;
  }

  /**
   * Nastavi titulek
   *
   * @param  string  $title
   * @return string
   */
  public function setTitle($title)
  {
    $buffer = $this->meta['title'];
    $this->meta['title'] = $title;
    return $buffer;
  }

  /**
   * Nastavi popis
   *
   * @param  string  $description
   * @return string
   */
  public function setDescription($description)
  {
    $buffer = $this->meta['description'];
    $this->meta['description'] = $description;
    return $buffer;
  }

  /**
   * Nastavi znakovou sadu
   *
   * @param  string  $charset
   * @return string
   */
  public function setCharset($charset = 'UTF-8')
  {
    $buffer = $this->meta['charset'];
    $this->meta['charset'] = $charset;
    return $buffer;
  }

  /**
   * Vrati autora
   *
   * @return string
   */
  public function getAuthor()
  {
    return isset($this->meta['author']) ? $this->meta['author'] : NULL;
  }

  /**
   * Vrati titulek
   *
   * @return string
   */
  public function getTitle()
  {
    return isset($this->meta['title']) ? $this->meta['title'] : NULL;
  }

  /**
   * Vrati popis
   *
   * @return string
   */
  public function getDescription()
  {
    return isset($this->meta['description']) ? $this->meta['description'] : NULL;
  }

  /**
   * Vrati znakovou sadu
   *
   * @return string
   */
  public function getCharset()
  {
    return isset($this->meta['charset']) ? $this->meta['charset'] : NULL;
  }

  /**
   * Factory
   *
   * @param  string  $type
   * @return Document
   */
  public static function factory($type = 'html')
  {
    $type = ucfirst(strtolower($type));
    $classname = "Document_${type}";

    Base::import('Document/' . $type);
    return new $classname();
  }
}

/**
 * Abstrakce generatoru dokumentu
 */
abstract class Document_Builder extends Common
{
  abstract public function build(Document $document);
}
