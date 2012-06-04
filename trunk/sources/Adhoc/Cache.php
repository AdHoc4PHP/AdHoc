<?php

namespace Adhoc;

/**
 * This class manages the  System's caches.
 * 
 * @author prometheus
 */
class Cache
{
	protected $caches = array();
	
	private $constArgsDefaults = array(
		'connection'	=> '',
		'alias'			=> 'default',
		'defaultRA'		=> null
	);
	
	/**
	 * Constructs the cache stores' collection optionally with auto-create.
	 * 
	 * @param array $list Items to auto-create. Each item has able to contain
	 * 3 keys: connection, alias, defaultRA (these are the possible values for
	 * an {@link Cache\Engine::__construct cache constructor})
	 * @throws \Exception
	 */
	public function __construct($list=array())
	{
		foreach ($list as $engine=>$constArgs)
		{
			$constArgs = array_merge($this->constArgsDefaults, $constArgs);
			if (isset($this->caches[$constArgs['alias']]))
			{
				throw new \Exception('Cache alias "'.$constArgs['alias'].'" already exists!');
			}
			$className = 'Cache\\Engine\\'.$engine;
			$this->caches = new $className($constArgs['connection'], $constArgs['alias'], $constArgs['defaultRA']);
		}
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultExists()
	{
		return isset($this->caches['default']);
	} 
	
	public function __destruct()
	{
		foreach ($this->caches as $k=>$v)
		{
			unset($this->caches[$k]);
		}
	}
	
	/**
	 * 
	 * @param string $alias
	 * @return Cache\Engine
	 */
	public function getStore($alias)
	{
		return $this->caches[$alias];
	}
	
	/**
	 * 
	 * @param string $engine
	 * @param string $connection
	 * @param string $alias
	 * @param callback $defaultRA
	 * @throws \Exception
	 * @return Cache\Engine
	 */
	public function createByEngine($engine, $connection='', $alias='default', $defaultRA=null)
	{
		if (isset($this->caches[$alias]))
		{
			throw new \Exception('Cache alias "'.$constArgs['alias'].'" already exists!');
		}
		$className = 'Cache\\Engine\\'.$engine;
		$this->caches[$alias] = new $className($connection, $alias, $defaultRA);
		
		return $this->caches[$alias];
	}
	
	/**
	 * 
	 * @param string $className
	 * @param string $connection
	 * @param string $alias
	 * @param callback $defaultRA
	 * @throws \Exception
	 * @return Cache\Engine
	 */
	public function createByClass($className, $connection='', $alias='default', $defaultRA=null)
	{
		if (isset($this->caches[$alias]))
		{
			throw new \Exception('Cache alias "'.$constArgs['alias'].'" already exists!');
		}
		$this->caches[$alias] = new $className($connection, $alias, $defaultRA);
		
		return $this->caches[$alias];
	}
	
	/**
	 * 
	 * @param Cache\Engine $instance
	 * @throws \Exception
	 * @return Cache\Engine
	 */
	public function createByInstance(Cache\Engine $instance)
	{
		$alias = $instance->getAlias();
		if (isset($this->caches[$alias]))
		{
			throw new \Exception('Cache alias "'.$constArgs['alias'].'" already exists!');
		}
		$this->caches[$alias] = $instance;
		
		return $this->caches[$alias];
	}
	
	/**
	 * 
	 * @param string $item
	 * @param string $fromStore
	 * @return mixed
	 */
	public function get($item, $fromStore='default')
	{
		return $this->caches[$fromStore][$item];
	}
	
	/**
	 * 
	 * @param string $item
	 * @param mixed $value
	 * @param string $onStore
	 * @return mixed $value
	 */
	public function set($item, $value, $onStore='default')
	{
		$this->caches[$onStore][$item] = $value;
		return $this->caches[$onStore];
	}
	
	/**
	 * 
	 * @param string $item
	 * @param string $fromStore
	 */
	public function remove($item, $fromStore='default')
	{
		unset($this->caches[$fromStore][$item]);
	}
	
	/**
	 * 
	 * @param string $item
	 * @param string $inStore
	 * @return bool
	 */
	public function exists($item, $inStore='default')
	{
		return isset($this->caches[$inStore][$item]);
	}
}