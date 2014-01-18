<?php

/**
 * HTML obal pro vystup testu
 */
class PHPUnit_WebUI_HtmlOutput
{
  const CHARSET = "UTF-8";
  const LANG    = "cs";

  /**
   * Tiskne HTML hlavicku
   *
   * @param  string  $title  titulek webu
   * @return string  HTML header
   */
  public static function start ($title)
  {
    return "<?xml version=\"1.0\" encoding=\"".self::CHARSET."\"?>"
         . "\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">"
         . "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".self::LANG."\" lang=\"".self::LANG."\">"
         . "\n<head>"
         . "\n<meta http-equiv=\"content-type\" content=\"text/html; charset=".self::CHARSET."\" />"
         . "\n<meta http-equiv=\"content-language\" content=\"".self::LANG."\" />"
         . "\n<title>".$title."</title>"
         . "<script type=\"text/javascript\">"
         . "
var browser = {
  dom:(document.getElementById != null),
  ie:(document.all && parseInt(navigator.appVersion) >= 4),
  ns:(document.layers && parseInt(navigator.appVersion) >= 4)
}

function getLayer (name)
{
  if (browser.dom) return document.getElementById(name);
  else if (browser.ie) return document.all[name];
  else if (browser.ns) return document.layers[name];
}

function showDefectBlock (id)
{
  getLayer(id).style.background = '#f1f1f1';
}

function hideDefectBlock (id)
{
  getLayer(id).style.background = '#fff';
}
         "
         . "</script>"
         . "<style type=\"text/css\">"
         . "h1, h2, h3 { margin: 0 0 1em 0; font-size: 1em; }"
         . "h4 { margin: 0 0 0.4em 0; font-size: 0.9em; }"
         . "h3.defect { margin: 1.6em 0 0.4em 0; }"
         . "p { margin: 0 0 1em 0; font-size: 1em; }"
         . "pre { margin: 0 0 1em 0; font-size: 1em; }"
         . "ul { margin: 0 0 1em 0; padding: 0; list-style: none; }"
         . "li { margin: 0 0 0.8em 0; padding: 0 0 0 0.4em; }"
         . "a { color: #005a9c; text-decoration: none; }"
         . "hr { display: none; }"
         . "#totalResult { margin: 1.6em 0 0 0; }"
         . "#totalResult p { margin: 0 0 0.4em 0; }"
         . ".resultsLine { margin: 0 0 1.8em 0; white-space: pre; }"
         . ".failMsg { margin: 0 0 0.3em 0; white-space: pre; font-family: monospace; color: #aa0000; }"
         . "</style>"
         . "\n</head>"
         . "\n<body>"
         . "\n";
  }

  /**
   * Tiskne HTML paticku
   *
   * @return string  HTML footer
   */
  public static function finish ()
  {
    return "\n</body>"
         . "\n</html>";
  }
}

?>