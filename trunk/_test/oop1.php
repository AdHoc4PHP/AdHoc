<?php

class A
{
	protected $a;
	protected $b;
	public function __construct(A $a = NULL, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}
	public function set(A $a = NULL)
	{
		if (!is_null($a))
		{
			$a->chg($this);
		}
	}
	protected function chg(A $a)
	{
		$this->a = $a;
	}
}

$foo = new A(NULL, 'foo');
$bar = new A(NULL, 'bar');
$foo->set($bar);

var_dump($bar);