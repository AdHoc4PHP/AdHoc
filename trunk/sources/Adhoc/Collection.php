<?php

namespace Adhoc;

class Collection extends \ArrayObject
{
	protected $keys = array();
	
	/**
	 * Constructs the collection.
	 * @param array|\Iterator|\ArrayObject $array
	 * @throws \InvalidArgumentException
	 */
	public function __construct($array=array())
	{
		parent::__construct(array());
		
		if (!Collection::isIterable($array))
		{
			throw new \InvalidArgumentException('Passed $array is not iterable (array, ArrayObject or an object which implements Iterator)!');
		}
		
		foreach ($array as $k=>$v)
		{
			$this->offsetSet($k, $v);
		}
	}
	
	/**
	 * @throws Exceptions\ItemNotExists
	 */
	public function offsetGet($index)
	{
		if (!isset($this[$index]))
		{
			throw new Exceptions\ItemNotExists($index);
		}
		
		return parent::offsetGet($index);
	}
	
	public function offsetSet($index, $newval)
	{
		if (!isset($this->keys[$index])) $this->keys[$index] = $index;
		parent::offsetSet($index, $newval);
	}
	
	/**
	 * @throws \ItemNotExistsException
	 */
	public function offsetUnset($index)
	{
		if (!isset($this[$index]))
		{
			throw new \ItemNotExistsException($index);
		}
		
		unset($this->keys[$index]);
		parent::offsetUnset($index);
	}
	
	public function offsetExists($index)
	{
		return isset($this->keys[$index]);
	}
	
	public static function isIterable($array)
	{
		$result = false;
		if (is_array($array))
		{
			$result = true;
		}
		else
		{
			if (is_object($array))
			{
				if (in_array('Iterator', class_implements(get_class($array))))
				{
					$result = true;
				}
				else if ($array instanceof ArrayObject)
				{
					$result = true;
				}
			}
		}
		return $result;
	}
}