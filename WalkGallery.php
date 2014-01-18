<?php

class WalkGallery
{
  const DESC_FILE = 'desc.txt';

  /**
   * Umisteni specifikovane galerie - adresar
   *
   * @var string
   */
  protected $path;

  /**
   * Seznam obrazku
   *
   * @var array
   */
  protected $pics;

  /**
   * Nacte soubory ve specifikovane ceste
   *
   * @throws DirectoryNotFoundException  pokud adresar neexisutje
   * @throws EmptyDirectoryException     pokud adresar neobsahuje obrazky
   */
  protected function loadGallery()
  {
    $handle = opendir($this->path);
    if (!$handle) {
      throw new DirectoryNotFoundException('Open directory ' . $this->path . ' failed');
    }
    $i = 0;
    $descs = self::getDescriptions();
    while ($file = readdir($handle)) {
      if (is_file($this->path . $file)) {
        $bin  = substr($file, -3);
        if ($bin != 'jpg'
            && $bin != 'png'
            && $bin != 'gif') {
          continue;
        }
        $name = substr($file, 0, -4);
        $this->pics[$i]['name'] = $name;
        $this->pics[$i]['path'] = $this->path . $file;
        if (isset($descs[$name])) {
          $this->pics[$i]['desc'] = $descs[$name];
        }
        $i++;
      }
    }
    if ($i == 0) {
      throw new EmptyDirectoryException('No pictures in directory' . $this->path);
    }
  }
  /**
   * Nacte popisky k obrazkum
   *
   * @return array  vrati popisky, nebo false pokud nejsou
   */
  protected function getDescriptions()
  {
    if (is_file($this->path . self::DESC_FILE)) {
      $descArray = file($this->path . self::DESC_FILE);
      foreach ($descArray as $descRow) {
        list($pic, $desc) = explode(';', $descRow);
        $descriptions[$pic] = trim($desc);
      }
      return $descriptions;
    }

    return false;
  }

  public function __construct($path)
  {
    $this->path = $path;
  }

  public function count()
  {
    if (empty($this->pics)) {
      $this->loadGallery();
    }
    return count($this->pics);
  }

  public function getPic($index = 0)
  {
    if (empty($this->pics)) {
      $this->loadGallery();
    }
    return isset($this->pics[$index]) ? $this->pics[$index] : false;
  }

  public function getPics()
  {
    if (empty($this->pics)) {
      $this->loadGallery();
    }
    return $this->pics;
  }
}
