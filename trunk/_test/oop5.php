<?php

class Foo
{
	public function __invoke()
	{
		$args = func_get_args();
		var_dump(__METHOD__, $args);
	}
}

$foo = new Foo();
call_user_func($foo, 1, 2, 3);
call_user_func_array($foo, array(1, 2));