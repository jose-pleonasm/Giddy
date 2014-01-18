<?php

Base::import('HtmlTools/HtmlE');

class HtmlDoc extends HtmlE
{
  protected $doc;

  protected static function getHtml ($doc)
  {
    $pos = strpos($doc, '<html');
    if ($pos !== false) {
      return trim(substr($doc, $pos));
    } else {
      throw new InvalidArgumentException('Source is not valid HTML document');
    }
  }

  public static function loadFile ($file)
  {
    if (!is_file($file)) {
      throw new FileNotFoundException('File "' . $file . '" doesnt exist');
    }

    $fh = @fopen($file, 'r');
    if (!$fh) {
        throw new IOException('File ' . $file . ' unable to open for reading');
      }
    $content = @fread($fh, filesize($file));
    @fclose($fh);

    $content = trim($content);
    return empty($content) ? NULL : $content;
  }

  public function __construct($file)
  {
    $this->doc = self::loadFile($file);
    parent::__construct('/', NULL, self::getHtml($this->doc));
  }

  public function __toString ()
  {
    return $this->doc;
  }
}
