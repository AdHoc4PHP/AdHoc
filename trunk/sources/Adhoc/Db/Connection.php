<?php

namespace Adhoc\Db;

class Connection
{
	/**
	 * @var \PDO
	 */
	protected $pdo;
	
	/**
	 * @var array
	 */
	protected $cfg;
	
	/**
	 * @var array
	 */
	protected $options;
	
	/**
	 * @var array [PDO::ATTR_* constant => PDO setting value]
	 */
	protected $attribs;
	
	/**
	 * @var \Adhoc\Db\Connection
	 */
	protected $boundConnection;
	
	/**
	 * @var \Adhoc\Db\Connection\Transaction
	 */
	protected $transaction;
	
	/**
	 * @var array [PDO::ATTR_* constant => PDO setting value]
	 * <p>Defaults are:</p><ul>
	 * <li>PDO::ATTR_ERRMODE is PDO::ERRMODE_EXCEPTION, so any SQL error throws an exception
	 * in PHP at runtime.</li>
	 * <li>PDO::ATTR_PERSISTENT is true, this means that connections are persistent - this will
	 * use less resources but it should want more care from side of developer.</li></ul>
	 *
	 * <p>These defaults for to able you to override they in class children if needed.</p>
	 */
	protected $defaultAttribs = array(
		PDO::ATTR_ERRMODE		=> PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_PERSISTENT	=> true
	);
	
	/**
	 * Constructor
	 *
	 * @param \Adhoc\Db\Connection|array $cfg Connection settings or a connection what will be
	 * {@link bindConnection bounded} automatically.
	 * @param array $options PDO connection attributes
	 * @param bool $autoConnect Automatically connects if it is true, otherwise not connects
	 * just stores the $cfg and $options
	 */
	public function __construct($cfg, array $options, $autoConnect = true)
	{
		if (is_object($cfg) and $cfg instanceof \Adhoc\Db\Connection)
		{
			$this->bindConnection($cfg);
			return;
		}
		$this->storeSettings($cfg, $options);
		if ($autoConnect) $this->connect();
	}
	
	/**
	 * Try to connect to DBMS by the specified (or previously stored) connection settings.
	 *
	 * @param array $cfg Connection settings
	 * @param array $options PDO connection attributes
	 * just stores the $cfg and $options
	 * @throws \RuntimeException
	 */
	public function connect(array $cfg = null, array $options = null)
	{
		if (isset($cfg)) $this->storeSettings($cfg, $options);
		if (isset($this->pdo)) throw new \RuntimeException('A connection already used, cannot create another connection.');
		
		$pdo = $this->cfg['PDOClass'];
		try
		{
			$this->pdo = new $pdo($this->cfg['vendor'].':'.$this->cfg['dsn'], $this->cfg['username'], $this->cfg['password'], $this->attribs);
			foreach ($this->attribs as $attr=>$value)
			{
				$this->pdo->setAttribute($attr, $value);
			}
			
			// force to throw exception if not throwen but connection has an error
			if ($this->pdo->errorCode() !== '00000')
			{
				$err = $this->pdo->errorInfo();
				throw new \PDOException($err[2], $err[0]);
			}
			
			if (isset($this->options['onConnect']))
			{
				$commands = (is_array($this->options['onConnect'])? $this->options['onConnect'] : array($this->options['onConnect']));
				foreach ($commands as $command)
				{
					$this->pdo->exec($command);
				}
			}
		}
		catch (\PDOException $e)
		{
			if (isset($this->options['onFailure']) and is_callable($this->options['onFailure']))
			{
				call_user_func($this->options['onFailure'], $this, $e);
			}
			else
			{
				throw $e; // rethrow if there are no failure handler specified.
			}
		}
	}
	
