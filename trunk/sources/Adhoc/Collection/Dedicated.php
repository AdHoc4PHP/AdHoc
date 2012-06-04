<?php

namespace Adhoc\Collection;

abstract class Dedicated extends AdhocCollection
{
	protected $CLASS_SUFFIX = '';
	
	protected $DEDICATED_FOR = '';
	
	/**
	 * Constructs the collection.
	 * @param array|\Iterator|\ArrayObject $array
	 * @throws \Exception
	 */
	public function __construct($array=array())
	{
		if (empty($this->CLASS_SUFFIX)) throw new \Exception('CLASS_SUFFIX must not empty!');
		if (empty($this->DEDICATED_FOR)) throw new \Exception('DEDICATED_FOR must not empty!');
		
		parent::__construct($array);
	}
	
	/**
	 * @throws \InvalidArgumentException
	 */
	public function offsetSet($index, $newval)
	{
		if (isset($index) and !is_string($index))
		{
			throw new \InvalidArgumentException('All '.get_class($this).'\'s items must be indexed with a string or null, but '.gettype($index).' index happened.');
		}
		
		$isItemValid = false;
		
		if (!isset($index))
		{
			if (is_string($newval))
			{
				$index = $newval;
				
				$className = $index.$this->CLASS_SUFFIX;
				$this->requireIf($className);
				
				$newval = $this->createItem($className);
				$isItemValid = true;
			}
			else if ($this->isValid($newval))
			{
				$index = $this->getKeyFor($newval);
				$isItemValid = true;
			}
			else
			{
				throw new \InvalidArgumentException('Unable to append item which is type of '.gettype($newval).'! Items must be strings or AdhocModel instances.');
			}
		}
		
		if (!$isItemValid and !$this->isValid($newval))
		{
			throw new \InvalidArgumentException('Index is proper, item is invalid.');
		}
		
		parent::offsetSet($index, $newval);
	}
	
	public function getKeyFor($item)
	{
		return substr(get_class($item), 0, -strlen($this->CLASS_SUFFIX));
	}
	
	/**
	 * @throws \Exception
	 */
	public function getPathFor($className)
	{
		throw new \Exception('This method must be implemented!');
	}
	
	public function isValid($item)
	{
		return (is_object($item) and $item instanceof $this->DEDICATED_FOR);
	}
	
	/**
	 * @throws \Adhoc\Exceptions\FileNotFound
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function requireIf($className)
	{
		if (!class_exists($className))
		{
			$filePath = $this->getPathFor($className);
			if (!file_exists($filePath))
			{
				throw new \Adhoc\Exceptions\FileNotFound($filePath);
			}
			
			require($filePath);
			
			if (!class_exists($className))
			{
				throw new \Exception('Class source found but '.$className.' definition is not there. Possibly a misspelled or unimplemented class in '.$filePath.'!');
			}
		}
		
		if (!in_array($this->DEDICATED_FOR, class_parents($className)))
		{
			throw new \InvalidArgumentException('Invalid cast of item. '.$className.' class found, but it isn\'t inheritor of '.$this->DEDICATED_FOR.'!');
		}
	}
	
	/**
	 * @throws \Exception
	 */
	protected function createItem($className)
	{
		throw new \Exception('This method must be implemented!');
	}
}