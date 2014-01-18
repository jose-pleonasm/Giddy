<?php

/**
 * Jednoducha galerie
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: LightGallery.php, 2011-05-26 11:11 $
 */

Base::import('Common');

/**
 * Realizuje galerie
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.4
 * @since      Trida je pristupna od verze 0.1
 */
class LightGallery extends Common
{
  /**
   * Nazev XML souboru s nastavenim
   */
  const CONFIG_FILE_NAME = 'config.xml';

  /**
   * Zakladni sablona - list
   */
  const TPL_CLASSIC = 'LightGallery/templates/classic.phtml';

  /**
   * Zakladni sablona - list
   */
  const TPL_LIST = 'LightGallery/templates/list.phtml';

  /**
   * Umisteni specifikovane galerie - adresar
   *
   * @var string
   */
  protected $path;

  /**
   * Nazev instance/galerie
   *
   * @var string
   */
  protected $name;

  /**
   * Nastaveni galerie
   *
   * @var LightGallery_Config
   */
  protected $cfg;

  /**
   * Obrazky ze specifikovaneho adresare:
   *  - original: cesta k velkemu obrazku
   *  - thumb:    cesta k nahledu
   *
   * @var array
   */
  protected $pics;

  /**
   * Nastaveni instance
   *
   * @var array
   */
  protected $settings = array();

  /**
   * Radici mechanizmus - zakladni
   *
   * @param  array  $a  prvni pole k porovnani
   * @param  array  $b  prvni pole k porovnani
   * @return integer odpovidajici ohodnoceni: -1, 0, 1
   */
  protected static function cmpBase($a, $b)
  {
    return strcmp($a['original'], $b['original']);
  }

  /**
   * Radici mechanizmus - dle vysky obrazku
   *
   * @param  array  $a  prvni pole k porovnani
   * @param  array  $b  prvni pole k porovnani
   * @return integer odpovidajici ohodnoceni: -1, 0, 1
   */
  protected static function cmpHeight($a, $b)
  {
    if ($a['height'] == $b['height']) {
      return 0;
    }
    return ($a['height'] < $b['height']) ? -1 : 1;
  }

  /**
   * Radici mechanizmus - dle sirky obrazku
   *
   * @param  array  $a  prvni pole k porovnani
   * @param  array  $b  prvni pole k porovnani
   * @return integer odpovidajici ohodnoceni: -1, 0, 1
   */
  protected static function cmpWidth($a, $b)
  {
    if ($a['width'] == $b['width']) {
      return 0;
    }
    return ($a['width'] < $b['width']) ? -1 : 1;
  }

  /**
   * Nasatvi fci pro razeni
   *
   * @param  callback  $fce
   * @return void
   */
  protected function setCmp($fce = '')
  {
    if (empty($fce)) {
      $this->settings['sortFce'] = array(__CLASS__, 'cmpBase');
      return NULL;
    }
    if ($fce == 'cmpBase'
        || $fce == 'cmpWidth'
        || $fce == 'cmpHeight') {
      $this->settings['sortFce'] = array(__CLASS__, $fce);
    } else {
      $this->settings['sortFce'] = $fce;
    }
  }

  /**
   * Nacte soubory ve specifikovane ceste
   *
   * @throws BaseGallery_LoadFilesException  pokud adresar neobsahuje obrazky
   */
  protected function loadGallery()
  {
    $handle = opendir($this->path);
    if (!$handle) {
      throw new DirectoryNotFoundException('Open directory ' . $this->path . ' failed');
    }
    $i = 0;
    while ($file = readdir($handle)) {
      //testovane obsahu slozky
      if (is_file($this->path . $file) &&
          is_file($this->path . $this->cfg->prefix . $file)) {
        //pokud se ma testovat i format souboru
        if ($this->settings['checkFileType']
            || $this->settings['sortFce'] == array(__CLASS__, 'cmpHeight')
            || $this->settings['sortFce'] == array(__CLASS__, 'cmpWidth')) {
          $imageInfo = getimagesize($this->path . $this->cfg->prefix . $file);
          if ($this->settings['checkFileType'] && $imageInfo[2] != 2) {
            continue;
          }
          $this->pics[$i]['width']  = $imageInfo[0];
          $this->pics[$i]['height'] = $imageInfo[1];
        }
        $this->pics[$i]['original'] = $file;
        $this->pics[$i]['thumb']    = $this->cfg->prefix . $file;
        $i++;
      }
    }
    if ($i == 0) {
      throw new EmptyDirectoryException('No pictures in directory' . $this->path);
    }
    usort($this->pics, $this->settings['sortFce']);
    closedir($handle);
  }

  /**
   * Nacte popisky k obrazkum
   *
   * @return array  vrati popisky, nebo false pokud nejsou
   */
  protected function getDescriptions()
  {
    if (is_file($this->path . $this->cfg->desc_file)) {
      $desc_array = file($this->path . $this->cfg->desc_file);
      foreach ($desc_array as $desc_row) {
        list($pic, $desc) = explode(';', $desc_row);
        $descriptions[$pic] = trim($desc);
      }
      return $descriptions;
    }

    return false;
  }

