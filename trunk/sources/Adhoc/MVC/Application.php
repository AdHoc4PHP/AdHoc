<?php

namespace Adhoc\MVC;

/**
 * Description of Application
 *
 * @author prometheus
 */
class Application
{
	/**
	 * Name of the requested controller class.
	 * @var string
	 */
	protected $controller = 'DefaultControl';
	
	/**
	 * Instance of the requested controller. 
	 * @var Controller
	 */
	protected $controllerInstance;

	/**
	 * Reusable database manager object.
	 * @var \Adhoc\PDO
	 */
	protected $connection;

	/**
	 * User's request (without the user query [q], with the passed arguments).
	 * @var array
	 */
	protected $request;

	/**
	 * Application's name or identifier on file system.
	 * @var string
	 */
	protected $appName = '';

	/**
	 * Application's own root path.
	 * @var string
	 */
	protected $appPath = '';

	/**
	 * Application's own configuration settings.
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * Application's response object
	 * @var \Adhoc\Response
	 */
	public $response;
	
	/**
	 * 
	 * @var \Adhoc\ErrorHandler
	 */
	protected $errorHandler;
	
	/**
	 * @var \Adhoc\Auth
	 */
	protected $auth;
	
	/**
	 * @var Model\Collection
	 */
	protected $models;
	
	/**
	 * @var Widget\Collection
	 */
	protected $widgets;
	
	/**
	 * Initializes the application.
	 * 
	 * This means (in the specified order):<ol>
	 * <li>Creates the {@link \Adhoc\Response response} object.</li>
	 * <li>Setting up a custom {@link \Adhoc\ErrorHandler error handler}.</li>
	 * <li>Setting up the {@link $appPath}.</li>
	 * <li>{@link $getConfig getting} and {@link $processConfig processing}
	 * application's config files.</li>
	 * <li>{@link $spawnConnection Spawning} the reusable DB connection and
	 * setting up the specified table prefix (see {@link \Adhoc\PDO}), then
	 * executing some optionally specified initialization queries.</li>
	 * <li>Creating {@link $models models'} collection.</li>
	 * <li>Attaching the auth class instance defined in config (or
	 * {@link \Adhoc\Auth} by default).</li>
	 * <li>Setting up {@link $appName} and
	 * {@link $request} based on the passed arguments.</li>
	 * <li>Stores the requested {@link Controller} in
	 * {@link $controller}.</li>
	 * <li>Requiring and instancing the requested controller.<li>
	 * </ol>
	 * <strong>A recommendation: override this method if you need to use
	 * multiple database connections in your application!</strong>
	 * <p>Used configuration settings are:</p>
	 * <ul>
	 * <li>string <code>dsn</code>: a valid PDO DSN - see
	 * {@link http://php.net/pdo}!</li>
	 * <li>[string <code>user</code>]: a username wich will be passed to the PDO
	 * constructor.</li>
	 * <li>[string <code>password</code>]: a password wich will be passed to the
	 * PDO constructor.</li>
	 * <li>[array <code>onAfterConnect</code>]: initialization queries used on
	 * step no. 5 looked above.</li>
	 * <li>[string <code>authClass</code>]: an <code>Auth</code>
	 * implementation for user authenticating.</li>
	 * <li>[array <code>cancelledErrors</code>] a list of PHP error constants.
	 * These type of errors will not throws an exception.</li>
	 * </ul>
	 * @param string $app Application's identifier (there is an existing
	 * "sources/app/<code>$app</code>" folder under your document root!)
	 * @param array $request In the format of {@link \Adhoc\Request::get} method's
	 * result!
	 */
	public function __construct($app, $request)
	{
		// Creates the response object
		$this->createResponse();
		
		// Hooking errors and exceptions
		$this->errorHandler = new \Adhoc\ErrorHandler(array($this, 'exceptionHandler'));
		
		// Application specific settings...
		$this->appPath = ADHOC_ROOT_DIR.'sources/app/'.$app.'/';
		
		// Got configuration
		$cfg = $this->getConfig();
		$cfg = $this->processConfig($cfg);
		$this->config = $cfg;
		
		// Setting up if has cancellable errors
		if (isset($this->config['cancelledErrors']))
		{
			$cancelledErrors = array();
			foreach ($this->config['cancelledErrors'] as $v)
			{
				$cancelledErrors[] = constant($v);
			}
			$this->errorHandler->silentErrors = $cancelledErrors;
		}
		
		// Spawning DB connection
		$this->connection = $this->spawnConnection($this->config['dsn'], $this->config['user'], $this->config['password']);
		$this->connection->setPrefix($this->config['prefix']);
		if (isset($this->config['onAfterConnect']))
		{
			foreach ($this->config['onAfterConnect'] as $query) $this->connection->exec($query);
		}
		
		// Creating models' collection
		$this->models = new Model\Collection($this);
		
		$authClass = 'Auth';
		if (isset($this->config['authClass']))
		{
			$authClass = $this->config['authClass'];
		}
		$this->auth = new $authClass($this->connection);
		
		// Processing user request
		if (count($request['q']) > 0)
		{
			$this->controller = array_shift($request['q']);
		}
		
		// Registering something
		$this->appName = $app;
		$this->request = $request;
		
		// Requiring and instancing controller
		if (!class_exists($this->controller, false))
		{
			$path = $this->appPath.'viewcontrol/'.$this->controller.'/controller.php';
			require($path);
		}
		$class = $this->controller;
		$this->controllerInstance = new $class($this);
	}
	
