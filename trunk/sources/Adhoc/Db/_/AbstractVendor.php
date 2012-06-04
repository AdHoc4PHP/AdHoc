<?php

namespace Adhoc\Db;

abstract class AbstractVendor
{
	/**
	 * @var \Adhoc\Db\Connection
	 */
	protected $connection;
	
	/**
	 * @var string
	 */
	protected $url;
	
	/**
	 * @var array
	 */
	protected $parsedUrl = array();
	
	/**
	 * Constructor
	 *
	 * @param \Adhoc\Db\Connection $connection
	 * @param string $url
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function __construct(\Adhoc\Db\Connection $connection, $url)
	{
		$this->connection = $connection;
		if (!is_string($url))
		{
			throw new \InvalidArgumentException('Argument $url of method '.__METHOD__.' must be a string, '.gettype($url).' given.');
		}
		$this->parsedUrl = \Adhoc\Util::parseUrl($url);
		if (count($this->parsedUrl) == 0)
		{
			throw new \RuntimeException('Argument $url of method '.__METHOD__.' must be a valid URL. Given string: '.$url);
		}
	}
	
	/**
	 * Returns a valid array of arguments for the connection specified in
	 * constructor's $url argument.
	 *
	 * @return array
	 */
	abstract public function getConnectionArguments();
	
	/**
	 * Checks if given method exists in this vendor.
	 *
	 * @param string $methodName
	 * @return bool True if method extsts, false otherwise.
	 * @thorws \InvalidArgumentException
	 */
	public function isImplemented($methodName)
	{
		if ($methodName == 'getConnectionArguments') return true;
		if (!is_string($methodName))
		{
			throw new \InvalidArgumentException('Argument $methodName of method '.__METHOD__.' must be a string, '.gettype($methodName).' given.');
		}
		
		$result = true;
		try
		{
			$r = new \ReflectionMethod($this, $methodName);
			if (!$r->isPublic() or $r->isStatic()) 
			{
				$result = false;
			}
		}
		catch (\ReflectionException $e)
		{
			$result = false;
		}
		
		return $result;
	}
}