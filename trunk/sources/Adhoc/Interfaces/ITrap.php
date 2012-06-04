<?php

namespace Adhoc\Interfaces;

interface ITrap
{
	/**
	 * Constructor
	 *
	 * Make this trap workable.
	 */
	public function __construct();
	
	/**
	 * Destructor
	 *
	 * Destroy all hooks in this trap.
	 */
	public function __destruct();
	
	/**
	 * Returns the name of this trap.
	 *
	 * @return string
	 */
	public function __toString();
	
	/**
	 * Registers a hook for this trap.
	 *
	 * @param \Adhoc\Interfaces\IHook $hook The hook itself.
	 * @return \Adhoc\Interfaces\ITrap This instance
	 */
	public function registerHook(\Adhoc\Interfaces\IHook $hook);
	
	/**
	 * Unregisters a hook from this trap.
	 *
	 * @param \Adhoc\Interfaces\IHook $hook The hook itself.
	 */
	public function unregisterHook(\Adhoc\Interfaces\IHook $hook);
	
	/**
	 * Executes the named hook (or the default hook if name not specified in $action).
	 *
	 * @param string $action
	 * @param array $arguments Arguments passed for hook being executed.
	 * @return mixed The hooks' returning value
	 */
	public function executeHook($action = '', $arguments = array());
	
	/**
	 * Checks if the named hook (or the default hook if name not specifid in $action)
	 * exists/registered.
	 *
	 * @return bool TRUE if exists, FALSE otherwise.
	 */
	public function isHooked($action = '');
	
	/**
	 * Walks with a callback through on this trap's hooks.
	 *
	 * @param callback $callback function (\Adhoc\Interfaces\IHook $hook)
	 * @param string|bool $action Name of the selected hook or false to disable selection
	 */
	public function walk($callback, $action = false);
	
	/**
	 * Implements that named hooks are callable as virtual methods.
	 *
	 * @return mixed The returning value of called hook.
	 */
	public function __call($method, $arguments);
	
	/**
	 * Implements executing the default hook in this trap by calling this instance
	 * like a function or method.
	 *
	 * WARNING: Use {@link php.net/func_get_args func_get_args} to access the passed
	 * arguments, YOU CANNOT owerride this method's argument list because PHP's
	 * interface rules! You may use PHPDoc too for easy access to params.
	 *
	 * @return mixed The returning value of default hook.
	 */
	public function __invoke();
}