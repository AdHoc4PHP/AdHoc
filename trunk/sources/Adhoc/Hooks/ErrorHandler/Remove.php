<?php

namespace Adhoc\Hooks\ErrorHandler;

/**
 * @author prometheus
 */
class Remove extends \Adhoc\Hook
{
	/**
	 * Removes an existsing error handler from the parent ErrorHandler trap.
	 *
	 * @param string $alias
	 * @return bool FALSE
	 */
	public function __invoke()
	{
		$alias = func_get_arg(0);
		
		$handlers =& $this->trap->GetList();
		
		if (!is_string($alias))
		{
			throw new \InvalidArgumentException('Argument "$alias" must be a type of string.');
		}
		if (!array_key_exists($alias, $handlers))
		{
			return;
		}
		
		unset($handlers[$alias]);
		
		return false;
	}
}