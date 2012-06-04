<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class Remove extends \Adhoc\Hook
{
	/**
	 * Removes an existsing data from the parent Registry trap.
	 *
	 * @param string $key
	 * @return bool FALSE
	 */
	public function __invoke()
	{
		$key = func_get_arg(0);
		
		$data =& $this->trap->GetList();
		
		if (!is_string($key))
		{
			throw new \InvalidArgumentException('Argument "$key" must be a type of string.');
		}
		if (!array_key_exists($alias, $data))
		{
			return;
		}
		
		unset($data[$key]);
		
		return false;
	}
}