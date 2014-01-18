<?php

/**
 * Zakladni vyjimky
 *
 * @link      https://github.com/jose-pleonasm/Giddy
 * @category  Giddy
 * @package   Giddy
 * @version   $Id: exceptions.php, 2011-03-29 16:16 $
 */

if (!class_exists('LogicException')) {
  /**
   * vyjímky předvídatelné již při návrhu programu
   */
  class LogicException extends Exception {}

  /**
   * chyba při volání funkce; funkce nenalezena; volání nepovoleno 
   */
  class BadFunctionCallException extends LogicException {}

  /**
   * totéž pro metody
   */
  class BadMethodCallException extends BadFunctionCallException {}

  /**
   * špatný argument předaný funkci
   */
  class InvalidArgumentException extends LogicException {}

  /**
   * index mimo rozsah pole či kolekce
   */
  class OutOfRangeException extends LogicException {}

  /**
   * hodnota překračuje povolenou délku
   */
  class LengthException extends LogicException {}

  /**
   * hodnota nespadá do požadované domény, rozsahu
   */
  class DomainException extends LogicException {}
}

if (!class_exists('RuntimeException')) {
  /**
   * vyjímky zjistitelné pouze za běhu programu
   */
  class RuntimeException extends Exception {}

  /**
   * přetečení bufferu či aritmetické operace; více dat než očekáváno
   */
  class OverflowException extends RuntimeException {}

  /**
   * podtečení bufferu či aritmetické operace; méně dat než očekáváno 
   */
  class UnderflowException extends RuntimeException {}

  /**
   * index mimo rozsah pole či kolekce
   */
  class OutOfBoundsException extends RuntimeException {}

  /**
   * hodnota nespadá do požadovaného rozsahu
   */
  class RangeException extends RuntimeException {}

  /**
   * neočekávaná hodnota (např. návratová hodnota funkce)
   */
  class UnexpectedValueException extends RuntimeException {}
}

if (!class_exists('IndexOutOfBoundsException')) {
  /**
   * index mimo rozsah pole či kolekce
   *
   * @package   Giddy
   */
  class IndexOutOfBoundsException extends OutOfBoundsException {}
}

if (!class_exists('NullPointerException')) {
  /**
   * pokud je specifikovana hodnota NULL
   *
   * @package   Giddy
   */
  class NullPointerException extends RangeException {}
}

if (!class_exists('ArgumentOutOfRangeException')) {
  /**
   * The exception that is thrown when the value of an argument is
   * outside the allowable range of values as defined by the invoked method.
   *
   * @package   Giddy
   */
  class ArgumentOutOfRangeException extends InvalidArgumentException {}
}

if (!class_exists('ClassCastException')) {
  /**
   * pokud je specifikovany objekt jineho typu nez je ocekavano
   *
   * @package   Giddy
   */
  class ClassCastException extends RuntimeException {}
}

if (!class_exists('InvalidStateException')) {
  /**
   * The exception that is thrown when a method call is invalid for the object's
   * current state, method has been invoked at an illegal or inappropriate time.
   *
   * @package   Giddy
   */
  class InvalidStateException extends RuntimeException {}
}

if (!class_exists('NotImplementedException')) {
  /**
   * The exception that is thrown when a requested method or operation is not implemented.
   *
   * @package   Giddy
   */
  class NotImplementedException extends LogicException {}
}

if (!class_exists('NotSupportedException')) {
  /**
   * The exception that is thrown when an invoked method is not supported. For scenarios where
   * it is sometimes possible to perform the requested operation, see InvalidStateException.
   *
   * @package   Giddy
   */
  class NotSupportedException extends LogicException {}
}

if (!class_exists('IOException')) {
  /**
   * The exception that is thrown when an I/O error occurs.
   *
   * @package   Giddy
   */
  class IOException extends RuntimeException {}
}

if (!class_exists('FileNotFoundException')) {
  /**
   * The exception that is thrown when accessing a file that does not exist on disk.
   *
   * @package   Giddy
   */
  class FileNotFoundException extends IOException {}
}