	/**
	 * Processes and stores passed connection config and options.
	 *
	 * <p>A connection config is an associative array with five possible key and value:</p><ul>
	 * <li>PDOClass: Connection uses the indicated PDO class - default is "PDO".</li>
	 * <li>vendor: Required. This is the vendor specific prefix normally used in the DSN.</li>
	 * <li>dsn: Required. This is a valid PDO DSN string <b>without</b> vendor prefix.</li>
	 * <li>username: Used as the second ($username) argument in the PDO constructor.</li>
	 * <li>password: Used as the third ($password) argument in the PDO constructor.</li></ul>
	 * <p>For example this is a valid config:</p>
	 * <code>$cfg = array('vendor'=>'mysql', 'dsn'=>'dbname=mydatabase;host=localhost', 'username'=>'myuser', 'password'=>'secret');</code>
	 * <p>And another example in opposite the above (so this one is invalid):</p>
	 * <code>$cfg = array('vendor'=>'mysql', 'dsn'=>'mysql:dbname=mydb;host=localhost');</code>
	 * <p>Above example is invalid becouse the value of "dsn" <b>must not</b> contain the "mysql:"
	 * vendor prefix as you can see in the config's description.</p>
	 *
	 * <p>Passed options will selected as PDO connection attributes and as Connection's options
	 * by a specified way:</p><ul>
	 * <li>If a value stared with "@" and continued with an existing an accessible callback,
	 * the return value of that callback will used as the value of the attribute or option.</li>
	 * <li>If a value contains a name of an existing constant, that constan's value will used.</li>
	 * <li>If a key is a valid PDO constant, class will try to use that option item as PDO
	 * connection attribute constant with previously processed value. These attributes stored in
	 * {@link $attribs} property.</li>
	 * <li>If a key is not a valid PDO constant, class will store that option in {@link $options}
	 * property for implementation-specific usage.</li></ul>
	 *
	 * @param array $cfg
	 * @param array $options
	 * @throws \LengthException if $cfg has no items (its count equals to zero).
	 * @throws \OutOfBoundsException if invalid key used in $cfg.
	 * @throws \RuntimeException if any of the required $cfg items is not found in the passed array.
	 */
	protected function storeSettings(array $cfg, array $options = null)
	{
		if (count($cfg) == 0) throw new \LengthException('Passed argument $cfg in method '.__METHOD__.' must have 1 item at least.');
		
		$valid = array('PDOClass', 'vendor', 'dsn', 'username', 'password');
		$required = array('vendor', 'dsn');
		$passed = 0;
		
		$sanitized = array('PDOClass'=>'\\PDO', 'vendor'=>'', 'dsn'=>'', 'username'=>'', 'password'=>'');
		foreach ($cfg as $k=>$v)
		{
			// an exception being throwen if key invalid
			if (!in_array($k, $valid)) throw new \OutOfBoundsException('The key "'.$k.'" is invalid in argument $cfg in method'.__METHOD__.'.');
			
			// counting required and existed keys
			if (in_array($k, $required))
			{
				$passed++;
			}
			
			// all keys are acceptable now because invalid keys results an exception and required keys will checked
			$sanitized[$k] = $v;
		}
		
		// an exception throwen if a required key not exists
		if ($passed != count($required)) throw new \RuntimeException('The keys "'.join(', ', $required).'" are required in argument $cfg in method'.__METHOD__.' to connect to a DBMS.');
		
		$this->cfg = $sanitized;
		
		// force type array
		if (!is_array($options)) $options = array();
		// set the default settings if not present any in the passed options
		$options = $this->defaultAttribs + $options;
		// select the option items given
		foreach ($options as $attr=>$value)
		{
			// checks if option item's value marked as callback
			if ($value{0} === '@' and is_callable($cb = substr($value, 1)))
			{
				// executes the callback then use its return value as the value of that option item
				$value = call_user_func($cb, $this);
			}
			// checks if value is a defined constant
			else if (defined($value))
			{
				// use the constant's value instead
				$value = constant($value);
			}
			
			// check is option key is an existant PDO constant
			if (strtoupper(substr($attr, 0, 5)) == 'PDO::' and defined($attr))
			{
				// select this key and its processed value as a connection attribute
				$this->attribs[constant($attr)] = $value;
			}
			else
			{
				// otherwise select this key and its processed value as a class option
				$this->options[$attr] = $value;
			}
		}
	}
	
