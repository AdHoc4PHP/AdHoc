<?php

function foo($cb)
{
	var_dump($cb);
	var_dump(is_object($cb) && $cb instanceof Closure);
}

foo(function($a, $b){});