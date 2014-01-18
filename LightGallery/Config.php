<?php
/**
 * Nastaveni galerie
 *
 * Modul pro definovani konstant pro LightGallery
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @subpackage LightGallery
 * @version    $Id: Config.php, 2011-07-12 16:42 $
 */

/**
 * Definuje nastaveni galerie
 *
 * @package    Giddy
 * @subpackage LightGallery
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class LightGallery_Config
{
  /**
   * Vychozi nastaveni
   *
   * @var array
   */
  private $settings;

  /**
   * Inicializace
   */
  public function __construct ()
  {
    //zda vypisovat strankovani galerie
    $this->settings['use_paging']      = false;
    //pocet nahledu na strance
    $this->settings['thumb_num']       = '25';
    //pocet nahledu na radce
    $this->settings['row_thumb_num']   = '5';
    //prefix souboru s nahledem
    $this->settings['prefix']          = 'preview/';
    //nazev souboru s popiskami
    $this->settings['desc_file']       = '';
    //zobrazovat popisky u fotek [0|1]
    $this->settings['show_desc']       = '0';
    //zobrazovat popisky i u nahledu [0|1]
    $this->settings['show_thumb_desc'] = '1';
    //zobrazit pouze fotku v novem okne [0|1]
    $this->settings['only_foto']       = '0';
    //implicitni title a alt, tam kde neni popisek
    $this->settings['implicit_desc']   = ' ';
    //nazev akce, odkud fotky pochazi
    $this->settings['gallery_name']    = '';
    //datum vytvoreni fotek
    $this->settings['date']            = '';
    //autor fotografii
    $this->settings['author']          = '';
    //poznamka k fotogalerii
    $this->settings['note']            = '';
  }

  /**
   * Nacte nastaveni specifikovane galerie
   *
   * @param  string  $file  cesta k XML dokumentu s nastavenim
   * @return boolean true pokud ok, jinak false
   */
  public function load ($file)
  {
    if (!is_file($file)) {
      return false;
    }
    @$xml = simplexml_load_file($file);
    if (empty($xml)) {
      return false;
    }
    foreach ($xml->const as $const) {
      $name = strtolower((string) $const['name']);
      if (!empty($name)) {
        $this->settings[$name] = (string) $const;
      }
    }
    return true;
  }

  /**
   * Vrati pozadovanou hodnotu
   *
   * @param  string  $name  nazev polozky
   * @return mixed   hodnotu pozadovane polozky
   */
  public function __get ($name)
  {
    return $this->settings[strtolower($name)];
  }
}
