<?php

namespace Adhoc\Cache;

/**
 * This is a basic cache engine - {@link AdhocCollection collection} of cached {@link Item items}.
 * 
 * <p><i>Remark: this is a dummy cache engine, use any descendants for real
 * caching support!</i></p>
 * <p><strong>Example of usage: </strong>
 * <code>$foo = new Engine('', 'foo');
 * if (!isset($foo['cached_hello']) $foo['cached_hello'] = 'hello';
 * if (!isset($foo['cached_world']) $foo['cached_world'] = new Item('world');
 * $foo['cached_hello'] = 'Hello';
 * $foo->getItem('cached_world')-&gt;set('World');
 * print $foo['cached_hello'].' '.$foo['cached_world'].'!';</code></p>
 * 
 * @author prometheus
 */
class Engine extends Adhoc\Collection
{
	/**
	 * The engine's identifier (the type of this engine - by other words).
	 * @var string
	 */
	protected $identifier = 'fake';
	
	/**
	 * Connection string for using this engine (if needed).
	 * @var string
	 */
	protected $connection = '';
	
	/**
	 * Alias of this instance.
	 * @var string
	 */
	protected $alias = 'default';
	
	/**
	 * The default "revalidate" event handler for all items.
	 * @var callback
	 */
	protected $defaultRevalidateAction;
	
	/**
	 * Constructs the engine.
	 * @param string $connection
	 * @param string $alias
	 * @param callback $defaultRevalidateAction
	 */
	public function __construct($connection, $alias='default', $defaultRevalidateAction=null)
	{
		$this->connection = $connection;
		$this->alias = $alias;
		$this->defaultRevalidateAction = $defaultRevalidateAction;
		
		parent::__construct();
	}
	
	/**
	 * Destructor with garbage collection.
	 */
	public function __destruct()
	{
		$this->runGarbageCollection();
		parent::__destruct();
	}
	
	/**
	 * Returns the alias of this engine instance.
	 * @return string
	 */
	public function getAlias()
	{
		return $this->alias;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see sources/core/AdhocCollection::offsetGet()
	 */
	public function offsetGet($index)
	{
		$this->retriveItem($index);
		$item = parent::offsetGet($index);
		return $item->get();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Adhoc\Collection::offsetSet()
	 */
	public function offsetSet($index, $newval)
	{
		$this->retriveItem($index);
		if (is_object($newval) and ($newval instanceof Item))
		{
			if (isset($this->keys[$index]))
			{
				$oldval = $this->offsetGet($index);
				if ($oldval !== $newval)
				{
					$newval->setId($index);
					$newval->Event->moveFrom($oldval->Event);
					// register that if we subscribed to the "remove" event...
					$hasRemoveHandler = $newval->Event->isOn('remove', array($this, 'onItemRemove'));
					
					// items can be created outside so we need to ensure that we
					// subscribed to mandatory events.
					$newval->Event->on('change', array($this, 'onItemChange'));
					$newval->Event->on('remove', array($this, 'onItemRemove'));
					
					// unsubscribe from unneccessary events.
					$oldval->Event->un('change', array($this, 'onItemChange'));
					$oldval->Event->un('remove', array($this, 'onItemRemove'));
					
					if (isset($this->defaultRevalidateAction))
					{
						// subscribe to new
						$newval->Event->on('revalidate', $this->defaultRevalidateAction);
						// unsubscribe from old
						$oldval->Event->un('revalidate', $this->defaultRevalidateAction);
					}
					
					// if we has no subscription to "remove" event before, we need to check
					if (!$hasRemoveHandler and $newval->isRemoved())
					{
						// we must handle the deletion
						$this->onItemRemove($newval);
						$this->offsetUnset($index);
						return;
					} 
				}
			}
		}
		else
		{
			if (!isset($this->keys[$index]))
			{
				$newval = new Item($newval);
				$newval->Event->on('change', array($this, 'onItemChange'));
				$newval->Event->on('remove', array($this, 'onItemRemove'));
				if (isset($this->defaultRevalidateAction))
				{
					$newval->Event->on('revalidate', $this->defaultRevalidateAction);
				}
			}
			else
			{
				parent::offsetGet($index)->set($newval);
				return;
			}
		}
		
		parent::offsetSet($index, $newval);
		
		// item has no way to detect its replace, we need to fire "change" event 
		$newval->Event->fire('change', array($newval, 'set'));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Adhoc\Collection::offsetUnset()
	 */
	public function offsetUnset($index)
	{
		$this->getItem($index)->remove();
		
		parent::offsetUnset($index);
	}
	
	/**
	 * Returns a cached item.
	 * @param string $index
	 * @return Item
	 */
	public function getItem($index)
	{
		return parent::offsetGet($index);
	}
	
	/**
	 * Override this method to retrive the specified item from cache.
	 * @param $index
	 */
	protected function retriveItem($index) {}
	
	/**
	 * Removes all data which is not valid.
	 */
	public function runGarbageCollection()
	{
		foreach ($this->keys as $key)
		{
			if (!$key->isValid())
			{
				$this->offsetUnset($key);
			}
		}
	}
	
	/**
	 * Returns all data identifier keys.
	 * @return array
	 */
	public function getKeys()
	{
		return array_values($this->keys);
	}
	
	/**
	 * Override this method to handle items "change" event after the value
	 * changing.
	 * @param Item $item
	 * @param string $method
	 */
	public function onItemChange(Item $item, $method) {}
	
	/**
	 * Override this method to handle items "remove" event after item marked as
	 * removed. The <code>parent::</code>{@link offsetUnset} method called after
	 * this.
	 * @param Item $item
	 */
	public function onItemRemove(Item $item) {}
}