	/**
	 * Executes the selected {@link Controller}.
	 * @param bool $return
	 * @throws \Adhoc\Exceptions\Fallback
	 */
	public function run($return=false)
	{
		$result = null;
		try
		{
			$this->createWidgets();
			$result = $this->controllerInstance->execute();
		}
		catch (\Adhoc\Exceptions\Fallback $e)
		{
			if ($e->hasContent())
			{
				$result = $e->getContent();
			}
		}
		
		if ($return)
		{
			return $result;
		}
		
		if (isset($result))
		{
			$this->response->setContent($result);
		}
		
		$this->response->send();
	}
	
	protected function createWidgets()
	{
		$this->widgets = new Widget\Collection($this);
	}
	
	/**
	 * Closes (destructs) the reusable database connection and destructs the
	 * used controller object.
	 */
	public function __destruct()
	{
		unset(
			$this->controllerInstance,
			$this->connection,
			$this->errorHandler
		);
	}
	
	protected function createResponse()
	{
		$this->response = new \Adhoc\Response();
	}
	
	/**
	 * Spawning new DB connection and returning the neccessery PDO
	 * implementation.
	 * @param $dsn
	 * @param $user
	 * @param $password
	 * @return \Adhoc\PDO
	 */
	protected function spawnConnection($dsn, $user='', $password='')
	{
		return new \Adhoc\PDO($dsn, $user, $password);
	}

	/**
	 * Returns the reusable {@link PDO} database connection manager object
	 * bound to the application based on its config.
	 * This method cannot instances new connection manager objects!
	 * @see spawnConnection
	 * @see init
	 * @return \Adhoc\PDO
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * Returns the application's own configuration settings, based on the
	 * config.json and config.*.json.
	 * @see getConfig
	 * @param bool $sanitized If <code>true</code> a sanitized copy of config
	 * returned instead of original.
	 * @param mixed $sanitizeOpts Passed options to the
	 * {@link sanitizeConfig sanitizer method}.
	 * @return array
	 */
	public function cfg($sanitized=false, $sanitizeOpts=false)
	{
		return (!$sanitized? $this->config : $this->sanitizeConfig($sanitizeOpts));
	}
	
