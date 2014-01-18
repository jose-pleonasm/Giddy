<?php

/**
 * Modul pro vycejazycny web
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Lang.php, 2011-04-20 14:30 $
 */

Base::import('Common');
Base::import('HttpInput');

/**
 * Reprezentuje zvoleny jazyk
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.4
 * @since      Trida je pristupna od verze 0.1
 */
class Lang extends Common
{
  /**
   * Expiracni doba cookie
   *
   * @var integer
   */
  protected static $cookieExpire = 1577833200;

  /**
   * Nazev cookie pro priznak zvoleneho jazyka
   *  + cookieName:  nazev cookie pro priznak zvoleneho jazyka
   *  + qsParamName: nazev parametru QUERY STRINGu pro priznak zvoleneho jazyka
   *
   * @var array
   */
  protected $settings;

  /**
   * Specifikace moznych jazyku
   *
   * @var array
   */
  protected $possibleLangs;

  /**
   * Zvoleny jazyk
   *
   * @var string
   */
  protected $lang;

  /**
   * Inicializace jazykove volby
   *
   * Pokud jiz byl jazyk zvolen je ziskan z parametru sezeni,
   * jinak se pokusi jazyk nastavit dle parametru HTTP komunikace
   *
   * @param  mixed  $possibleLangs  mozne jazyky
   * @param  string $settings       nastaveni
   */
  public function __construct($possibleLangs, $settings = NULL)
  {
    if (isset($settings['name'])) {
      $this->settings['cookieName']  = $settings['name'];
      $this->settings['qsParamName'] = $settings['name'];
    } else {
      $this->settings['cookieName']  = isset($settings['cookieName']) ? trim($settings['cookieName']) : 'lang';
      $this->settings['qsParamName'] = isset($settings['qsParamName']) ? trim($settings['qsParamName']) : 'lang';
    }
    $this->settings['cookiePath']  = isset($settings['cookiePath']) ? trim($settings['cookiePath']) : '/';

    if (is_array($possibleLangs)) {
      $this->possibleLangs = $possibleLangs;
    } else {
      $this->possibleLangs = explode(',', $possibleLangs);
    }

    if (HttpInput::get($this->settings['qsParamName']) != NULL) {
      $this->set(HttpInput::get($this->settings['qsParamName']));

    } elseif (HttpInput::cookie($this->settings['cookieName']) != NULL) {
      $this->set(HttpInput::cookie($this->settings['cookieName']));
    }
    if (!in_array($this->lang, $this->possibleLangs)) {
      $this->clear();
    }
  }

  /**
   * Jestli uz je jazyk nastaven
   *
   * @since  metoda je pristupna od verze 0.9.4
   * @return bool
   */
  public function isAlreadySet()
  {
    return $this->lang != NULL && $this->lang != '';
  }

  /**
   * Automaticke zvoleni jazyka
   *
   * @since  metoda je pristupna od verze 0.9.4
   * @return string
   */
  public function choose()
  {
    // odhadnuti odpovidajiciho jazyku
    @$languages = explode(',', HttpInput::server('ACCEPT_LANGUAGE'));
    foreach ($languages as $language) {
      $languageCode = substr($language, 0, 2);
      if (in_array($languageCode, $this->possibleLangs)) {
        // nasteveni jazyka (prvniho odpovidajiciho)
        $this->set($languageCode);
        return $this->lang;
      }
    }
    // pokud se k zadnemu vhodnemu jazyku nedopracovalo, je pouzit prvni ze specifikovanych
    if (empty($this->lang)) {
      $this->set($this->possibleLangs[0]);
    }
    return $this->lang;
  }

  /**
   * Zkontroluje platnost zvoleneho jazyka
   *
   * @return boolean  true pokud je platny, jinak false
   */
  public function check()
  {
    return in_array($this->lang, $this->possibleLangs);
  }

  /**
   * Nastavi jazyk
   *
   * @param  string  $lang  zvoleny jazyk
   */
  public function set($lang)
  {
    $this->lang = $lang;
    // cookie
    @setCookie($this->settings['cookieName'], $lang, self::$cookieExpire, $this->settings['cookiePath']);
  }

  /**
   * Vrati zvoleny jazyk
   *
   * @since  metoda je pristupna od verze 0.9.4
   * @return string  zvoleny jazyk
   */
  public function get()
  {
    if ($this->lang == NULL || $this->lang == '') {
      $this->choose();
    }
    return $this->lang;
  }

  /**
   * Vrati zvoleny jazyk
   *
   * @return string  zvoleny jazyk
   * @deprecated
   */
  public function getLang()
  {
    return $this->get();
  }

  /**
   * Nuluje volbu
   *
   * @since  metoda je pristupna od verze 0.9.4
   */
  public function clear()
  {
    $this->lang = NULL;
    // cookie
    @setCookie($this->settings['cookieName'], '', time()-42000, $this->settings['cookiePath']);
  }

  /**
   * Nuluje volbu
   *
   * @deprecated
   */
  public function clearLang()
  {
    $this->clear();
  }

  /**
   * Vrati nastaveny parametr QUERY STRINGu
   *
   * @since  metoda je pristupna od verze 0.9.4
   * @return string
   */
  public function getQsParam()
  {
    return $this->settings['qsParamName'] . '=' . $this->lang;
  }

  /**
   * Vrati nastaveny parametr QUERY STRINGu
   *
   * @return string
   * @deprecated
   */
  public function getLangPar()
  {
    return $this->settings['qsParamName'] . '=' . $this->lang;
  }

  /**
   * Vrati mozne jazyku
   *
   * @return array  seznam moznych jazyku
   */
  public function getPossibleLangs()
  {
    return $this->possibleLangs;
  }

  /**
   * @since metoda je pristupna od verze 0.9.4
   */
  public function __toString()
  {
    return $this->lang;
  }
}
