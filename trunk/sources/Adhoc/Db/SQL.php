<?php

namespace Adhoc\Db;

class SQL
{
	/**
	 * @var \Adhoc\Db\Connection
	 */
	protected $connection;
	
	/**
	 * Constructor
	 *
	 * @param \Adhoc\Db\Connection $connection
	 */
	public function __construct(\Adhoc\Db\Connection $connection)
	{
		$this->connection = $connection;
	}
	
	/**
	 * Sends an SQL query to the connection and returns a {@link \Adhoc\Db\Resultset}.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * @param string $query
	 * @return \Adhoc\Db\Resultset|bool Resultset instance if success, false otherwise.
	 * @throws \PDOException
	 */
	public function query($query, array $args = array(), $resultset = '\\Adhoc\\Db\\Resultset')
	{
		if (count($args) > 0)
		{
			$stmt = $this->getConnection()->getPDO()->prepare($query);
			if ($stmt === false) return false;
			
			foreach ($args as $arg=>$value)
			{
				$success = $stmt->bindValue($arg, $value);
				if (!$success) return false;
			}
			
			$success = $stmt->execute();
			if ($success === false) return false;
		}
		else
		{
			$stmt = $this->getConnection()->getPDO()->query($query);
			if ($stmt === false) return false;
		}
		
		$cb = \Adhoc\Closure::by(function($lazy)
		{
			$_this = \Adhoc\Closure::getBound(func_get_args());
			return new $lazy($_this);
		});
		$cb->bindTo($stmt); // callback uses the statement for context
		$result = Util::lazyClass($resultset, '\\Adhoc\\Db\\Resultset', $cb);
		
		return $result;
	}
	
	/**
	 * Sends an SQL prepare to the connection and returns a {@link \Adhoc\Db\Statement}.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * @param string $query
	 * @return \Adhoc\Db\Statement|bool Statement instance if success, false otherwise.
	 * @throws \PDOException
	 */
	public function prepare($query, $statement = '\\Adhoc\\Db\\Statement')
	{
		$stmt = $this->getConnection()->getPDO()->prepare($query);
		$result = false;
		if ($stmt !== false)
		{
			$cb = \Adhoc\Closure::by(function($lazy)
			{
				$_this = \Adhoc\Closure::getBound(func_get_args());
				return new $lazy($_this);
			});
			$cb->bindTo($stmt);
			$result = Util::lazyClass($statement, '\\Adhoc\\Db\\Statement', $cb);
		}
		
		return $result;
	}
	
	/**
	 * Sends an SQL command to the connection.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * @param string $query
	 * @return bool True if success, false otherwise.
	 * @throws \PDOException
	 */
	public function exec($query)
	{
		$result = $this->getConnection()->getPDO()->exec($query);
		
		return $result;
	}
	
	/**
	 * Returns the used connection or bound.
	 *
	 * @return \Adhoc\Db\Connection
	 */
	public function getConnection()
	{
		return $this->connection->getBoundConnection();
	}
}