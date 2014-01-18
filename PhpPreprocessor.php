<?php

/**
 * Nastroj pro minimalizovani PHP kodu
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: PhpPreprocessor.php, 2010-09-25 11:54 $
 */

Base::import('Common');

/**
 * PhpPreprocessor
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8.3
 * @since      Trida je pristupna od verze 0.8.2
 */
class PhpPreprocessor extends Common
{
  /**
   * Povolene soubory (dle koncovky)
   *
   * @var array
   */
  protected static $allowedFiles = array('php');

  /**
   * Nastaveni objektu
   *
   * @var array
   */
  protected $settings;

  /**
   * Seznam souboru, ktere se maji zpracovat
   *
   * @var array
   */
  protected $files;

  /**
   * Nacte soubor a vrati jeho obsah
   *
   * @param  string  $file
   * @return string
   */
  protected function loadFile($file)
  {
    if (!is_file($file)) {
      throw new FileNotFoundException('File "' . $file . '" doesnt exist');
    }

    $fh = @fopen($file, 'r');
    if (!$fh) {
      throw new IOException('File "' . $file . '" unable to open for reading');
    }
    $content = @fread($fh, filesize($file));
    @fclose($fh);

    $content = trim($content);
    return empty($content) ? NULL : $content;
  }

  /**
   * Ulozi obsah do specifikovaneho souboru
   *
   * @param  string  $file
   * @param  string  $content
   * @return void
   */
  protected function saveFile($file, $content)
  {
    if (!$this->settings['replace-existing-file'] && is_file($file)) {
      throw new FileAlreadyExistsException('File "' . $file . '" already exists');
    }

    $fh = @fopen($file, 'w');
    if (!$fh) {
      throw new IOException('File "' . $file . '" unable to create or open for writing');
    }
    $content = @fwrite($fh, $content);
    @fclose($fh);
  }

  /**
   * Spusti pozadovane makro
   *
   * @param  array  $matches
   * @return mixed
   */
  protected function runMacro($matches)
  {
    if (count($matches) < 3) {
      throw new UnexpectedValueException('Invalid macro definition');
    }
    $call = $this->settings['macro-set'] . '::macro' . ucfirst($matches[1]);
    if (!is_callable($call)) {
      throw new InvalidStateException('Macro "' . $matches[1] . '" not implemented in ' . $this->settings['macro-set']);
    }
    if (empty($matches[2]) && !empty($matches[3])) {
      $code = $matches[3];
    } else {
      $code = $this->removeComments($matches[2]);
    }
    return call_user_func($call, $code);
  }

  /**
   * Interpetuje makra
   *
   * @param  string  $content
   * @return string
   */
  protected function interpretMacros($content)
  {
    if (!class_exists($this->settings['macro-set'])) {
      Base::import($this->settings['macro-set']);
    }

    //
    //   + zacatek komentare
    //   |                + nazev makra
    //   |                |          + oteviraci zavorka
    //   |                |          |     + konec komentare
    //   |                |          |     |              + vlastni kod
    //   |                |          |     |              |      + uzaviraci zavorka
    //   |            ____|____      |     |   ___________|______|____
    //   |           /         \     |     |  /                  |    \
    // /\*[ ]*MACRO:([a-zA-Z]\w+)[ ]*{[ ]*\*/((?:[^/]*(?:/(?!\* *}))?)*)
    //                                         \       \__________/ /
    //                                          \____________|_____/
    //                                                   |   |
    //                                                   +---+ netvori zpetne reference (?:)
    //

    //$content = preg_replace_callback('#/\*[ ]*MACRO:([a-zA-Z]\w+)[ ]*{[ ]*\*/((?:[^/]*(?:/(?!\* *}))?)*)#s',
    //                                 array($this, 'runMacro'),
    //                                 $content);

    // doplneno o alternativu, kdy je kod makra primo mezi zavorkami (uvnitr stejneho komentare)
    $content = preg_replace_callback('#/\*[ ]*MACRO:([a-zA-Z]\w+)[ ]*(?:{[ ]*\*/((?:[^/]*(?:/(?!\* *}))?)*)|{([^}]*)} *\*/)#s',
                                     array($this, 'runMacro'),
                                     $content);

    return $content;
  }

  /**
   * Pripravy obsah na zpracovani
   *
   * @param  string $content
   * @return string
   */
  protected function prepare($content)
  {
    $content = str_ireplace("\r\n", "\n", $content);
    $content = str_ireplace("\r", "\n", $content);
    if (substr($content, 0, 5) == "<?php") {
      $content = substr($content, 5);
    }
    if (substr($content, -2) == "?>") {
      $content = substr($content, 0, -2);
    }
    return $content;
  }

