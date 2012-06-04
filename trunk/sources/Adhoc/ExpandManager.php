<?php

namespace Adhoc;

class ExpandManager extends Collection\Dedicated
{
	protected $CLASS_SUFFIX = 'Expander';
	
	protected $DEDICATED_FOR = 'Expander';
	
	/**
	 * @var Application
	 */
	protected $application;
	
	protected $owner;
	
	/**
	 * Constructs the collection.
	 * @param object $owner
	 * @param Application $app
	 * @param array|\Iterator|\ArrayObject $array
	 */
	public function __construct($owner, $app=null, $array=array())
	{
		$this->application = (isset($app)? $app : Globals::get('DefaultApplication'));
		$this->owner = $owner;
		
		parent::__construct($array);
	}
	
	public function setApplication(Application $app)
	{
		$this->application = $app;
	}
	
	public function getApplication()
	{
		return $this->application;
	}
	
	public function getOwner()
	{
		return $this->owner;
	}
	
	public function handleCall($called, $args)
	{
		$matches = array();
		if (preg_match('!^(?P<expander>[A-Z][a-zA-Z0-9]+?)_(?P<method>[a-zA-Z0-9_]+)$!', $called, $matches))
		{
			return call_user_func_array(array($this[$matches['expander']], $matches['method']), $args);
		}
	}
	
	public function handleGet($property)
	{
		if (isset($this[$property]))
		{
			return $this[$property];
		}
	}
	
	public function getPathFor($className)
	{
		$result = '';
		if (isset($this->application))
		{
			$result = $this->application->getPath().'expander/'.$className.'.php';
		}
		if (!isset($this->application) or !file_exists($result))
		{
			$result = _ROOT_DIR.'sources/Adhoc/Expanders/'.$className.'.php';
		}
		
		return $result;
	}
	
	protected function createItem($className)
	{
		return new $className($this);
	}
}