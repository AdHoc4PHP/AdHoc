<?php

namespace Adhoc\PDO;

/**
 * Description of ExtPDO
 *
 * @author prometheus
 */
class ExtPDO extends \Adhoc\PDO
{
	public function __construct($dsn, $username="", $password="", $options=array())
	{
		parent::__construct($dsn, $username, $password, $options);
		$this->setAttribute(\PDO::ERRMODE_EXCEPTION, TRUE);
		$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\\Adhoc\\PDO\\ExtPDOStatement'));
	}
}
