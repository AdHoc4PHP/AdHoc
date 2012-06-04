<?php

namespace Adhoc\Db\SQL;

/**
 * This class used to manage SELECT queries with LIMIT clause.
 *
 * <p>You can send a SELECT with LIMIT to the DBMS then easily get the length of
 * returned resultset (count of rows returned) <i>without</i> LIMIT clause. For
 * example:</p>
 *
 * <code>&lt;?php
 *
 * // subject that if users table contains 121 records
 * $query = 'SELECT * FROM users';
 * $select = $connection->send('SQL\\Select');
 *
 * // getting a resultset contains 25 users after the 50th
 * $result = $select->limit(50, 25)->query($query);
 *
 * // will used \Adhoc\Db\SQL\Select\VendorHelp\MysqlHelper class if connection's vendor is 'mysql'
 * $countOfUsers = $select->getCount(); // returns 121, NOT 25!
 * </code>
 */
class Select extends SQL
{
	/**
	 * It's a very big number used as argument to LIMIT "unlimited" counts of records.
	 * @var number
	 */
	const SQL_BIGNUM = '18446744073709551615';
	
	/**
	 * @var int
	 */
	protected $offset;
	
	/**
	 * @var int
	 */
	protected $limit;
	
	/**
	 * @var string
	 */
	protected $query = '';
	
	/**
	 * This contains the counting query which MUST returns a single row and single
	 * column resultset, contains the count of original SELECT query's full
	 * resultset's (full resultset means that we getting without LIMIT clause) records.
	 *
	 * @var string
	 */
	protected $countQuery = '';
	
	/**
	 * Indicates that {@link $limit} or {@link $offset} changed.
	 * @var bool
	 */
	protected $dirty = false;
	
	/**
	 * The LIMIT clause which will concatanated to the {@link $query}.
	 * @var string
	 */
	protected $limitClause = '';
	
	/**
	 * Vendor specific helper instance.
	 * @var \Adhoc\Db\SQL\Select\VendorHelp
	 */
	protected $helper;
	
	/**
	 * Sets a pageframe to the resultset.
	 *
	 * <p>Use this method BEFORE calling {@link query}!</p>
	 *
	 * @param int $offset Starting rownumber of the pageframe.
	 * @param int $limit Count of requested rows.
	 * @return Select This instance
	 */
	public function limit($offset = 0, $limit = null)
	{
		$this->offset = (is_null($offset)? null : (int)$offset);
		$this->limit = (is_null($limit)? null: (int)$limit);
		$this->dirty = true;
		
		return $this;
	}
	
	/**
	 * Builds and returns the {@link $limitClause LIMIT clause}.
	 *
	 * <p>This method changes and uses the {@link dirty} flag. Returny the last used
	 * {@link $limitClause LIMIT clause} if dirty flag set and the clause isn't empty.</p>
	 *
	 * @return string
	 */
	protected function getLimitClause()
	{
		if (!$this->dirty and !empty($this->limitClause)) return $this->limitClause;
		
		$this->dirty = false;
		if (is_null($this->offset))
		{
			$this->limitClause = '';
			return '';
		}
		
		if (is_null($this->limit)) $this->limit = Select::SQL_BIGNUM;
		
		$result = "\n".'LIMIT '.$this->limit.' OFFSET '.$this->offset;
		$this->limitClause = $result;
		
		return $result;
	}
	
	/**
	 * Sends an SQL query to the connection and returns a {@link \Adhoc\Db\Resultset}.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * <p>Passed query should auto-modified via a vendor specfic helper - start the query
	 * with a "-" character to force not to modify the query passed.</p>
	 *
	 * @param string $query
	 * @return \Adhoc\Db\Resultset|bool Resultset instance if success, false otherwise.
	 * @throws \PDOException
	 */
	public function query($query, array $args = array(), $resultset = '\\Adhoc\\Db\\Resultset')
	{
		$query = ($query{0} != '-'? $this->getHelper()->query($query) : substr($query, 1));
		$this->query = $query;
		$this->countQuery = '';
		
		$query .= $this->getLimitClause();
		
		return parent::query($query, $args, $resultset);
	}
	