  /**
   * Inicializace
   *
   * @param  string  $path  cesta ke galerii
   * @param  string  $name  nazev/id galerie
   * @param  array   $settings  nastaveni instance
   */
  public function __construct($path, $name = '', $settings = NULL)
  {
    // cesta k obrazkum
    $this->path = $path;

    if (empty($name)) {
      $this->name = 'bgal-' . crc32($path);
    } else {
      $this->name = $name;
    }

    // nastaveni instance
    $this->setCmp($settings['sortFce']);
    $this->settings['checkFileType'] = isset($settings['checkFileType']) ? $settings['checkFileType'] : false;
    $this->settings['useLightbox']   = isset($settings['useLightbox']) ? $settings['useLightbox'] : true;
    $this->settings['jsWinParams']   = isset($settings['jsWinParams'])
                                     ? $settings['jsWinParams']
                                     : 'toolbar=no, menubar=no, location=no, directories=no, scrollbars=yes, resizable=yes, status=yes, width=800, height=600';

    // nastaveni galerie
    $this->cfg = new LightGallery_Config();
    $this->cfg->load($this->path . self::CONFIG_FILE_NAME);
    $this->loadGallery();
  }

  /**
   * Vrati seznam obrazku
   *
   * @return string
   */
  public function __toString()
  {
    $buffer = "";
    foreach ($this->pics as $pic) {
      $buffer .= $pic['original'] . "\n";
    }
    return $buffer;
  }

  /**
   * Vratin hodnotu specifikovane konfiguracni polozky
   *
   * @param  string  $name  nazev pozadovane polozky
   * @return mixed   hodnota polozky
   */
  public function getCfg($name)
  {
    return $this->cfg->$name;
  }

  /**
   * Pocet obrazku v galerii
   *
   * @return integer
   */
  public function numberOfPics()
  {
    return count($this->pics);
  }

  /**
   * Vypise galerii
   *
   * @since  metoda (respektive jeji soucasny nazev) je pristupna od verze 0.9.4f
   * @param  string  $template  vystupni sablona
   * @param  integer $page      cislo stranky galerie, ktera se ma zobrazit (jen pri strankovani)
   * @param  string  $hrefBase  url odkazu pro strankovani galerie
   * @param  string  $selfPath  vlastni cesta pro vlozeni obrazky (pro atribut src)
   * @return void
   */
  public function render($template = NULL, $page = 1, $hrefBase = '', $selfPath = NULL)
  {
    if (empty($template)) {
      $template = self::TPL_LIST;
    }
    //spocitani nutnych hodnot: posledni soubor stranky, posledni stranka atd.
    $firstPic  = 0;
    $lastPic   = count($this->pics);
    $firstPage = 1;
    $lastPage  = floor($lastPic / $this->cfg->thumb_num);
    if (($lastPic / $this->cfg->thumb_num) != $lastPage) {
      $lastPage++;
    }
    if ($page < 1 || $page > $lastPage) {
      $page    = 1;
    }
    $beginPic  = ($page - 1) * $this->cfg->thumb_num;
    if (($beginPic + $this->cfg->thumb_num) > $lastPic) {
      $endPic  = $lastPic;
    } else {
      $endPic  = $beginPic + $this->cfg->thumb_num;
    }

    // nasatveni hodnot pro vypis
    $name = $this->name;
    $items = $this->pics;
    $path = empty($selfPath) ? $this->path : $selfPath;
    $useLightbox = $this->settings['useLightbox'];
    $desc   = $this->getDescriptions();
    $title  = ($this->cfg->gallery_name != '') ? $this->cfg->gallery_name : NULL;
    $author = ($this->cfg->author != '') ? $this->cfg->author : NULL;
    $date   = ($this->cfg->date != '') ? $this->cfg->date : NULL;
    $note   = ($this->cfg->note != '') ? $this->cfg->note : NULL;
    $useJs  = $this->cfg->only_foto;
    $picsInLine = $this->cfg->row_thumb_num;
    // prideleni popisku
    foreach ($this->pics as $pic) {
      if (!empty($desc[$pic['original']])) {
        $pic_desc[$pic['original']] = $desc[$pic['original']];
      }
      $desc[$pic['original']] = isset($desc[$pic['original']])
                              ? $desc[$pic['original']] : $this->cfg->implicit_desc;
    }

    require $template;
  }

  /**
   * Vypise galerii
   *
   * @param  string  $template  vystupni sablona
   * @param  integer $page      cislo stranky galerie, ktera se ma zobrazit (jen pri strankovani)
   * @param  string  $hrefBase  url odkazu pro strankovani galerie
   * @param  string  $selfPath  vlastni cesta pro vlozeni obrazky (pro atribut src)
   * @return void
   */
  public function printGallery($template = NULL, $page = 1, $hrefBase = '', $selfPath = NULL)
  {
    $this->render($template, $page, $hrefBase, $selfPath);
  }
}
