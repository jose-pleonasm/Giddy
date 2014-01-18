<?php

/**
 * Jednoducha implementace List
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_Collections
 * @version    $Id: ArrayList.php, 2009-10-30 11:45 $
 */

Base::import('Collections/AbstractList');

/**
 * Zakladni List imitujici indexovane pole
 *
 * @package    Giddy_Collections
 * @author     Josef Hrabec
 * @version    Release: 0.4
 * @since      Rozhrani je pristupne od verze 0.1
 */
class ArrayList extends AbstractList
{
  /**
   * Inicializace
   *
   * Pokud je konstruktoru predano pole,
   * je vlozeno do seznamu
   *
   * @uses   is_array()
   * @uses   ArrayList::addAll()
   * @param  array  $items  elementy ke vlozeni
   */
  public function __construct ($items = NULL)
  {
    if (is_array($items)) {
      $this->addAll($items);
    }
  }

  /**
   * Pridani objektu
   *
   * @uses   isset()
   * @uses   array()
   * @uses   count()
   * @uses   array_splice()
   * @uses   AbstractList::$base
   * @uses   AbstractList::$data
   * @param  integer  $index  index
   * @param  mixed    $item   element ke vlozeni
   * @return boolean  true pokud pridani posunulo kolekci, jinak NULL
   * @throws IndexOutOfBoundsException
   */
  public function add ($index, $item = NULL)
  {
    if ($item === NULL) {
      if (count($this->data) == 0) {
        $this->data[$this->base] = $index;
      } else {
        $this->data[] = $index;
      }

    } else {
      if ($index < $this->base || $index > count($this->data)) {
        throw new IndexOutOfBoundsException();
      }
      //pokud jiz pod timto Index je ulozen objekt,
      //musi se cele pole za timto Index posunout
      if (isset($this->data[$index])) {
        array_splice($this->data, (int) $index, 0, array($item));
        return true;

      } else {
        $this->data[$index] = $item;
      }
    }
  }

  /**
   * Vlozeni vsech objektu
   *
   * @uses   isset()
   * @uses   count()
   * @uses   array_merge()
   * @uses   array_splice()
   * @uses   AbstractList::$base
   * @uses   AbstractList::$data
   * @uses   AbstractList::toArray()
   * @param  integer  $index  cislo urcujici od ktereho indexu zacit
   * @param  mixed    $items  elementy ke vlozeni (array nebo ArrayList objekt)
   * @return boolean  true pokud se zmenil index puvodnich hodnot pole
   * @throws NullPointerException
   * @throws IndexOutOfBoundsException
   */
  public function addAll ($index, $items = NULL)
  {
    if ($items === NULL) {
      $items = $index;
      if ($items === NULL) {
        throw new NullPointerException();
      }
      if ($items instanceof AbstractList) {
        $items = $items->toArray();
      }
      $this->data = array_merge($this->data, $items);

    } else {
      if ($items === NULL) {
        throw new NullPointerException();
      }
      if ($items instanceof ArrayList) {
        $items = $items->toArray();
      }
      if ($index < $this->base || $index > count($this->data)) {
        throw new IndexOutOfBoundsException();
      }
      if (isset($this->data[$index])) {
        array_splice($this->data, (int) $index, 0, $items);
        return true;

      } else {
        $this->data = array_merge($this->data, $items);
      }
    }
  }

  /**
   * Ziskani objektu dle jeho klice
   *
   * @uses   isset()
   * @uses   AbstractList::$data
   * @param  integer  $index  index elemntu k navraceni
   * @return mixed    element na specifikovane pozici seznamu
   * @throws IndexOutOfBoundsException
   */
  public function get ($index)
  {
    if (isset($this->data[$index])) {
      return $this->data[$index];
    } else {
      throw new IndexOutOfBoundsException();
    }
  }

  /**
   * Zmeni obsah pole pod danym klicem
   *
   * @uses   isset()
   * @uses   AbstractList::$data
   * @param  integer  $index  index elementu
   * @param  mixed    $item   element, ktery se ma ulozit
   * @return mixed    puvodni hodnota
   * @throws IndexOutOfBoundsException
   */
  public function set ($index, $item)
  {
    if (isset($this->data[$index])) {
      $buffer = $this->data[$index];
      $this->data[$index] = $item;
    } else {
      throw new IndexOutOfBoundsException();
    }
    return $buffer;
  }

