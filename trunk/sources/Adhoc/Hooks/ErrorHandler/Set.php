<?php

namespace Adhoc\Hooks\ErrorHandler;

/**
 * @author prometheus
 */
class Set extends \Adhoc\Hook
{
	/**
	 * Sets an existsing error handler in the parent ErrorHandler trap.
	 *
	 * @param string $alias
	 * @param \Adhoc\Interfaces\IErrorHandler $handler
	 * @return bool FALSE
	 */
	public function __invoke()
	{
		$alias = func_get_arg(0);
		$handler = func_get_arg(1);
		
		$handlers =& $this->trap->GetList();
		
		if (!is_string($alias))
		{
			throw new \InvalidArgumentException('Argument "$alias" must be a type of string.');
		}
		if (!is_object($handler))
		{
			throw new \InvalidArgumentException('Argument "$handler" must be a type of object.');
		}
		if (!array_key_exists($alias, $handlers))
		{
			return;
		}
		if (!in_array('\Adhoc\Interfaces\IErrorHandler', class_implements(get_class($handler))))
		{
			throw new \InvalidArgumentException('Specified handler\'s class ('.get_class($handler).') not implements the \Adhoc\Interfaces\IErrorHandler interface.');
		}
		
		$handlers[$alias] = $handler;
		
		return false;
	}
}