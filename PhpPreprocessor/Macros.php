<?php

/**
 * Soubor maker pro PhpPreprocessor
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Macros.php, 2010-09-25 11:54 $
 */

/**
 * PhpPreprocessor_Macros
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.8.3
 * @since      Trida je pristupna od verze 0.8.3
 */
class PhpPreprocessor_Macros
{
  /**
   * Nacte obsah souboru
   *
   * @param  string  $const
   * @param  string  $concrete
   * @return string|false
   */
  protected static function getFileContent($const, $concrete)
  {
    $path = '';
    if (!empty($const)) {
      if (!defined($const)) {
        throw new RuntimeException('Constant "' . $const . '" not defined');
      }
      $path .= eval('return ' . $const . ';');
    }
    if (!empty($concrete)) {
      $path .= $concrete;
    }
    if (is_file($path)) {
      $fh = @fopen($path, 'r');
      if (!$fh) {
        throw new IOException('File "' . $path . '" unable to open for reading');
      }
      $content = @fread($fh, filesize($path));
      @fclose($fh);
      return $content;
    }
    return false;
  }

  /**
   * Odstrani cast
   *
   * @param  string  $code
   * @return string
   */
  public static function macroRemove($code)
  {
    return '';
  }

  /**
   * Interpretuje cast jako PHP kod
   *
   * @param  string  $code
   * @return string
   */
  public static function macroExe($code)
  {
    $result = eval($code);
    return is_string($result) ? $result : '';
  }

  /**
   * Vlozi obsah pozadovanych souboru primo v kodu
   *
   * @param  string  $code
   * @return string
   */
  public static function macroRequireLive($code)
  {
    $result = '';
    $code = str_replace("\r\n", "\n", $code);
    $code = str_replace("\r", "\n", $code);
    $lines = explode("\n", $code);
    if (count($lines) > 0) foreach ($lines as $line) {
      preg_match_all('/require(?:_once)? *\(?([A-Z_][A-Z0-9_]+)? *\.? *(?:[\'|"]([^\'|"]*))?/', $line, $matches, PREG_SET_ORDER);
      if (!empty($matches[0])) {
        foreach ($matches as $match) {
          $const = empty($match[1]) ? NULL : $match[1];
          $string = empty($match[2]) ? NULL : $match[2];
          $buffer = self::getFileContent($const, $string);
          if (substr($buffer, 0, 5) == '<?php') {
            $buffer = substr($buffer, 5);
          }
          if (substr($buffer, -2) == '?>') {
            $buffer = substr($buffer, 0, -2);
          }
          $result .= $buffer;
        }
      }
    }
    return $result;
  }

  /**
   * Vlozi obsah specifikovaneho souboru
   *
   * @param  string  $code
   * @return string
   */
  public static function macroRequire($code)
  {
    $result = '';
    preg_match('/([A-Z_][A-Z0-9_]+)? *\.? *(?:[\'|"]([^\'|"]*))?/', $code, $matches);
    $const = empty($matches[1]) ? NULL : $matches[1];
    $string = empty($matches[2]) ? NULL : $matches[2];
    $buffer = self::getFileContent($const, $string);
    if (substr($buffer, 0, 5) == '<?php') {
      $buffer = substr($buffer, 5);
    }
    if (substr($buffer, -2) == '?>') {
      $buffer = substr($buffer, 0, -2);
    }
    $result .= $buffer;

    return $result;
  }

  /**
   * Vypise obsah na vystup
   *
   * @param  string  $code
   * @return void
   */
  public static function macroPrint($code)
  {
    echo $code . "\n";
  }
}
