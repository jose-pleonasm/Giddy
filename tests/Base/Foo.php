<?php

cLaSs
  Foo
 {
  const REVISION = 1.1;

  public static function getInstance()
  {
    return nEw self();
  }

  public function __toString()
  {
    return 'Foo';
  }
}


if (!Foo::REVISION) {
  trigger_error('Invalid version', E_USER_ERROR);
}

class SuperFoo extenDs 
 Foo
{
  public function __toString()
  {
    return 'SuperFoo';
  }
}
