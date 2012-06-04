<?php

namespace Adhoc\Cache\Engine;

/**
 * This is a JSON file cache engine with supporting concurrent file locks.
 * 
 * <p><strong>Warning:</strong> This engine uses numered lock-files (*.lock.json)
 * and a flag file (*.dropped) solution to possible concurrent cache accessing
 * conflicts. This means you possibly need to purge cache folder periodically
 * if you experience that this engine sometimes not collected garbages
 * correctly!</p>
 * <p><strong>Example of usage:</strong>
 * <code>$foo = new AdhocCache_Engine_JSONFile('cache/foo/', 'foo');
 * if (!isset($foo['cached_hello']) $foo['cached_hello'] = 'hello';
 * if (!isset($foo['cached_world']) $foo['cached_world'] = new AdhocCache_Item('world');
 * $foo['cached_hello'] = 'Hello';
 * $foo->getItem('cached_world')-&gt;set('World');
 * print $foo['cached_hello'].' '.$foo['cached_world'].'!';</code></p>
 * 
 * @author prometheus
 */
class JSONFile extends Adhoc\Cache\Engine
{
	/**
	 * Location path for cached data folder <em>with</em> trailing "/".
	 * @var string
	 */
	protected $location;
	
	/**
	 * Constructs the engine.
	 * @param string $connection Folder path under ADHOC_ROOT_DIR <em>with</em>
	 * trailing "/".
	 * @param string $alias
	 * @param callback $defaultRevalidateAction
	 * @throws \Exception
	 */
	public function __construct($connection, $alias='default', $defaultRevalidateAction=null)
	{
		$this->location = str_replace('\\', '/', ADHOC_ROOT_DIR.$connection);
		if (!file_exists($this->location))
		{
			mkdir($this->location, 0777, true);
		}
		else
		{
			if (!is_dir($this->location))
			{
				throw new \Exception('Path "'.$this->location.'" not a directory.');
			}
			if (!is_readable($this->location))
			{
				throw new \Exception('Path "'.$this->location.'" not readable.');
			}
			if (!is_writable($this->location))
			{
				throw new \Exception('Path "'.$this->location.'" not writable.');
			}
		}
		
		parent::__construct($connection, $alias, $defaultRevalidateAction);
	}
	
	protected function retriveItem($index)
	{
		if (!isset($this->keys[$index]))
		{
			if ($this->hasDropFlag($index))
			{
				return;
			}
			
			$last = $this->getLastVersion($index);
			if ($last === false)
			{
				return;
			}
			
			$itemObject = json_decode(file_get_contents($last));
			if ($itemObject->type == \Adhoc\Cache\Item::TYPE_COMPLEX)
			{
				$itemObject->data = unserialize($itemObject->data);
			}
			
			$item = new \Adhoc\Cache\Item(null, $itemObject);
			$item->dirty = false;
			$item->Event->on('change', array($this, 'onItemChange'));
			$item->Event->on('remove', array($this, 'onItemRemove'));
			parent::offsetSet($index, $item);
		}
	}
	
	public function onItemChange(AdhocCache_Item $item, $method)
	{
		if ($this->hasDropFlag($item->getId()) and !$item->dirty)
		{
			return;
		}
		
		$lock = $this->createLock($item->getId());
		file_put_contents($lock, $item->toJSON());
		$item->dirty = false;
		$this->releaseLock($lock, $item->getId());
	}
	
	public function onItemRemove(AdhocCache_Item $item)
	{
		$index = $item->getId();
		if (!$this->hasDropFlag($index))
		{
			$dropFlag = $this->createDropFlag($index);
			$cacheFile = $this->location.$index.'.cache.json';
			
			if (file_exists($cacheFile) and is_writable($cacheFile))
			{
				unlink($cacheFile);
			}
			
			$list = glob($this->location.$index.'-*.lock.json');
			foreach ($list as $file)
			{
				if (file_exists($file) and is_writable($file))
				{
					unlink($file);
				}
			}
			
			$this->releaseDropFlag($dropFlag);
		}
	}
	
	public function getKeys()
	{
		$list = glob($this->location.'*.cache.json');
		$result = array();
		if (is_array($list))
		{
			foreach ($list as $file)
			{
				$matches = array();
				$file = str_replace('\\', '/', $file);
				if (preg_match('!/([^/].+?).cache\.json$!', $file, $matches))
				{
					$result[] = $matches[1];
				}
			}
		}
		
		return $result;
	}
	
	protected function getLastVersion($index)
	{
		$list = glob($this->location.$index.'-*.lock.json');
		
		if (is_array($list) and count($list) > 0)
		{
			$list = array_reverse($list);
			foreach ($list as $filePath)
			{
				if (is_readable($filePath) and filesize($filePath) > 0)
				{
					return str_replace('\\', '/', $filePath);
				}
			}
		}
		
		// if all locked files are unreadable
		$filePath = $this->location.$index.'.cache.json';
		if (file_exists($filePath))
		{
			return str_replace('\\', '/', $filePath);
		}
		else
		{
			return false;
		}
	}
	
	protected function createLock($index)
	{
		$list = glob($this->location.$index.'-*.lock.json');
		$last = $this->location.$index.'-00001.lock.json';
		
		if (is_array($list) and count($list) > 0)
		{
			$last = $list[count($list) - 1];
			$matches = array();
			preg_match('!\-(\d+)\.lock\.json$!', $last, $matches);
			$last = $this->location.$index.'-'.str_pad((string)(((int)$matches[1]) + 1), 5, '0', STR_PAD_LEFT).'.lock.json';
		}
		
		$last = str_replace('\\', '/', $last);
		
		file_put_contents($last, '');
		chmod($last, 0666);
		
		return $last;
	}
	
	protected function releaseLock($lockFile, $index)
	{
		$filePath = $this->location.$index.'.cache.json';
		$list = glob($this->location.$index.'-*.lock.json');
		if (is_array($list))
		{
			// a lock file is unlockable only if that is the last.
			$canUnlock = (($list[count($list) - 1] == $lockFile) || !file_exists($filePath));
			foreach ($list as $file)
			{
				$file = str_replace('\\', '/', $file);
				if ($file == $lockFile)
				{
					if ($canUnlock and file_exists($filePath) and is_writable($filePath))
					{
						unlink($filePath);
					}
					
					// do not remove later created lock files!
					break;
				}
				else
				{
					// remove potentially garbage
					if (file_exists($file) and is_readable($file) and is_writable($file) and filesize($file) > 0)
					{
						unlink($file);
					}
				}
			}
		}
		
		if (!file_exists($filePath))
		{
			rename($lockFile, $filePath);
		}
	}
	
	protected function createDropFlag($index)
	{
		$result = $this->location.$index.'.dropped';
		
		file_put_contents($result, (string)time());
		chmod($result, 0666);
		
		return $result;
	}
	
	protected function hasDropFlag($index)
	{
		return file_exists($this->location.$index.'.dropped');
	}
	
	protected function releaseDropFlag($flag)
	{
		if (file_exists($flag) and is_writable($flag))
		{
			unlink($flag);
		}
	}
}