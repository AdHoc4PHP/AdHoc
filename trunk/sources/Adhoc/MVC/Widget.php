<?php

namespace Adhoc\MVC;

class Widget
{
	/**
	 * Path of the application with trailing "/".
	 * @var string
	 */
	protected $appPath;
	
	/**
	 * The preprocessed request.
	 * @var array
	 */
	protected $request;
	
	/**
	 * Controller's own config file's path.
	 * @var string
	 */
	protected $configPath;
	
	/**
	 * Controller's own config.
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * Application's database connection.
	 * @var \Adhoc\PDO
	 */
	protected $connection;
	
	/**
	 * The application object wich is created this controller.
	 * @var \Adhoc\Application
	 */
	protected $application;
	
	/**
	 * Data representation object.
	 * @var View
	 */
	protected $view;
	
	public function __construct(Application $app)
	{
		$this->application = $app;
		$this->connection = $app->getConnection();
		$this->appPath = $app->getPath();
		$this->request = $app->getRequest();
		$this->configPath = $this->getPath(false).'config.'.get_class($this).'.json';
		if (file_exists($this->configPath))
		{
			$this->config = json_decode(file_get_contents($this->configPath), TRUE);
		}
	}
	
	public function execute()
	{
		
	}

	public function __destruct()
	{
		unset($this->config);
	}
	
	/**
	 * Gets the folder path for this widget.
	 * @param bool $full If <code>false</code> function returns the widget's
	 * folder path insead of the exact widget's path. 
	 */
	public function getPath($full=true)
	{
		return $this->appPath.'widget/'.($full? get_class($this).'/' : '');
	}
}