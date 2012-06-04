<?php

$re = '#(?<!\\\\)\{(?<!\\\\)\{(.+?)(?<!\\\\)\}(?<!\\\\)\}#s';

$ptn = array();
$ptn[] = '{{}}';
$ptn[] = '{{foo}}';
$ptn[] = '{{foo';
$ptn[] = '{{foo\}\}}}';
$ptn[] = '{\{\foo\}\}';

foreach ($ptn as $p)
{
	var_dump(preg_match($re, $p, $m));
	var_dump($m);
	echo '---';
	var_dump(preg_replace($re, '`$1`', $p));
	echo '---';
}