	/**
	 * Sends an SQL prepare to the connection and returns a {@link \Adhoc\Db\Statement}.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * <p>Passed query should auto-modified via a vendor specfic helper - start the query
	 * with a "-" character to force not to modify the query passed.</p>
	 *
	 * @param string $query
	 * @return \Adhoc\Db\Statement|bool Statement instance if success, false otherwise.
	 * @throws \PDOException
	 */
	public function prepare($query, $statement = '\\Adhoc\\Db\\Statement')
	{
		$query = ($query{0} != '-'? $this->getHelper()->query($query) : substr($query, 1));
		$this->query = $query;
		$this->countQuery = '';
		
		$query .= $this->getLimitClause();
		
		return parent::prepare($query, $statement);
	}
	
	/**
	 * Sends an SQL command to the connection.
	 *
	 * <p>Notice: {@link getConnection} method used to determinate the target connection.</p>
	 *
	 * <p>Passed query should auto-modified via a vendor specfic helper - start the query
	 * with a "-" character to force not to modify the query passed.</p>
	 *
	 * @param string $query
	 * @return bool True if success, false otherwise.
	 * @throws \PDOException
	 */
	public function exec($query)
	{
		$query = ($query{0} != '-'? $this->getHelper()->query($query) : substr($query, 1));
		$this->query = $query;
		$this->countQuery = '';
		
		$query .= $this->getLimitClause();
		
		return parent::exec($query);
	}
	
	/**
	 * Sets the no limited counting query for this object.
	 *
	 * <p>This method uses vendor-specific helper to generate default value based on
	 * the last sended query.</p>
	 *
	 * <p>Passed query MUST returns only one record with only one field which MUST
	 * contain an integer number counted the previous LIMITed query's resultset
	 * without LIMIT clause!</p>
	 *
	 * @param string $query This argument used to replace the auto-generated counting
	 * query if needed (for example becouse the complexity of the query).
	 * @return Select This instance
	 */
	public function setCountQuery($query = null)
	{
		if (is_null($query))
		{
			$query = $this->getHelper()->count($query);
		}
		
		$this->countQuery = $query;
		
		return $this;
	}
	
	/**
	 * Returns the full count of resultset of previous send query.
	 *
	 * <p>Full count of resultset means that we count a query's resultset's
	 * records without a LIMIT clause.</p>
	 *
	 * <p>This method shold use the {@link setCountQuery} method without arguments.</p>
	 *
	 * @param array $args Same arguments which are used on the original query if any.
	 * @return int|bool The count if success, false otherwise. You must use '==='
	 * operator to test the return value in the proper way (<i>bool</i> false checked with '=='
	 * equals with <i>int</i> zero too)!
	 * @throws \PDOException
	 */
	public function getCount(array $args = array())
	{
		// if query changed or no counting query set we must set one
		if ($this->dirty or empty($this->countQuery))
		{
			$this->countQuery = $this->setCountQuery();
		}
		
		// tring to prepare the query
		$stmt = $this->getConnection()->getPDO()->prepare($this->countQuery);
		if ($stmt === false) return false;
		
		// tring to execute prepared statement returned before
		$success = $stmt->execute();
		if ($success === false) return false;
		
		// tring to bind passed arguments to the prepared statement
		foreach ($args as $arg=>$value)
		{
			$success = $stmt->bindValue($arg, $value);
			if (!$success) return false;
		}
		
		// tring to get the onlyone value
		$result = $stmt->fetchCloumn(1);
		
		// closing cursor is important becouse the connection
		$stmt->closeCursor();
		
		// cleaning unused memory
		unset($stmt);
		
		// checking result and returning bool false if it is not a number
		if (!is_numeric($result)) return false;
		
		// returning result casted to integer
		return (int)$result;
	}
	
	/**
	 * @return \Adhoc\Db\SQL\VendorHelp
	 */
	protected function getHelper()
	{
		if (isset($this->helper)) return $this->helper;
		
		$helpClsBase = '\\Adhoc\\Db\\SQL\\Select\\VendorHelp\\';
		$helpClsName = $helpClsBase.ucfirst($this->getConnection()->getVendor()).'Helper';
		if (!class_exists($helpClsName))
		{
			$helpClsName = $helpClsBase.'DefaultHelper';
		}
		
		$this->helper = new $helpClsName();
		
		return $this->helper;
	}
}