  /**
   * Odstrani komentare
   *
   * @param  string  $content
   * @return string
   */
  protected function removeComments($content)
  {
    $start = 0;
    while ($start <= strlen($content)) {
      $cStart = strpos($content, "//");
      if ($cStart === false) {
        break;
      }
      $cEnd = strpos($content, "\n", $cStart);
      $content = substr($content, 0, $cStart) . substr($content, $cEnd);
      $start = $cStart;
    }
    $start = 0;
    while ($start <= strlen($content)) {
      $cStart = strpos($content, "/*");
      if ($cStart === false) {
        break;
      }
      $cEnd = strpos($content, "*/", $cStart) + 2;
      $content = substr($content, 0, $cStart) . substr($content, $cEnd);
      $start = $cStart;
    }
    return $content;
  }

  /**
   * Odstrani prebytecne radky
   *
   * @param  string  $content
   * @return string
   */
  protected function removeEmptyLines($content)
  {
    $content = preg_replace("/ +/i", " ", $content);
    $content = preg_replace("/\t+/i", " ", $content);
    if ($this->settings['one-line']) {
      $content = preg_replace("/\s+/i", " ", $content);
    } else {
      $content = preg_replace("/\s{2,}/i", "\n", $content);
    }
    return $content;
  }

  /**
   * Dodela nutne upravy
   *
   * @param  string  $content
   * @return string
   */
  protected function finish($content)
  {
    $content = "<?php\n" . trim($content);
    return $content;
  }

  /**
   * Inicializace
   *
   * @param  array  $settings
   */
  public function __construct($settings = NULL)
  {
    $this->settings['one-line'] = isset($settings['one-line']) ? (bool) $settings['one-line'] : true;
    $this->settings['all-in-one'] = isset($settings['all-in-one']) ? (bool) $settings['all-in-one'] : true;
    $this->settings['replace-existing-file'] = isset($settings['replace-existing-file']) ? (bool) $settings['replace-existing-file'] : false;
    $this->settings['use-macro'] = isset($settings['use-macro']) ? (bool) $settings['use-macro'] : false;
    $this->settings['macro-set'] = isset($settings['macro-set']) ? $settings['macro-set'] : 'PhpPreprocessor_Macros';
  }

  /**
   * Registrace souboru pro multi zpracovani
   *
   * @param  string  $file
   * @return void
   */
  public function registerFile($file)
  {
    if (empty($file) || $file === true) {
      throw new InvalidArgumentException('File must be specified');
    }
    $this->files[] = $file;
  }

  /**
   * Registrace souboru ze specifikovane slozky
   *
   * @param  string  $dir
   * @param  boolean $recursive
   * @return void
   */
  public function registerDir($dir, $recursive = true)
  {
    if (empty($dir) || $dir === true) {
      throw new InvalidArgumentException('Dir must be specified');
    }
    if (!is_dir($dir)) {
      throw new InvalidArgumentException('Invalid dir');
    }
    $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
    $dir = str_replace('\\', DIRECTORY_SEPARATOR, $dir);
    if (substr($dir, '-1') != DIRECTORY_SEPARATOR) {
      $dir .= DIRECTORY_SEPARATOR;
    }

    $dh = @opendir($dir);
    if (!$dh) {
      throw new IOException('Dir "' . $dir . '" unable to open');
    }
    while (($file = readdir($dh)) !== false) {
      $type = filetype($dir . $file);
      if ($type == 'dir'
          && ($file == '.' || $file == '..')) {
        continue;
      }
      if ($type == 'dir' && $recursive) {
        $this->registerDir($dir . $file, true);
      }
      if ($type == 'file' && in_array(substr($file, '-3'), self::$allowedFiles)) {
        $this->registerFile($dir . $file);
      }
    }
    closedir($dh);
  }

  /**
   * Zpracuje soubor
   *
   * @param  string  $file
   * @param  mixed   $asFile
   * @return string|bool
   */
  public function run($file, $asFile = NULL)
  {
    if (empty($file) || $file === true) {
      throw new InvalidArgumentException('File must be specified');
    }

    $content = $this->loadFile($file);
    $content = $this->prepare($content);
    if ($this->settings['use-macro']) {
      $content = $this->interpretMacros($content);
    }
    $content = $this->removeComments($content);
    $content = $this->removeEmptyLines($content);

    if ($asFile === NULL || $asFile === false) {
      return trim($content);

    } else {
      $content = $this->finish($content);
      if ($asFile === true || empty($asFile)) {
        if (in_array(substr($file, '-3'), self::$allowedFiles)) {
          $asFile = substr($file, 0, '-4') . '.min.php';
        } else {
          $asFile = $file . '.min';
        }
      }
      $this->saveFile($asFile, $content);
    }

    return true;
  }

  /**
   * Zpracuje soubory
   *
   * @param  string  $asFile
   * @return bool
   */
  public function runMulti($asFile)
  {
    if (empty($this->files)) {
      throw new InvalidStateException('File(s) must be registred');
    }
    $content = "";
    foreach ($this->files as $file) {
      if ($this->settings['all-in-one']) {
        $content .= " " . $this->run($file, false);
      } else {
        $this->run($file, true);
      }
    }
    if ($this->settings['all-in-one']) {
      if (empty($asFile)) {
        throw new InvalidArgumentException('Target file must be specified');
      }
      $content = $this->finish($content);
      $this->saveFile($asFile, $content);
    }

    return true;
  }
}
