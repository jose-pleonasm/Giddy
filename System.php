<?php

/**
 * System
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: System.php, 2010-08-12 13:40 $
 */

/**
 * Implementuje zakladni fce pro prostredi na kterem web bezi
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class System
{
  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * OS independant PHP extension load. Remember to take care
   * on the correct extension name for case sensitive OSes.
   *
   * @param  string $ext The extension name
   * @return bool   Success or not on the dl() call
   */
  public static function loadExtension($ext)
  {
    if (!extension_loaded($ext)) {
      // if either returns true dl() will produce a FATAL error, stop that
      if ((ini_get('enable_dl') != 1) || (ini_get('safe_mode') == 1)) {
        return false;
      }
      if (self::isWin()) {
        $suffix = '.dll';

      } elseif (PHP_OS == 'HP-UX') {
        $suffix = '.sl';

      } elseif (PHP_OS == 'AIX') {
        $suffix = '.a';

      } elseif (PHP_OS == 'OSX') {
        $suffix = '.bundle';

      } else {
        $suffix = '.so';
      }
      return @dl('php_'.$ext.$suffix) || @dl($ext.$suffix);
    }
    return true;
  }

  /**
   * Otestuje zda je specifikovany soubor includovatelny (pripadne vrati vsechny definovane cesty pro include)
   *
   * @param  string  $fileName
   * @param  bool    $returnPaths
   * @return bool|array
   */
  public static function isIncludable($fileName, $returnPaths = false)
  {
    $includePaths = explode(PATH_SEPARATOR, ini_get('include_path'));

    foreach ($includePaths as $path) {
      $include = $path . DIRECTORY_SEPARATOR . $fileName;
      if (is_file($include) && is_readable($include)) {
        if ($returnPaths == true) {
          $includablePaths[] = $path;
        } else {
          return true;
        }
      }
    }

    return (isset($includablePaths) && $returnPaths == true) ? $includablePaths : false;
  }

  /**
   * Zda system na kterem bezi skript je Win typu
   *
   * @return bool
   */
  public static function isWin()
  {
    return substr(PHP_OS, 0, 3) == 'WIN';
  }

  /**
   * Zda system na kterem bezi skript je Unix typu
   *
   * @return bool
   */
  public static function isUnix()
  {
    return substr(PHP_OS, 0, 3) != 'WIN';
  }

  /**
   * Jakeho typu je hostitelsky OS
   *
   * @return string
   */
  public static function getOs()
  {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      return 'Windows';
    } else {
      return 'Unix';
    }
  }
}
