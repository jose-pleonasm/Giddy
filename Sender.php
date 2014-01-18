<?php

/**
 * Odesilatel pozadavku
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy
 * @version    $Id: Sender.php, 2010-01-22 13:55 $
 */

/**
 * Sender
 *
 * @package    Giddy
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Trida je pristupna od verze 0.1
 */
class Sender
{
  protected static $delimiter = "\r\n";

  protected $protocol = 'HTTP/1.0';

  /**
   * Adresa ciloveho serveru
   *
   * @var string
   */
  protected $server;

  /**
   * Umisteni cilu
   *
   * @var string
   */
  protected $path;

  /**
   * Hlavicky odpovedi
   *
   * @var array
   */
  protected $response;

  /**
   * Telo odpovedi
   *
   * @var string
   */
  protected $body;

  /**
   * Overi platnost adresy serveru
   *
   * @param  string $server
   * @return bool
   */
  protected static function isValidServer($server)
  {
    if ($server == 'localhost') {
      return true;
    }
    return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-.]+\.[a-zA-Z]{2,6}$/i', $server);
  }

  /**
   * Vytvori POST dotaz
   *
   * @param  array $data
   * @return string
   */
  protected static function createPostQuery($data)
  {
    $body = '';
    foreach ($data as $name => $value) {
      if (!empty($body)) {
        $body .= '&';
      }
      $body .= $name . '=' . (string) $value;
    }
    return $body;
  }

  /**
   * Zda je Sender pripraven k odeslani pozadavku
   *
   * @return bool
   */
  protected function isReady()
  {
    return !empty($this->server);
  }

  /**
   * Vrati sestavy HTTP pozadavek
   *
   * @param  string $body
   * @return string
   */
  protected function getQuest($body)
  {
    $quest  = "POST " . $this->path . " " . $this->protocol . self::$delimiter;
    $quest .= "Host: " . $this->server . self::$delimiter;
    $quest .= "Content-Length: " . strlen($body) . self::$delimiter;
    $quest .= "Content-Type: application/x-www-form-urlencoded" . self::$delimiter;
    $quest .= self::$delimiter;
    $quest .= $body . self::$delimiter;
    $quest .= self::$delimiter;
    return $quest;
  }

  /**
   * Nastaveni zakladnich parametru
   *
   * @param  string  $server
   * @param  string  $path
   * @return void
   * @throws InvalidArgumentException
   */
  public function __construct($server = NULL, $path = NULL)
  {
    if (isset($server)) {
      $pos = strpos($server, '/');
      if ($pos) {
        $buffer = $server;
        $server = substr($buffer, 0, $pos);
        $path   = substr($buffer, $pos);
      }

      if (!self::isValidServer($server)) {
        throw new InvalidArgumentException($server . ' is not valid server');
      }
      $this->server = $server;
    }
    if (isset($path)) {
      $this->path = $path;
    }
  }

  /**
   * Nastavy server
   *
   * @param  string  $server
   * @return void
   * @throws InvalidArgumentException
   */
  public function setServer($server)
  {
    if (!self::isValidServer($server)) {
      throw new InvalidArgumentException($server . ' is not valid server');
    }
    $this->server = $server;
  }

  /**
   * Nastavy umisteni
   *
   * @param  string  $path
   * @return void
   */
  public function setPath($path)
  {
    $this->path = $path;
  }

  /**
   * Nastavy server
   *
   * @return string
   * @throws InvalidArgumentException
   */
  public function getServer()
  {
    return $this->server;
  }

  /**
   * Vrati umisteni
   *
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Odesle pozadavek
   *
   * @param  array $data
   * @return bool
   * @throws InvalidStateException
   */
  public function send(array $data)
  {
    if (!self::isReady()) {
      throw new InvalidStateException('Sender not ready for send');
    }
    $query = self::createPostQuery($data);

    $response = '';
    $content  = '';
    $isBody   = false;
    $sock = fsockopen($this->server, 80);
    if (!$sock) {
      return false;
    }
    fputs($sock, $this->getQuest($query));
    while ($line = fgets($sock, 128)) {
      if ($line == "\r\n") {
        $isBody = true;
      }
      if ($isBody) {
        $content .= $line;
      } else {
        $response[] = $line;
      }
    }
    $this->response = $response;
    $this->body     = $content;

    return true;
  }

  /**
   * Vrati kod odpovedi
   *
   * @return int
   */
  public function getResponseCode()
  {
    $list = preg_split('/ +/', $this->response[0]);
    return (int) $list[1];
    //return (int) substr($this->response[0], 9, 3);
  }

  /**
   * Vrati hlavicky odpovedi
   *
   * @return array
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Vrati telo odpovedi
   *
   * @return string
   */
  public function getBody()
  {
    return trim($this->body);
  }
}
