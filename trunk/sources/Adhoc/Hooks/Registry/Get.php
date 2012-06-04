<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class Get extends \Adhoc\Hook
{
	/**
	 * Gets a dataitem from parent Registry trap, or returns null if not exists.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function &__invoke()
	{
		$key = func_get_arg(0);
		
		$data =& $this->trap->GetList();
		
		if (!is_string($key))
		{
			throw new \InvalidArgumentException('Argument "$key" must be a type of string.');
		}
		if (!array_key_exists($key, $data))
		{
			return;
		}
		
		return $data[$key];
	}
}