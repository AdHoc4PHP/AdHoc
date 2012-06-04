<?php

namespace Adhoc\Db\SQL;

abstract class VendorHelp
{
	public function query($query)
	{
		return $query;
	}
	
	public function count($query)
	{
		$re = '%^(\s*?SELECT\s+?)(.*?)(\s+?(FROM|WHERE|ORDER\s+?BY|GROUP\s+?BY))?.*$%is';
		if (preg_match($re, $query))
		{
			$query = preg_replace($re, '$1count(*)$3');
		}
		
		return $query;
	}
}