  /**
   * Smaze objekt z pole podle klice
   *
   * @uses   is_int()
   * @uses   isset()
   * @uses   array_splice()
   * @uses   AbstractList::$data
   * @uses   ArrayList::indexOf()
   * @param  mixed    $subject  index elementu, nebo element, ktery ma byt smazan
   * @return mixed    pokud byl specifikovan index - puvodni hodnota, pokud objekt - true pokud element existoval
   * @throws IndexOutOfBoundsException
   */
  public function remove ($subject)
  {
    if (is_int($subject)) {
      $index = $subject;
      if (isset($this->data[$index])) {
        $buffer = $this->data[$index];
      } else {
        throw new IndexOutOfBoundsException();
      }
      array_splice($this->data, $index, 1);
      return $buffer;

    } else {
      $index = $this->indexOf($subject);
      if ($index !== -1) {
        array_splice($this->data, $index, 1);
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * Odstrani elementy od indexu $fromIndex (vcetne) po $toIndex (nezahrnuty)
   *
   * // protected
   *
   * @uses   count()
   * @uses   array_splice()
   * @uses   AbstractList::$base
   * @uses   AbstractList::$data
   * @param  integer  $fromIndex  spodni index - vcetne
   * @param  integer  $toIndex    horni index - nezahrnuty
   * @return void
   * @throws IndexOutOfBoundsException
   */
  public function removeRange ($fromIndex, $toIndex)
  {
    if ($fromIndex < $this->base || $fromIndex >= count($this->data)
        || $toIndex > count($this->data) || $toIndex < $fromIndex) {
      throw new IndexOutOfBoundsException();
    }
    array_splice($this->data, $fromIndex, ($toIndex - $fromIndex));
  }

  /**
   * Vrati cast pole
   *
   * @uses   count()
   * @uses   array_slice()
   * @uses   AbstractList::$base
   * @uses   AbstractList::$data
   * @uses   AbstractList::__construct()
   * @param  integer  $formIndex  spodni mez - vcetne
   * @param  integer  $toIndex    horni mez - nezahrnuty
   * @return ArrayList
   * @throws IndexOutOfBoundsException
   */
  public function subList ($fromIndex, $toIndex)
  {
    if ($fromIndex < $this->base || $fromIndex >= count($this->data)
        || $toIndex > count($this->data) || $toIndex < $fromIndex) {
      throw new IndexOutOfBoundsException();
    }
    return new ArrayList(array_slice($this->data, $fromIndex, ($toIndex - $fromIndex)));
  }

  /**
   * Hleda element v poli
   *
   * @uses   in_array()
   * @uses   AbstractList::$data
   * @param  mixed   $item  hledany element
   * @return boolean true - pokud seznam element obsahuje
   */
  public function contains ($item)
  {
    return in_array($item, $this->data);
  }

  /**
   * Vrati index prvni nalezeneho specifikovaneho elementu
   *
   * @uses   array_search()
   * @uses   AbstractList::$data
   * @param  mixed   $item  hledany element
   * @return integer index elementu, pokud element neobsahuje tak -1
   */
  public function indexOf ($item)
  {
    $index = array_search($item, $this->data);
    return $index === false ? -1 : $index;
  }

  /**
   * Vrati index posledniho nalezeneho specifikovaneho elementu
   *
   * @uses   empty()
   * @uses   count()
   * @uses   array_keys()
   * @uses   AbstractList::$data
   * @param  mixed   $item  hledany element
   * @return integer index elementu, pokud element neobsahuje tak -1
   */
  public function lastIndexOf ($item)
  {
    $indexis = array_keys($this->data, $item);
    if (empty($indexis)) {
      return -1;
    }
    return $indexis[count($indexis) - 1];
  }

  /**
   * Vrati iterator
   *
   * @return ListIterator  iterator pro prochazeni pole
   */
  public function getIterator ()
  {
    Base::import('Collections_ListIterator');
    return new ListIterator($this->data);
  }
}
