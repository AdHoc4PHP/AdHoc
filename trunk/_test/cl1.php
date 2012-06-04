<?php

class C
{
	protected $text = '';
	public function __construct($text)
	{
		$this->text = $text;
	}
	public function __invoke(C $c=NULL)
	{
		echo $this->text.(is_null($c)? '' : '.');
		return $c;
	}
	public static function create($text)
	{
		return new static($text);
	}
}

class C2 extends C
{
	public function __invoke(C $c=NULL)
	{
		echo $this->text.'2'.(is_null($c)? '' : '.');
		return $c;
	}
}

$a = C::create('foo');
$a = $a(C2::create('bar'));
$a();