<?php

$a = array('foo'=>1, 'bar'=>2);

$b = array('foobar'=>3, 'foo'=>4) + $a;

var_dump($b);