<?php

namespace Adhoc;

abstract class Trap implements \Adhoc\Interfaces\ITrap
{
	protected $hooks = array();
	
	/**
	 * Constructor
	 *
	 * Make this trap workable.
	 */
	abstract public function __construct();
	
	/**
	 * Destructor
	 *
	 * Destroy all hooks in this trap.
	 */
	public function __destruct()
	{
		// properly unbound all object referencies used in this method
		foreach ($this->hooks as $name=>$hooks)
		{
			$hooksLength = count($hooks);
			for ($i = 0; $i < $hooksLength; $i++)
			{
				unset($this->hooks[$name][$i]);
			}
			
			unset($this->hooks[$name]);
		}
	}
	
	/**
	 * Returns the name of this trap.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return \Adhoc\Util::getClassName($this);
	}
	
	/**
	 * Registers a hook for this trap.
	 *
	 * @param \Adhoc\Interfaces\IHook $hook The hook itself.
	 * @return \Adhoc\Interfaces\ITrap This instance
	 * @throws \LogicException
	 */
	public function registerHook(\Adhoc\Interfaces\IHook $hook)
	{
		$name = (string)$hook;
		if (!isset($this->hooks[$name]))
		{
			$this->hooks[$name] = array();
		}
		
		if (in_array($hook, $this->hooks[$name]))
		{
			throw new \LogicException('Try to register an existing "'.get_class($hook).'" hook instance.');
		}
		
		array_unshift($this->hooks[$name], $hook);
		
		return $this;
	}
	
	/**
	 * Unregisters a hook from this trap.
	 *
	 * @param \Adhoc\Interfaces\IHook $hook The hook itself.
	 * @throws \LogicException
	 */
	public function unregisterHook(\Adhoc\Interfaces\IHook $hook)
	{
		$name = (string)$hook;
		if (!isset($this->hooks[$name]))
		{
			throw new \LogicException('Trying unregister in an empty hook ('.$name.').');
		}
		
		$id = array_search($hook, $this->hooks[$name]);
		if ($id === false)
		{
			throw new \LogicException('Trying unregister an unexistant hook instance ('.get_class($hook).').');
		}
		
		unset($this->hooks[$name][$id]);
		
		// reindexing
		$this->hooks[$name] = array_values($this->hooks[$name]);
	}
	
	/**
	 * Executes the named hook (or the default hook if name not specified in $action).
	 *
	 * @param string $action
	 * @param array $arguments Arguments passed for hook being executed.
	 * @return mixed The hooks' returning value
	 */
	public function executeHook($action = '', $arguments = array())
	{
		if (!isset($this->hooks[$action]))
		{
			return;
		}
		
		foreach ($this->hooks[$action] as $hook)
		{
			$result = call_user_func_array($hook, $arguments);
			if (!is_null($result)) break;
		}
		
		return $result;
	}
	
	/**
	 * Checks if the named hook (or the default hook if name not specifid in $action)
	 * exists/registered.
	 *
	 * @param string $action
	 * @return bool TRUE if exists, FALSE otherwise.
	 */
	public function isHooked($action = '')
	{
		if (isset($this->hooks[$action]) and count($this->hooks[$action]) > 0)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Walks with a callback through on this trap's hooks.
	 *
	 * @param callback $callback function (\Adhoc\Interfaces\IHook $hook)
	 * @param string|bool $action Name of the selected hook or false to disable selection
	 * @throws \InvalidArgumentException
	 */
	public function walk($callback, $action = false)
	{
		if (!is_callable($callback))
		{
			throw new \InvalidArgumentException('Argument $callback must be a callable and existing callback.');
		}
		if ($action !== false and !$this->isHooked($action))
		{
			return;
		}
		
		if (is_object($callback) and $callback instanceof Closure)
		{
			if ($action === false)
			{
				foreach ($this->hooks as $hooks)
				{
					foreach ($hooks as $hook)
					{
						$callback($hook);
					}
				}
			}
			else
			{
				foreach ($this->hooks[$action] as $hook)
				{
					$callback($hook);
				}
			}
		}
		else
		{
			if ($action === false)
			{
				foreach ($this->hooks as $hooks)
				{
					foreach ($hooks as $hook)
					{
						call_user_func($callback, $hook);
					}
				}
			}
			else
			{
				foreach ($this->hooks[$action] as $hook)
				{
					call_user_func($callback, $hook);
				}
			}
		}
	}
	
	/**
	 * Implements that named hooks are callable as virtual methods.
	 *
	 * @return mixed The returning value of called hook.
	 */
	public function __call($method, $arguments)
	{
		return $this->executeHook($method, $arguments);
	}
	
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
	public function __invoke()
	{
		$args = func_get_args();
		return $this->executeHook('', $args);
	}
}