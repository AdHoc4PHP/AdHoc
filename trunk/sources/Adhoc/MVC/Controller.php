<?php

namespace Adhoc\MVC;

/**
 * Description of Controller
 *
 * @author prometheus
 */
class Controller extends Widget {
	/**
	 * Selected action-method.
	 * @var string
	 */
	protected $method = 'defaultAction';
	
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
	 * The role for test the user if permitted to use this controller.
	 * @var string
	 */
	protected $role = 'guest';
	
	public function __construct(Application $app)
	{
		parent::__construct($app);
		
		$this->application->authorize();
		$this->authorize();
		$this->request = $this->parseActionRoute($app->getRequest());
	}
	
	public function getPath($full=false)
	{
		return $this->appPath.'viewcontrol/'.($full? get_class($this).'/' : '');
	}
	
	protected function parseActionRoute($request)
	{
		if (count($request['q']) > 0)
		{
			$this->method = array_shift($request['q']).'Action';
		}
		return $request;
	}

	public function execute()
	{
		if (!method_exists($this, $this->method))
		{
			throw new Exception('The '.get_class($this).' controller couldn\'t call this action-method: '.$this->method);
		}
		return call_user_func_array(array($this, $this->method), $this->request['q']);
	}

	public function __destruct()
	{
		unset($this->config);
	}
	
	public function authorize($authFor=null)
	{
		$role = $this->role;
		if (isset($this->config['authorize'])) $role = isset($this->config['authorize']);
		
		$this->application->authorize('controller', $authFor, $role, $this);
	}
}

?>