<?php

namespace \Adhoc\Cache\Engine;

class APC extends \Adhoc\Cache\Engine
{
	/**
	 * Constructs the engine.
	 * @param string $connection unused.
	 * @param string $alias
	 * @param callback $defaultRevalidateAction
	 * @throws \Exception
	 */
	public function __construct($connection, $alias='default', $defaultRevalidateAction=null)
	{
		if (!function_exists('apc_add'))
		{
			throw new \Exception('APC support not found!');
		}
		
		if (!apc_exists('AdhocCache_Engine_APC-keys-'.$alias))
		{
			apc_add('AdhocCache_Engine_APC-keys-'.$alias, $this->keys);
		}
		else
		{
			$this->keys = apc_fetch('AdhocCache_Engine_APC-keys-'.$alias);
		}
		
		parent::__construct($connection, $alias, $defaultRevalidateAction);
	}
	
	protected function retriveItem($index)
	{
		if (isset($this->keys[$index]) and !$this->offsetExists($index) and apc_exists('AdhocCache_Engine_APC-item-'.$this->alias.'-'.$index))
		{
			parent::offsetSet($index, apc_fetch($this->getAPCKey($index)));
		}
	}
	
	public function onItemChange(AdhocCache_Item $item, $method)
	{
		$item->dirty = false;
		$apcKey = $this->getAPCKey($index);
		if (apc_exists($apcKey))
		{
			apc_delete($apcKey);
		}
		apc_add($apcKey, $item);
	}

	public function onItemRemove(AdhocCache_Item $item)
	{
		$apcKey = $this->getAPCKey($index);
		if (apc_exists($apcKey))
		{
			apc_delete($apcKey);
		}
	}
	
	protected function getAPCKey($index)
	{
		return 'AdhocCache_Engine_APC-item-'.$this->alias.'-'.$index;
	}
}