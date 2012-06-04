<?php

function foo()
{
	$r = 1;
	$cb = function () use (&$r)
	{
		$r = 2;
	};
	$cb();
	var_dump($r);
}

foo();