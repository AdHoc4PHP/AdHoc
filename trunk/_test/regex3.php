<?php

$reFind = '%^(\s*?SELECT\s+?)(((ALL|DISTINCT|DISTINCTROW)\s+?)?((HIGH_PRIORITY)\s+?)?((STRAIGHT_JOIN)\s+?)?((SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_BUFFER_RESULT)\s+?)?((SQL_CACHE|SQL_NO_CACHE)\s+?)?)(.*)$%is';
$query = 'SELECT ALL HIGH_PRIORITY STRAIGHT_JOIN SQL_SMALL_RESULT SQL_NO_CACHE * FROM table';

preg_match($reFind, $query, $m);
var_dump($m);

var_dump(preg_replace($reFind, '\\1\\2SQL_CALC_FOUND_ROWS ${13}', $query));
