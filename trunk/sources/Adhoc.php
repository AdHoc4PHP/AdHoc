<?php

class Adhoc
{
	protected static $traps = array();
	
	/**
	 * Chains a new system trap by its name.
	 *
	 * @param \Adhoc\Interfaces\ITrap $instance
	 * @throws \OutOfBoundsException
	 */
	public static function setTrap(\Adhoc\Interfaces\ITrap $instance)
	{
		$alias = (string)$instance;
		if (Adhoc::hasTrap($alias, $instance))
		{
			throw new \OutOfBoundsException('Instanced trap ('.get_class($instance).') already exists.');
		}
		if (!Adhoc::hasTrap($alias))
		{
			Adhoc::$traps[$alias] = array();
		}
		
		array_unshift(Adhoc::$traps[$alias], $instance);
	}
	
	/**
	 * Checks wether a trap alias is exists or not.
	 *
	 * @param string $alias
	 * @param \Adhoc\Interfaces\ITrap $instance Exactly checks for the passed instance's existing
	 * @return bool Returns true if alias or specified instance existed, false otherwise.
	 */
	public static function hasTrap($alias, \Adhoc\Interfaces\ITrap $instance = null)
	{
		$set = isset(Adhoc::$traps[$alias]);
		return ($set && (!$set or is_null($instance)? true : (bool)array_search($instance, Adhoc::$traps[$alias])));
	}
	
	/**
	 * Unsets a fully system trap or one existing trap that specified in $instance.
	 *
	 * @param string $alias
	 * @param \Adhoc\Interfaces\ITrap $instance Exactly checks for the passed instance's existing
	 * @throws \OutOfBoundsException
	 */
	public static function unsetTrap($alias, \Adhoc\Interfaces\ITrap $instance = null)
	{
		if (is_null($instance))
		{
			if (!Adhoc::hasTrap($alias))
			{
				throw new \OutOfBoundsException('Unexistant trap ('.$alias.') cannot be unset.');
			}
			
			$last = count(Adhoc::$traps[$alias]);
			for ($i = $last; $i >= 0; $i--)
			{
				unset(Adhoc::$traps[$alias][$i]);
			}
		}
		else
		{
			if (!Adhoc::hasTrap($alias, $instance))
			{
				throw new \OutOfBoundsException('Unexistant trap ('.$alias.') cannot be unset.');
			}
			
			$i = array_search($instance, Adhoc::$traps[$alias]);
			unset(Adhoc::$traps[$alias][$i]);
			
			Adhoc::$traps[$alias] = array_values(Adhoc::$traps[$alias]);
		}
	}
	
	/**
	 * Provides the ability to access a trap and that traps' one hook as
	 * calling a virtual method with the same name as an existing trap.
	 *
	 * <p>Calling example:</p>
	 * <code>$list = Adhoc::ErrorHandler('GetList');</code>
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic($method, $arguments)
	{
		$hook = array_shift($arguments);
		if (Adhoc::hasTrap($method))
		{
			foreach (Adhoc::$traps[$method] as $trap)
			{
				$result = $trap->executeHook($hook, $arguments);
				if (!is_null($result)) return $result;
			}
		}
		else
		{
			throw new \BadMethodCallException('Call of unexistant trap ('.$method.').');
		}
	}
	
	/**
	 * The specified $callback walks through on traps named in $alias argument.
	 *
	 * @param string $alias
	 * @param callback $callback
	 * @return mixed
	 * @throws \OutOfBoundsException
	 * @throws \InvalidArgumentException
	 */
	public static function eachTrap($alias, $callback)
	{
		if (!Adhoc::hasTrap($alias))
		{
			throw new \OutOfBoundsException('Unexistant trap: '.$alias);
		}
		if (!is_callable($callback))
		{
			throw new \InvalidArgumentException('argument $callback must be callable but '.gettype($callback).' given.');
		}
		
		if (is_object($callback) && $callback instanceof Closure)
		{
			foreach (Adhoc::$traps[$alias] as $trap)
			{
				$result = $callback($trap);
				if (!is_null($result)) return $result;
			}
		}
		else
		{
			foreach (Adhoc::$traps[$alias] as $trap)
			{
				$result = call_user_func($callback, $trap);
				if (!is_null($result)) return $result;
			}
		}
	}
	
	/**
	 * Tries to search and select hook object by its class name specified in
	 * $hookClass argument within the $alias trap collection. Returns null if
	 * no hook found named in $hookClass argument.
	 *
	 * @param string $alias
	 * @param string $hookClass
	 * @return \Adhoc\Interfaces\IHook
	 * @throws \OutOfBoundsException
	 * @throws \InvalidArgumentException
	 */
	public static function selectHook($alias, $hookClass)
	{
		if (!Adhoc::hasTrap($alias))
		{
			throw new \OutOfBoundsException('Unexistant trap: '.$alias);
		}
		if (!is_string($hookClass))
		{
			throw new \InvalidArgumentException('String excepted on $hookClass but '.gettype($hookClass).' given.');
		}
		
		$action = \Adhoc\Util::getClassName($hookClass);
		$result = null;
		$cb = function (\Adhoc\Interfaces\IHook $hook) use (&$result, $hookClass)
		{
			if (!isset($result) and (get_class($hook) == $hookClass))
			{
				$result = $hook;
			}
		};
		
		foreach (Adhoc::$traps[$alias] as $trap)
		{
			$trap->walk($cb, $action);
			if (!is_null($result)) break;
		}
		
		return $result;
	}
	
	protected static function initTraps()
	{
	}
	
	protected static function destroyTraps()
	{
	}
}