<?php

namespace Adhoc\Db\SQL\Select\VendorHelp;

class MysqlHelper extends VendorHelp
{
	/**
	 * This will insert SQL_CALC_FOUND_ROWS option for passed SELECT statement to
	 * the proper place within if it's not present. If SQL_CALC_FOUND_ROWS present
	 * in the passed query, returns the query without any change.
	 *
	 * @param string $query
	 * @return string
	 */
	public function query($query)
	{
		$reTest = '%^\s*?SELECT.+?SQL_CALC_FOUND_ROWS%is';
		$reFind = '%^(\s*?SELECT\s+?)(((ALL|DISTINCT|DISTINCTROW)\s+?)?((HIGH_PRIORITY)\s+?)?((STRAIGHT_JOIN)\s+?)?((SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_BUFFER_RESULT)\s+?)?((SQL_CACHE|SQL_NO_CACHE)\s+?)?)(.*)$%is';
		if (!preg_match($reTest, $query))
		{
			$query = preg_replace($reFind, '$1$2SQL_CALC_FOUND_ROWS ${13}');
		}
		return $query;
	}
	
	/**
	 * This will return 'SELECT FOUND_ROWS()'.
	 *
	 * @param string $query Unused in this implementation!
	 * @return string
	 */
	public function count($query)
	{
		return 'SELECT FOUND_ROWS()';
	}
}