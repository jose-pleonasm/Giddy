<?php

Base::import('Collections_Comparator');

class DateComparator extends Common implements Comparator
{
  public function compare ($o1, $o2)
  {
    return $o1->compareTo($o2);
  }
}
