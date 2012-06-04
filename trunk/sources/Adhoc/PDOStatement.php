<?php

namespace Adhoc\PDO;

/**
 * Description of PDOStatement
 *
 * @author prometheus
 */
class PDOStatement extends \PDOStatement
{
	public function fetchAll($mode=\PDO::FETCH_ASSOC)
	{
		$result = NULL;

		switch ($mode)
		{
			default:
			{
				$result = parent::fetchAll($mode);
			}
		}

		return $result;
	}
}
