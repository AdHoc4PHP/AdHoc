<?php

namespace Adhoc;

/**
 * Description of PDO
 *
 * @author prometheus
 */
class PDO extends \PDO
{
	protected $prefix = '';
	
	public $debug = FALSE;
	
	public function __construct($dsn, $username="", $password="", $options=array())
	{
		parent::__construct($dsn, $username, $password, $options);
		$this->setAttribute(\PDO::ERRMODE_EXCEPTION, TRUE);
		$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\\Adhoc\\PDOStatement'));
	}
	
	public function setPrefix($prefix)
	{
		$this->prefix = (string)$prefix;
	}
	
	public static function protect($conn, &$array)
	{
		$args = func_get_args();
		if (count($args) == 2)
		{
			foreach ($array as $k=>$item)
			{
				$array[$k] = $conn->quote($item);
			}
		}
		else if (count($array) > 2)
		{
			array_shift($args);
			array_shift($args);
			foreach ($args as $k)
			{
				$array[$k] = $conn->quote($array[$k]);
			}
		}
	}
	
	public static function protectEach($conn, &$array)
	{
		$args = func_get_args();
		if (count($args) == 2)
		{
			foreach ($array as $k=>$item)
			{
				self::protect($conn, $array[$k]);
			}
		}
		else if (count($args) > 2)
		{
			array_shift($args);
			array_shift($args);
			foreach ($array as $k=>$item)
			{
				foreach ($args as $field)
				{
					$array[$field] = $conn->quote($array[$field]);
				}
			}
		}
	}
	
	public static function convertDateToUnix(&$date)
	{
		if (empty($date))
		{
			return;
		}
		
		$parts = explode('-', $date);
		$date = mktime(0, 0, 0, (int)$parts[1], (int)$parts[2], (int)$parts[0]);
	}
	
	public static function convertTimeToUnix(&$time)
	{
		if (empty($time))
		{
			return;
		}
		
		$time = strtotime($time);
	}
	
	public static function convertUnixToDate(&$date)
	{
		if (empty($date))
		{
			return;
		}
		
		$date = date('Y-m-d', (int)$date);
	}
	
	public static function convertUnixToTime(&$time)
	{
		if (empty($time))
		{
			return;
		}
		
		$time = date('H:i', (int)$time);
	}
	
	public function quote($string, $parameter_type = PDO::PARAM_STR)
	{
		if ($parameter_type === PDO::PARAM_STR)
		{
			$string = str_replace(array('{', '}'), array('\\{', '\\}'), (string)$string);
		}
		
		return parent::quote($string, $parameter_type);
	}
	
	public function query($query, $mode = PDO::FETCH_ASSOC)
	{
		$query = $this->queryReplazor($query);
		$args = func_get_args();
		$args[0] = $query;
		$result = call_user_func_array('parent::query', $args);
		
		if ($result === false and $this->debug)
		{
			$err = $this->errorInfo();
			FB::warn('SQL Query: '.$err[2]);
		}
		
		return $result;
	}
	
	public function prepare($query, $driverOptions=array())
	{
		$query = $this->queryReplazor($query);
		$stmt = parent::prepare($query, $driverOptions);
		
		if ($result === false and $this->debug)
		{
			$err = $this->errorInfo();
			FB::warn('SQL Prepare: '.$err[2]);
		}
		
		return $stmt;
	}
	
	public function exec($query)
	{
		$query = $this->queryReplazor($query);
		$result = parent::exec($query);
		
		if ($result === false and $this->debug)
		{
			$err = $this->errorInfo();
			FB::warn('SQL Exec: '.$err[2]);
		}
		
		return $result;
	}
	
	protected function queryReplazor($query)
	{
		$reTableNames = '#(?<!\\\\)\{(?<!\\\\)\{(.+?)(?<!\\\\)\}(?<!\\\\)\}#s';
		$query = preg_replace($reTableNames, '`'.$this->prefix.'$1`', $query);
		
		if ($this->debug) FB::log($query);
		
		return $query;
	}
}