if (!class_exists('FileAlreadyExistsException')) {
  /**
   * The exception that is thrown when accessing a file that does not exist on disk.
   *
   * @package   Giddy
   */
  class FileAlreadyExistsException extends IOException {}
}

if (!class_exists('DirectoryNotFoundException')) {
  /**
   * The exception that is thrown when part of a file or directory cannot be found.
   *
   * @package   Giddy
   */
  class DirectoryNotFoundException extends IOException {}
}

if (!class_exists('EmptyDirectoryException')) {
  /**
   * Obsah slozky je prazdny
   *
   * @package   Giddy
   */
  class EmptyDirectoryException extends IOException {}
}


/**
 * Neplatna verze
 *
 * @package   Giddy_Backup
 */
class VersionMismatchException extends RuntimeException {}


/**
 * Pri chybe parsovani
 *
 * @package   Giddy_Backup
 */
class ParsingFailedException extends IOException
{
  /**
   * Chyby z parsovaciho nastroje
   *
   * @var array
   */
  protected $errors;

  /**
   * Constructor
   *
   * @uses   IOExceptio::__construct()
   * @uses   ParsingFailedExceptin::$errors
   * @param  string  $message
   * @param  int     $code
   * @param  array   $errors
   */
  public function __construct ($message = 'parsing failed', $code = 0, $errors = NULL)
  {
    parent::__construct($message, $code);
    $this->errors = $errors;
  }

  /**
   * Vrati seznam chyb parsovani XML dokumentu
   *
   * @uses   ParsingFailedExceptin::$errors
   * @return array
   */
  public function getErrors ()
  {
    return $this->errors;
  }

  /**
   * Vrati zpravu o chybach parsovani XML dokumentu
   *
   * @uses   LIBXML_ERR_WARNING
   * @uses   LIBXML_ERR_ERROR
   * @uses   LIBXML_ERR_FATAL
   * @uses   trim()
   * @uses   ParsingFailedExceptin::$errors
   * @return string
   */
  public function getErrorsAsString ()
  {
    return serialize($this->errors);
  }
}


/**
 * Pri chybe parsovani XML dokumentu
 *
 * @package   Giddy_Backup
 */
class XmlParsingFailedException extends ParsingFailedException
{

  /**
   * Constructor
   *
   * @uses   IOExceptio::__construct()
   * @uses   ParsingFailedExceptin::$errors
   * @param  string  $message
   * @param  int     $code
   * @param  array   $errors
   */
  public function __construct ($message = 'xml parsing failed', $code = 0, $errors = NULL)
  {
    parent::__construct($message, $code);
    $this->errors = $errors;
  }

  /**
   * Vrati zpravu o chybach parsovani XML dokumentu
   *
   * @uses   LIBXML_ERR_WARNING
   * @uses   LIBXML_ERR_ERROR
   * @uses   LIBXML_ERR_FATAL
   * @uses   trim()
   * @uses   ParsingFailedExceptin::$errors
   * @return string
   */
  public function getErrorsAsString ()
  {
    $return = "";
    $total = 0;
    foreach ($this->errors as $error) {
      $total++;
      $return .= "#$total ";
      switch ($error->level) {
        case LIBXML_ERR_WARNING:
          $return .= "Warning $error->code: ";
          break;
        case LIBXML_ERR_ERROR:
          $return .= "Error $error->code: ";
          break;
        case LIBXML_ERR_FATAL:
          $return .= "Fatal error $error->code: ";
          break;
      }
      $return .= trim($error->message)
               . " in file '$error->file'"
               . " on line $error->line"
               . ", column: $error->column";
      $return .= "\n";
    }
    return $return;
  }
}

/**
 * Vlastni vyjimka frameworku
 *
 * Muze slouzit jako zaklad pro dalsi vyjimky
 * kvuli pridane funkcnosti
 *
 * @package   Giddy
 * @author    Josef Hrabec
 * @version   Release: 0.4
 * @since     Trida je pristupna od verze 0.1
 */
