<?php

class Foo
{
	protected $id = 0;
	public function __construct($id)
	{
		$o = set_error_handler(array($this, 'h'));
		register_shutdown_function('Foo::shf');
		Foo::r($this);
		$this->id = $id;
	}
	
	public function done()
	{
		restore_error_handler();
		Foo::u($this);
	}
	
	public function __destruct()
	{
		var_dump(__METHOD__);
		var_dump($this->id);
	}
	
	public function h($errno, $errstr, $errfile, $errline)
	{
		var_dump($this->id.': '.$errstr);
		return true;
	}
	
	public function s()
	{
		ob_start();
		var_dump(__METHOD__.' '.$this->id);
		$s = ob_get_contents();
		ob_end_clean();
		print $s;
	}
	
	public static function shf()
	{
		static $r = false;
		
		if ($r) return;
		
		$r = true;
		
		$last = count(self::$ol) - 1;
		for ($i = $last; $i >= 0; $i--)
		{
			self::$ol[$i]->s();
		}
	}
	
	protected static $ol = array();
	
	public static function r($o)
	{
		self::$ol[] = $o;
	}
	
	public static function u($o)
	{
		$ol =& self::$ol;
		unset($ol[array_search($o, $ol)]);
	}
}

class FooBar
{
	public function __destruct()
	{
		var_dump(__METHOD__);
	}
}

$foo = array();
$foo[1] = new Foo(1);
$foo[2] = new Foo(2);
$foo[3] = new Foo(3);
//$fb = new FooBar();
trigger_error('test');
//Bar::destroy($foo[3]);
$foo[3]->done();
unset($foo[3]);
trigger_error('test');
//Bar::destroy($foo[2]);
$foo[2]->done();
unset($foo[2]);
trigger_error('test');
//Bar::destroy($foo[1]);
$foo[1]->done();
unset($foo[1]);
trigger_error('test');
