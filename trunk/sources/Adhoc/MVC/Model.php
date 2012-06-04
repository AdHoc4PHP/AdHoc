<?php

namespace Adhoc\MVC;

/**
 * Description of Model
 *
 * @author prometheus
 */
class Model
{
	/**
	 * @var \Adhoc\PDO
	 */
	protected $connection;

	public function __construct(\Adhoc\PDO $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @return \Adhoc\PDO
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	public function setConnection(\Adhoc\PDO $connection)
	{
		$this->connection = $connection;
	}
}

?>