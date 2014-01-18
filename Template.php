<?php

Base::import('Common');

class Template extends Common
{
  const CONF_FILE_NAME = 'template.conf';

  protected $paths;

  protected $settings;

  protected $helpers = array();

  protected $vars;

  protected $protocol;

  public function __construct($paths = NULL, $settings = NULL)
  {
    $this->vars = array();
    $this->paths = array();
    $this->settings = array();

    $this->paths['template']   = isset($paths['template']) ? $paths['template'] : '';
    $this->paths['compile']    = isset($paths['compile']) ? $paths['compile'] : '';
    $this->paths['dictionary'] = isset($paths['dictionary']) ? $paths['dictionary'] : '';
    $this->paths['config']     = isset($paths['config']) ? $paths['config'] : '';

    $this->settings['prefix']       = isset($settings['prefix']) ? $settings['prefix'] : '';
    $this->settings['lang']         = isset($settings['lang']) ? $settings['lang'] : 'cs';
    $this->settings['encoding']     = isset($settings['encoding']) ? $settings['encoding'] : 'UTF-8';
    $this->settings['content_type'] = isset($settings['content_type']) ? $settings['content_type'] : 'text/html';

    $file = $this->paths['config'] . self::CONF_FILE_NAME;
    $this->loadConfig($file);
  }

  public function loadConfig($file)
  {
    if (!is_file($file)) {
      return false;
    }

    $fh = fopen($file, 'r');
    while (!feof($fh)) {
      $line = fgets($fh);

      $pair = preg_split('/[[:blank:]]+/', $line, 2);
      $name = trim($pair[0]);
      if (!isset($pair[1])) {
        continue;
      }
      $value = trim($pair[1]);
      if (strpos($name, '#') === 0) {
        continue;
      }
      $comPos = strpos($value, '#');
      if ($comPos > 0) {
        $value = substr($value, 0, $comPos);
      }

      $this->assign(strtoupper($name), $value);
    }
    fclose($fh);

    return true;
  }

  public function addHelpersFile($file)
  {
    $this->helpers[] = $file;
  }

  public function assign($name, $value)
  {
    $this->vars[$name] = $value;
  }

  public function render($template)
  {
    $file = $this->paths['template'] . $this->settings['prefix'] . $template;
    if (!is_file($file)) {
      throw new InvalidArgumentException("Template \"{$file}\" does not exist");
    }

    if (count($this->helpers) > 0) {
      foreach ($this->helpers as $helpersFile) {
        if (is_file($helpersFile)) {
          include $helpersFile;
        } else {
          $this->protocol[] = "ERROR: Helpers file \"{$helpersFile}\" does not exist";
        }
      }
    }

    extract($this->vars);
    ob_start();
    include $file;
    ob_end_flush();
  }
}
