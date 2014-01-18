<?php

/**
 * Giddy Framework
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: GiddyFramework.php, 2011-08-12 13:27 $
 */

/**
 * Giddy Framework
 *
 * Informace o verzi atd.
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.9.4
 * @since      Trida je pristupna od verze 0.1
 * @static
 */
final class GiddyFramework
{
  const NAME = 'Giddy Framework';

  const WEB = 'http://jose.cz/GiddyFramework';

  const VERSION = '1.0';

  const REVISION = '2011-08-12 13:27';

  /**
   * Static class - nemuze byt objektem
   */
  final public function __construct ()
  {
    throw new LogicException('Cannot instantiate static class ' . get_class($this));
  }

  /**
   * Porovnava aktualni verzi frameworku se specifikovanou.
   *
   * @uses   Framework::VERSION
   * @uses   version_compare()
   * @param  string
   * @return int
   */
  public static function compareVersion ($version)
  {
    return version_compare($version, self::VERSION);
  }

  /**
   * Giddy Framework promotion
   *
   * @uses   Framework::WEB
   * @uses   Framework::NAME
   * @return string
   */
  public static function getPromo ()
  {
    return '<a href="' . self::WEB . '" title="Giddy Framework - jednoduchý PHP Framework">'
         . self::NAME
         . '</a>';
  }

  /**
   * Giddy Framework promotion
   *
   * @uses   Framework::getPromo()
   * @return void
   */
  public static function promo ()
  {
    echo self::getPromo();
  }
}
