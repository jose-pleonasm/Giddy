<?php

/**
 * Nastroje pro praci s HTML
 * 
 * Experimentalni modul
 *
 * @link       http://jose.cz/GiddyFramework
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: HtmlTools.php, 2010-08-12 13:54 $
 */

trigger_error('This modul is experimental!', E_USER_NOTICE);

class HtmlTools
{
  protected static $pairElements = array(
    'div',
    'p',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'ul',
    'ol',
    'li',
    'dl',
    'dt',
    'dd',
    'address',
    'pre',
    'blockquote',
    'ins',
    'del',
    'a',
    'span',
    'bdo',
    'em',
    'strong',
    'dfn',
    'code',
    'samp',
    'kbd',
    'var',
    'cite',
    'abbr',
    'acronym',
    'q',
    'sub',
    'sup',
    'tt',
    'i',
    'b',
    'big',
    'small',
    'object',
    'form',
    'label',
    'select',
    'textarea',
    'fieldset',
    'legend',
    'table',
    'caption',
    'thead',
    'tbody',
    'tfoot',
    'colgroup',
    'col',
    'tr',
    'th',
    'td',
    'script',
    'noscript'
  );

  public static function includesTag ($string)
  {
    $els = implode('|', self::$pairElements);
    preg_match('/<(' . $els . ')( |>)/i', $string, $matches);
    return empty($matches) ? false : $matches[1];
  }

  public static function getIncElements ($string)
  {
    $els = implode('|', self::$pairElements);
    preg_match_all('/<(' . $els . ')( |>)/i', $string, $matches);
    return empty($matches[1]) ? NULL : $matches[1];
  }
}
