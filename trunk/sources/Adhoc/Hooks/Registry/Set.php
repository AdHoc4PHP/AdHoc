<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class Set extends \Adhoc\Hook
{
	/**
	 * Sets an existsing (or adds a new) dataitem for parent Registry trap.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return bool FALSE
	 */
	public function __invoke()
	{
		$key = func_get_arg(0);
		$value = func_get_arg(1);
		
		$data =& $this->trap->GetList();
		
		if (!is_string($key))
		{
			throw new \InvalidArgumentException('Argument "$key" must be a type of string.');
		}
		
		$data[$key] = $value;
		
		return false;
	}
}