	/**
	 * Returns the PDO instance only for friendly classes
	 *
	 * <p>It's not recommended to directly use the returned PDO instance because
	 * it may turns your code to an inconsitsent fluffy bunny.</p>
	 *
	 * @return \PDO
	 */
	public function getPDO()
	{
		return $this->pdo;
	}
	
	/**
	 * Binds a connection to this connection.
	 *
	 * <p>Bounded connections are used only, no settings copied!</p>
	 */
	public function bindConnection(\Adhoc\Db\Connection $connection)
	{
		$this->boundConnection = $connection;
	}
	
	/**
	 * Unbinds the previously bounded connection.
	 */
	public function unbindConnection()
	{
		$this->boundConnection = null;
	}
	
	/**
	 * Returns the bounded connection or this connection if no bound.
	 *
	 * @return \Adhoc\Db\Connection
	 */
	public function getBoundConnection()
	{
		$result = $this;
		if (isset($this->boundConnection))
		{
			$result = $this->boundConnection;
		}
		
		return $result;
	}
	
	/**
	 * Disconnects from connected DBMS
	 */
	public function disconnect()
	{
		unset($this->pdo);
		$this->pdo = null;
	}
	
	/**
	 * Destructor
	 *
	 * <p>Closes the used connection</p>
	 */
	public function __destruct()
	{
		$this->disconnect();
	}
	
	/**
	 * Returns the connection-specific option(s)'s value.
	 *
	 * @param string|array The option key(s) you needed to get.
	 * @return mixed|array Array returns with mixed values if array passed as the argument.
	 */
	public function getOptions($keys = null)
	{
		if (!isset($keys)) return $this->options;
		if (!is_array($keys))
		{
			if (isset($this->options[$keys])) return $this->options[$keys];
		}
		else
		{
			$result = array();
			foreach ($keys as $key)
			{
				if (isset($this->options[$key])) $result[$key] = $this->options[$key];
			}
			return $result;
		}
	}
	
	/**
	 * Returns the connection-specific attribute(s)'s value.
	 *
	 * @param string|array The attribute key(s) you needed to get.
	 * @return mixed|array Array returns with mixed values if array passed as the argument.
	 */
	public function getAttribs($keys = null)
	{
		if (!isset($keys)) return $this->attribs;
		if (is_scalar($keys))
		{
			if (isset($this->attribs[$keys])) return $this->attribs[$keys];
		}
		else
		{
			$result = array();
			foreach ($keys as $key)
			{
				if (isset($this->attribs[$key])) $result[$key] = $this->attribs[$key];
			}
			return $result;
		}
	}
	
	/**
	 * Sets the connection-specific option(s)'s value.
	 *
	 * @param string|array The attribute key(s) you needed to set.
	 * @param mixed $value Use only if $options is not an array key.
	 */
	public function setOptions($options, $value = null)
	{
		if (!is_array($options))
		{
			$this->options[$options] = $value;
		}
		else
		{
			foreach ($options as $key=>$value)
			{
				$this->options[$key] = $value;
			}
		}
	}
	
	/**
	 * Sets the connection-specific attribute(s)'s value.
	 *
	 * @param string|array The attribute key(s) you needed to set.
	 * @param mixed $value Use only if $options is not an array key.
	 */
	public function setAttribs($attribs, $value = null)
	{
		if (!is_array($attribs))
		{
			$this->attribs[$attribs] = $value;
		}
		else
		{
			foreach ($attribs as $key=>$value)
			{
				$this->attribs[$key] = $value;
			}
		}
	}
	
	/**
	 * Returns the used DBMS's vendor (PDO vendor prefix).
	 *
	 * @return string
	 */
	public function getVendor()
	{
		return $this->cfg['vendor'];
	}
	