	/**
	 * Gets and returns the application's config, particulary relying on HTTP
	 * request's host name. This method IS NOT for call time-to-time, this
	 * method called once by {@link Application::init}! Use {@link cfg} method
	 * instead!
	 * <strong>This method is standard purposes and for override the standard
	 * identifing (and/or loading) mechanisms for config files if it is
	 * neccessary!</strong>
	 * @return array 
	 */
	protected function getConfig()
	{
		$defaultCfg = json_decode(file_get_contents($this->appPath.'config.json'), TRUE);
		if (
			isset($_SERVER) and
			isset($_SERVER['HTTP_HOST']) and
			!empty($_SERVER['HTTP_HOST']) and
			file_exists($this->appPath.'config.'.$_SERVER['HTTP_HOST'].'.json')
		)
		{
			$addieCfg = json_decode(file_get_contents($this->appPath.'config.'.$_SERVER['HTTP_HOST'].'.json'), TRUE);
			$defaultCfg = array_merge($defaultCfg, $addieCfg);
		}
		
		return $defaultCfg;
	}
	
	/**
	 * Returns a sanitized copy of the config.
	 * @param mixed $addOpts Additional option for sanitizing.
	 */
	protected function sanitizeConfig($addOpts=false)
	{
		$cfg = $this->config;
		unset(
			$cfg['dsn'],
			$cfg['user'],
			$cfg['password'],
			$cfg['prefix'],
			$cfg['onAfterConnect']
		);
		
		return $cfg;
	}
	
	/**
	 * 
	 * @param \Exception $e
	 * @protected
	 */
	public function exceptionHandler(\Exception $e)
	{
		$this->exceptionResponse($e);
	}
	
	/**
	 * Results detailed error/exception informations via HTTP 500.
	 * <strong>Override this method if application's Content-type header is not
	 * compatible with the HTML format!</strong>
	 * @param \Exception $e
	 */
	public function exceptionResponse(\Exception $e)
	{
		$data = Util::showException($e);
		$this->response
			->setCode(500)
			->setContent($data)
			->send();
	}
	
	/**
	 * Pre-processing configuration elements on this application. 
	 * @param array $cfg
	 * @return array
	 */
	protected function processConfig($cfg)
	{
		return Util::replaceTree('%ADHOC_ROOT_DIR%', ADHOC_ROOT_DIR, $cfg);
	}
	
	/**
	 * Returns the preprocessed request array.
	 * @return array
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * Returns the application's root path with trailing "/".
	 * @return string
	 */
	public function getPath()
	{
		return $this->appPath;
	}
	
	/**
	 * @throws \Adhoc\Exceptions\Auth
	 */
	public function authorize($level='application', $authFor=null, $wanted=null, $object=null)
	{
		if (
			isset($this->config['authorize']) and
			isset($this->config['authorize']['level']) and
			(
				(
					is_array($this->config['authorize']['level']) and
					in_array($level, $this->config['authorize']['level'])
				) or
				(
					$this->config['authorize']['level'] == $level
				)
			) and
			!$this->auth->isAuthorized())
		{
			throw new \Adhoc\Exceptions\Auth(get_class($this).': You doesn\'t have enough permissions to execute this application!');
		}
		
		if (!isset($authFor)) $authFor = $this->auth->getUserRoles();
		if (!isset($wanted)) $wanted = $this->auth->getDefaultRole();
		if ($this->auth->roleMatch($authFor, $wanted))
		{
			$clsName = (isset($object)? get_class($object) : (is_string($object)? $object : get_class($this).'\'s unknown object')); 
			throw new \Adhoc\Exceptions\Auth($clsName.': You doesn\'t have enough permissions!');
		}
	}
	
	/**
	 * @return \Adhoc\Auth
	 */
	public function getAuth()
	{
		return $this->auth;
	}
	
	/**
	 * @return Model\Collection
	 */
	public function getModels()
	{
		return $this->models;
	}
	
	/**
	 * @param string $name
	 * @return Model
	 */
	public function getModel($name)
	{
		return $this->models[$name];
	}
	
	/**
	 * @return Widget\Collection
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}
	
	/**
	 * @param string $name
	 * @return Widget
	 */
	public function getWidget($name)
	{
		return $this->widgets[$name];
	}
}

?>