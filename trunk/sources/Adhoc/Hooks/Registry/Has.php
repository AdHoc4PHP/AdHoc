<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class Has extends \Adhoc\Hook
{
	/**
	 * Returns true if parent Registry has a not null value for specified $key,
	 * false otherwise.
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
		return isset($data[$key]);
	}
}