	/**
	 * Returns the last connection-level error's SQLSTATE-92 standardized code.
	 *
	 * @return string 5 digit code
	 */
	public function getErrorCode()
	{
		if (!isset($this->pdo)) throw new \RuntimeException('Not connected when calling method '.__METHOD__.'.');
		
		$msg = $this->pdo->errorInfo();
		
		return $msg[0];
	}
	
	/**
	 * Returns the last connection-level error's driver specific message.
	 *
	 * @return string
	 */
	public function getErrorMessage()
	{
		if (!isset($this->pdo)) throw new \RuntimeException('Not connected when calling method '.__METHOD__.'.');
		
		$msg = $this->pdo->errorInfo();
		
		return $msg[2];
	}
	
	/**
	 * Returns ID of the last inserted record or sequence value.
	 *
	 * <p><b>Some PDO drivers not support this calling!</b></p>
	 *
	 * @param string $name Used for identifing the auto-increment sequence if driver supports that.
	 * @return mixed
	 */
	public function getLastInsertId($name = null)
	{
		if (!isset($this->pdo)) throw new \RuntimeException('Not connected when calling method '.__METHOD__.'.');
		
		return $this->lastInsertId($name);
	}
	
	/**
	 * Provides a factory for sending SQL queries to the connected DBMS.
	 *
	 * <p>This method returns an instance of the specified class which implements
	 * {@link \Adhoc\Db\SQL} class. If no namespace before the class name then
	 * "\Adhoc\Db\" will be used.</p>
	 * <p>You can use \Adhoc\Db\SQL based class instances as an argument for formal
	 * purposes (the instanceof checking decreses a possible semantic error, or for
	 * another example this should be usable for code readibility reasons becouse
	 * increments the code's consistency).</p>
	 *
	 * @param \Adhoc\Db\SQL|string
	 * @return \Adhoc\Db\SQL
	 * @throws \InvalidArgumentException When passed $what (or the named class) not
	 * an instance of the \Adhoc\Db\SQL class.
	 */
	public function send($what = '\\Adhoc\\Db\\SQL')
	{
		$cb = \Adhoc\Closure::by(function($lazy)
		{
			$_this = \Adhoc\Closure::getBound(func_get_args());
			return new $lazy($_this);
		});
		$cb->bindTo($this);
		$result = Util::lazyClass($what, '\\Adhoc\\Db\\SQL', $cb);
		
		return $result;
	}
	
	/**
	 * Provides a factory for instancing a Transaction class based on this connection
	 * without a reconnect.
	 *
	 * <p>This method returns an instance of the specified class which implements
	 * {@link \Adhoc\Db\Connection\Transaction} class. If no namespace before the class
	 * name then "\Adhoc\Db\Connection\" will be used.</p>
	 * <p>You can use \Adhoc\Db\Connection\Transaction based class instances as an
	 * argument for formal purposes (the instanceof checking decreses a possible semantic
	 * error, or for another example this should be usable for code readibility reasons
	 * becouse increments the code's consistency).</p>
	 *
	 * @param \Adhoc\Db\Connection\Transaction|string
	 * @param bool $mustCreate Method try to returns the previously created transaction
	 * object if false, new instance created if true.
	 * @return \Adhoc\Db\Connection\Transaction
	 * @throws \InvalidArgumentException When passed $what (or the named class) not
	 * an instance of the \Adhoc\Db\Connection\Transaction class.
	 */
	public function transaction($instance = '\\Adhoc\\Db\\Connection\\Transaction', $mustCreate = false)
	{
		if (!$mustCreate and isset($this->transaction))
		{
			return $this->transaction;
		}
		
		$cb = \Adhoc\Closure::by(function($lazy)
		{
			$_this = \Adhoc\Closure::getBound(func_get_args());
			return new $lazy($_this);
		});
		$cb->bindTo($this);
		$this->transaction = Util::lazyClass($instance, '\\Adhoc\\Db\\Connection\\Transaction', $cb);
		
		return $this->transaction;
	}
}