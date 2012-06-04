<?php

class MyClosure
{
	protected $closure;
	protected $bound;
	
	public function __construct(\Closure $closure)
	{
		$this->closure = $closure;
	}
	public static function by(\Closure $closure)
	{
		return new self($closure);
	}
	
	public static function getBound(array $args)
	{
		return array_pop($args);
	}
	
	public function bindTo($object)
	{
		if (!is_object($object))
		{
			throw new \InvalidArgumentException('Argument 1 passed to '.__METHOD__.' must be an object, '.gettype($object).' given.');
		}
		$this->bound = $object;
	}
	
	public function __invoke()
	{
		$args = func_get_args();
		$args[] = $this->bound;
		return call_user_func_array($this->closure, $args);
	}
}

class Foo
{
	public function format($what)
	{
		return 'Hello, '.$what.'!';
	}
}

$cc = MyClosure::by(function($name)
{
	$_this = MyClosure::getBound(func_get_args());
	print $_this->format($name);
});
$cc->bindTo(new Foo());
$cc('Bar');