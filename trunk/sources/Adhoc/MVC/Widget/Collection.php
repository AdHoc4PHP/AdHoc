<?php

namespace Adhoc\MVC\Widget;

class Collection extends \Adhoc\Collection\Dedicated
{
	protected $CLASS_SUFFIX = 'Widget';
	
	protected $DEDICATED_FOR = '\\Adhoc\\MVC\\Widget';
	
	/**
	 * @var \Adhoc\MVC\Application
	 */
	protected $application;
	
	/**
	 * Constructs the collection.
	 * @param \Adhoc\MVC\Application $app
	 * @param array|\Iterator|\ArrayObject $array
	 */
	public function __construct(\Adhoc\MVC\Application $app, $array=array())
	{
		$this->application = $app;
		
		parent::__construct($array);
	}
	
	public function getPathFor($className)
	{
		return $this->application->getPath().'widget/'.$className.'/widget.php';
	}
	
	protected function createItem($className)
	{
		return new $className($this->application);
	}
}