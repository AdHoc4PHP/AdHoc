<?php

namespace Adhoc;

/**
 * Default Hook Class for system hooks.
 *
 * @see Adhoc
 * @author prometheus
 */
abstract class Hook implements \Adhoc\Interfaces\IHook
{
	/**
	 * @var \Adhoc\Interfaces\ITrap
	 */
	protected $trap;
	
	/**
	 * Indicates that this is a default hook in a group.
	 * @var bool
	 */
	protected $isDefault = false;
	
	/**
	 * Constructor.
	 *
	 * Initializes that hookie...
	 * @param \Adhoc\Interfaces\ITrap The owner of this hook.
	 */
	public function __construct(\Adhoc\Interfaces\ITrap $trap)
	{
		$this->trap = $trap;
	}
	
	/**
	 * Destructor
	 *
	 * Unregisters that poor hookie from its Trap...
	 */
	public function __destruct()
	{
		$this->trap->unregisterHook($this);
	}
	
	/**
	 * Returns the hook's name. This is like a method's name.
	 *
	 * @return string Empty string returned if this is a default hook.
	 */
	public function __toString()
	{
		return ($this->isDefault === false? \Adhoc\Util::getClassName($this) : '');
	}
	
	/**
	 * Implements the hook's functionality.
	 *
	 * WARNING: Use {@link php.net/func_get_args func_get_args} to access the passed
	 * arguments, YOU CANNOT owerride this method's argument list because PHP's
	 * interface rules! You may use PHPDoc too for easy access to params.
	 *
	 * @return mixed NULL if hook wants to continue the bubbling of hooks, any other
	 * if returns a value (this will cancel the bubbling).
	 */
	abstract public function __invoke();
}