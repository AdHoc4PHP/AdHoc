<?php

$re = '%^(?P<rep>HELLO\s+)(.*)$%is';
$str = 'Hello World!';

echo preg_replace($re, '\\g{rep}Man!', $str);