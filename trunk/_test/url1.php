<?php

$url = array();
$url[] = 'pdo:///mysql/opt1=val1;opt2=val2;opt3=val3';
$url[] = 'pdo://hostname/path:1337';
$url[] = 'pdo://hostname:1337/path';
$url[] = 'pdo://@hostname/path'; // X
$url[] = 'pdo://username@hostname/path';
$url[] = 'pdo://username:password@hostname/path';
$url[] = 'pdo://:password@hostname/path'; // X
$url[] = 'pdo://username:@hostname/path';
$url[] = 'pdo://username:password@hostname:1337/path:1337/path#frag';
$url[] = 'pdo://username:password@hostname:1337/path:1337/path?query#frag';
$url[] = 'pdo:///path/path#frag';


$re = '%^(?P<schema>[a-zA-Z0-9_][-a-zA-Z0-9_]*?)://(((?P<user1>[^:@]+?)@)|((?P<user2>[^:@]+?):(?P<pass>[^:]*?)@)|)((?P<host1>[^:@/]*?)|(?P<host2>[^:@/]*?):(?P<port>\d+?))(?P<path>/[^\?#]*?)(\?(?P<query>.*?))?(#(?P<fragment>.*))?$%';
foreach ($url as $u)
{
	var_dump($u);
	preg_match($re, $u, $m);
	$d = array();
	foreach ($m as $k=>$v)
	{
		if (empty($v)) continue;
		if ($k === 'user1' or $k === 'user2')
		{
			$d['user'] = $v;
			continue;
		}
		if ($k === 'host1' or $k === 'host2')
		{
			$d['host'] = $v;
			continue;
		}
		if (!is_int($k)) $d[$k] = $v;
	}
	unset($d['user1'], $d['user2']);
	var_dump($d);
}