class BaseException extends Exception
{
  /**
   * Vlastni (nadstavene) parametry objektu
   *
   * @var array
   * @since Parametr je pristupny od verze 0.1
   */
  private $vars; 

  /**
   * Nastavi vlastni (nadstaveny) parametr
   *
   * @uses   BaseException::$vars
   * @param  string  $name  nazev parametru
   * @param  mixed   $value hodnota parametru
   * @since  Metoda je pristupna od verze 1.3
   */
  final protected function setVar ($name, $value)
  {
    $buffer = $this->vars[$name];
    $this->vars[$name] = $value;
    return $buffer;
  }

  /**
   * Ziskani vlastniho (nadstaveneho) parametru
   *
   * @uses   BaseException::$vars
   * @param  string  $name  specifikace parametru
   * @return mixed   hodnota specifikovaneho parametru
   * @since  Metoda je pristupna od verze 0.1
   */
  final protected function getVar ($name)
  {
    if (isset($this->vars[$name])) {
      return $this->vars[$name];
    } else {
      return NULL;
    }
  }

  /**
   * Prevede vyjimku do vyjimky typu BaseException
   *
   * @uses   Exception::getMessage()
   * @uses   Exception::getCode()
   * @uses   Exception::getTrace()
   * @uses   BaseException::setVar()
   * @uses   get_object_vars()
   * @param  object  $e  vyjimka (objekt Exception nebo jejiho potomka)
   * @return object  BaseException
   * @since  Metoda je pristupna od verze 1.3
   */
  public static function assimilate (Exception $e)
  {
    $newE = new BaseException($e->getMessage(), $e->getCode());
    $newE->file = $e->getFile();
    $newE->line = $e->getLine();
    $newE->originalTrace = $e->getTrace();
    $vars = get_object_vars($e);
    foreach ($vars as $name => $value) {
      if ($name != 'message' && $name != 'code'
          && $name != 'file' && $name != 'line') {
        $newE->setVar($name, $value);
      }
    }
    return $newE;
  }

  /**
   * Osetreni volani nedefinovanych parametru objektu
   *
   * @param  string  $name  specifikace parametru
   * @return mixed   hodnota specifikovaneho parametru
   * @since  Metoda je pristupna od verze 1.3
   */
  public function __get ($name)
  {
    return $this->getVar($name);
  }
}

/**
 * Vyjimky nepovedeneho zavadeni nektere jednotky frameworku
 *
 * @package   Giddy
 * @author    Josef Hrabec
 * @version   Release: 0.9.4
 * @since     Trida je pristupna od verze 0.1
 */
class ImportException extends RuntimeException
{
  /**
   * Kod teto vyjimky
   *
   * @since Konstanta je pristupna od verze 0.7
   * @var mixed
   */
  const CODE = 0;

  /**
   * Vyraz (klasicky nazev pozadovane tridy)
   *
   * @var string
   */
  protected $expression;

  /**
   * Vyvolani vyjimky
   *
   * Definovani objektu
   *
   * @uses   BaseException::__construct()
   * @uses   ImportException::CODE
   * @uses   ImportException::$expression
   * @param  string  $message   zprava
   * @param  string  $className specifikace tridy
   */
  public function __construct($message, $expression = NULL)
  {
    parent::__construct($message, self::CODE);
    $this->expression = $expression;
  }

  /**
   * Vrati specifikovany vyraz
   *
   * @uses   ImportException::$expression
   * @return string specifikovany vyraz pro import
   */
  public function getExpression()
  {
    return $this->expression;
  }

  /**
   * Vrati specifikovanou tridu
   *
   * Kvuli zpetne kompatibilite
   *
   * @uses   ImportException::$expression
   * @return string pripadny nazev tridy
   */
  public function getClassName()
  {
    return $this->expression;
  }

  /**
   * Vrati zpravu i s informaci o specifikovane tride
   *
   * @uses   ImportException::$expression
   * @uses   ImportException::$message
   * @return string
   * @since  Metoda je pristupna od verze 0.7
   */
  public function getExtendedMessage()
  {
    return $this->message . ' (expression/class name: ' . $this->expression . ')';
  }
}
