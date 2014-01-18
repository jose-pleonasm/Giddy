<?php

Base::import('Common');

class HtmlE extends Common
{
  protected $name;

  protected $attrs;

  protected $content;

  protected static function handleIdForPattern ($id)
  {
    if (strpos($id, '-') !== false) {
      $id = str_replace('-', '\-', $id);
    }
    if (strpos($id, '_') !== false) {
      $id = str_replace('_', '\_', $id);
    }
    return trim($id);
  }

  public function __construct($name, $attrs, $content)
  {
    $this->name = $name;
    $this->attrs = $attrs;
    $this->content = $content;
  }

  public function getElementById ($id)
  {
    $pattern = '/<([a-z]{1,20})[ ]*id="' . self::handleIdForPattern($id) . '"[^>]*>/i';
    var_dump($pattern);
    preg_match($pattern, $this->content, $matches, PREG_OFFSET_CAPTURE, 0);

    $nodeStart = $matches[0][1] + strlen($matches[0][0]);
    // FIXME: najde uz prvni vyskyt uzaviraciho tagu, ale uvnitr elementu muze byt vice stejnych tagu
    preg_match('/<' . $matches[1][0] . '/i', $this->content, $matchNext, PREG_OFFSET_CAPTURE, $nodeStart);
    preg_match('/<\/' . $matches[1][0] . '>/i', $this->content, $matchEnd, PREG_OFFSET_CAPTURE, $nodeStart);
    /*
    $count = 0;
    while ($matchNext[0][1] < $matchEnd[0][1]) {
      $count++;
      $offset = $matchNext[0][1] + strlen($matchNext[0][0]);
      preg_match('/<' . $matches[1][0] . '/i', $this->content, $matchNext, PREG_OFFSET_CAPTURE, $offset);
      var_dump($matchNext[0][1]);
      var_dump($matchEnd[0][1]);
      var_dump($matchNext[0][1] < $matchEnd[0][1]);
    }
    */
    
    $nodeEnd = $matchEnd[0][1] - strlen($matchEnd[0][0]);
    $node = substr($this->content, $nodeStart, ($nodeEnd - $nodeStart));

    return new self($matches[1][0], NULL, $node);
  }

  public function getE ($term)
  {
    if (strpos($term, ' ') !== false) {
      throw new InvalidStateException('Dont implement for now');
      $terms = explode(' ', $term);
    }

    $name = '';
    if (substr($term, 0, 1) == '#') {
      $name = substr($term, 1);
      $e = $this->getElementById($name);

    } elseif (substr($term, 0, 1) == '.') {
      $name = substr($term, 1);

    } else {
      $name = $term;
    }
    return $e;
  }

  public function getName ()
  {
    return $this->name;
  }

  public function getContent ()
  {
    return $this->content;
  }

  public function __toString ()
  {
    return "<$this->name>$this->content</$this->name>";
  }
}
