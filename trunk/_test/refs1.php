<?php

interface IFoo
{
	public function __invoke();
}

class Foo implements IFoo
{
	protected $bar = 42;
	
	public function &__invoke()
	{
		return $this->bar;
	}
}

$foo = new Foo();
$bar = &$foo();
var_dump($bar);
$bar = 66;
var_dump($foo());