<?php

namespace Adhoc\MVC\Model;

class Collection extends \Adhoc\Collection\Dedicated
{
	protected $CLASS_SUFFIX = 'Model';
	
	protected $DEDICATED_FOR = '\\Adhoc\\MVC\\Model';
	
	/**
	 * @var \Adhoc\Application
	 */
	protected $application;
	
	/**
	 * Constructs the collection.
	 * @param \Adhoc\Application $app
	 * @param array|\Iterator|\ArrayObject $array
	 */
	public function __construct(\Adhoc\Application $app, $array=array())
	{
		$this->application = $app;
		
		parent::__construct($array);
	}
	
	public function getPathFor($className)
	{
		return $this->application->getPath().'model/'.$className.'.php';
	}
	
	protected function createItem($className)
	{
		return new $className($this->application->getConnection());
	}
}