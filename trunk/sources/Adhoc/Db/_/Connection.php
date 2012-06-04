<?php

namespace Adhoc\Db;

class Connection
{
	/**
	 * @var \Adhoc\PDO
	 */
	protected $pdo;
	
	/**
	 * @var array The connection's parameters. Possible keys are: driver, username, password,
	 * hostname, database, settings, options. Key "settings" contains the connection string if
	 * specified, key "options" are the driver options (PDO constants and/or autoexec queries).
	 */
	protected $connectionArguments = array();
	
	/**
	 * @var \Adhoc\Db\Connection\AbstractVendor
	 */
	protected $driver;
	
	/**
	 * Constructor
	 *
	 * <p>Supports many DBMS schemas which PDO able to handle (mysql, pgsql, ...etc) and
	 * a special one: <i>pdo</i>.</p>
	 *
	 * <p>A standard URL is like: 'schema://username:password@hostname/database[?pdo_options]'</p>
	 * <p>Supported pdo_options are:</p><ul>
	 * <li>Any valid PDO::ATTR* constant</li>
	 * <li>array autoexec: An array of queries being executed with PDO::exec after connection
	 * estabilished (SET NAMES for MySQL, for example)</li></ul>
	 * <p>Options are URL-formatted key-value pairs (separated by ampersands).</p>
	 *
	 * <p>With pdo schema the url looks like this:
	 * 'pdo://[username[:password]@][hostname]/dsn_prefix/dsn_options[?pdo_options]'</p>
	 * <ul>
	 * <li>dsn_prefix is a valid DSN (driver or vendor) prefix</li>
	 * <li>dsn_options is the string after the prefix formatted like in a PDO DSN (separated
	 * by semicolons).</li>
	 * <ul>
	 * <p>So there is a way to you to setting up a connection based on connection string
	 * instead of PDO parameters - for example you can specify only an alias for an ODBC
	 * resource.</p>
	 *
	 * <p>Examples:</p><ul>
	 * <li>"mysql://adhocuser:secretpassword@localhost/adhoc_db" - Connects to the MySQL
	 * server on localhost in the name of adhocuser and selects the adhoc_db database as
	 * the connection's default database.</li>
	 * <li>"pdo:///odbc/Driver={Microsoft Access Driver (*.mdb)};Dbq=C:\\db.mdb;Uid=Admin" -
	 * Connects to an MS Access database existed at C:\db.mdb in the name of Admin via
	 * ODBC.</li></ul>
	 *
	 * <p>Special characters (for example: / and &) must be encoded by
	 * {@link http://php.net/urlencode urlencode} - see there about the details.</p>
	 */
	public function __construct($url)
	{
		$parsed = \Adhoc\Util::parseUrl($url);
		if (count($parsed) == 0)
		{
			throw new \InvalidArgumentException('Passed database connection URL possibly malformed: '.\Adhoc\Util::securedUrl($url));
		}
		
		$vendorSourcesPath = glob(ADHOC_ROOT_DIR.'sources/Adhoc/Db/Connection/Vendor/*.php');
		
		// Generating a map for vendor classes:
		// - keys are lower case based on file names under the folder
		// - values are in original case
		$vendors = array();
		$re = '%^.*/(?P<vendor>[^/]*)\.php$%';
		foreach ($vendorSourcesPath as $path)
		{
			if (preg_match($re, $path, $match))
			{
				$vendors[strtolower($match['vendor'])] = $match['vendor'];
			}
		}
		
		if (!isset($vendors[$parsed['schema']])
		{
			throw new \Exception('No driver class found for DSN prefix "'.$parsed['schema'].'". Check if file exists under sources/Adhoc/Db/Connection/Vendor folder. Database not supported if there is no file found named like that DSN prefix.');
		}
		
		$driverClass = '\\Adhoc\\Db\\Connection\\Vendor\\'.$vendors[$parsed['schema']];
		$this->driver = new $driverClass($this, $url);
		$this->connectionArguments = $this->driver->getConnectionArguments();
		/*
		switch ($parsed['schema'])
		{
			case 'pdo':
			{
				$path = explode('/', $parsed['path']);
				if (is_array($path) or empty($path[0]))
				{
					throw new \InvalidArgumentException('Passed database connection URL may not contain any driver identifier or it is malformed: '.\Adhoc\Util::securedUrl($url));
				}
				
				$this->parameters['driver'] = $path[0];
				if (isset($path[1])) $this->parameters['settings'] = $path[1];
				if (isset($parsed['user'])) $this->parameters['username'] = $parsed['user'];
				if (isset($parsed['pass'])) $this->parameters['password'] = $parsed['pass'];
				if (isset($parsed['host'])) $this->parameters['hostname'] = $parsed['host'];
				if (isset($parsed['query'])) $this->parameters['options'] = $parsed['query'];
				$this->driver = new \Adhoc\Db\Connection\Vendor\PDO($this, $url);
				break;
			}
			default:
			{
				if (!class_exists($driverClass))
				{
					throw new \Exception('Specified database connection URL indicates that "'.$parsed['schema'].'" driver need to connect, but '.$driverClass.' class not exists!');
				}
				
				break;
			}
		}
		*/
	}
}