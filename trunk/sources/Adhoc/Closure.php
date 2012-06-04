<?php

namespace Adhoc;

/**
 * Makes a PHP 5.4-like Closure that supports context binding.
 *
 * <p>Example:</p>
 * <code>&lt;?php
 * class Foo
 * {
 * 	public function format($what)
 * 	{
 * 		return 'Hello, '.$what.'!';
 * 	}
 * }
 * 
 * $fn = \Adhoc\Closure::by(function ($name)
 * {
 * 	$_this = \Adhoc\Closure::getBound(func_get_args());
 * 	print $_this->format($name);
 * });
 * 
 * $fn->bindTo(new Foo());
 * $fn('Bar'); // prints "Hello, Bar!";</code>
 *
 * <p>This class makes you able to use the concept of PHP 5.4's Closure
 * contexts. In PHP 5.4 you may unwrap the wrapped Closures and getBound()
 * calls, then replace $_this's to $this simply.</p>
 */
class Closure
{
	/**
	 * @var \Closure
	 */
	protected $closure;
	
	/**
	 * @var object
	 */
	protected $bound;
	
	/**
	 * Creates this wrapper for passed closure
	 */
	public function __construct(\Closure $closure)
	{
		$this->closure = $closure;
	}
	
	/**
	 * Creates this wrapper for passed closure
	 *
	 * @return \Adhoc\Closure
	 */
	public static function by(\Closure $closure)
	{
		return new self($closure);
	}
	
	/**
	 * Returns the context which bound to this wrapper based on arguments list.
	 *
	 * @return object
	 */
	public static function getBound(array $args)
	{
		return array_pop($args);
	}
	
	/**
	 * Bounds the passed object as the context to this wrapper.
	 */
	public function bindTo($object)
	{
		if (!is_object($object))
		{
			throw new \InvalidArgumentException('Argument 1 passed to '.__METHOD__.' must be an object, '.gettype($object).' given.');
		}
		
		$this->bound = $object;
	}
	
	/**
	 * Calls the wrapped Closure and passes the context as the last argument.
	 * If no context bound with {@link bindTo} before calling this wrapper, extra
	 * argument shouldn't populated, calling {@link \Adhoc\Closure::getBound} will
	 * problematic.
	 *
	 * @return mixed The return of wrapped Closure.
	 */
	public function __invoke()
	{
		$args = func_get_args();
		if (isset($this->bound)) $args[] = $this->bound;
		return call_user_func_array($this->closure, $args);
	}
}
