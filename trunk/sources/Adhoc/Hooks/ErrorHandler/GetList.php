<?php

namespace Adhoc\Hooks\ErrorHandler;

/**
 * @author prometheus
 */
class GetList extends \Adhoc\Hook
{
	protected $handlers = array();
	
	/**
	 * Returns all error handler object assigned to the parent ErrorHandler trap.
	 *
	 * @return array Array of objects implements {@link \Adhoc\Interfaces\IErrorHandler IErrorHandler} interface.
	 */
	public function &__invoke()
	{
		return $this->handlers;
	}
}