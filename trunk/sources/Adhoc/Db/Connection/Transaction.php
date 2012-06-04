<?php

namespace Adhoc\Db\Connection;

class Transaction extends \Adhoc\Db\Connection
{
	/**
	 * Begins a transaction on this (or bounded) connection.
	 *
	 * @return Returns TRUE on success or FALSE on failure.
	 */
	public function start()
	{
		$connection = $this->getBoundConnection();
		return $connection->getPDO()->beginTransaction();
	}
	
	/**
	 * Commits the queries send to this transaction.
	 *
	 * @return Returns TRUE on success or FALSE on failure.
	 */
	public function commit()
	{
		$connection = $this->getBoundConnection();
		return $connection->getPDO()->commit();
	}
	
	/**
	 * Cancels (rolls back) the queries send to this transaction.
	 *
	 * @return Returns TRUE on success or FALSE on failure.
	 */
	public function cancel()
	{
		$connection = $this->getBoundConnection();
		return $connection->getPDO()->rollBack();
	}
}