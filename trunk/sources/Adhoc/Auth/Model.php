<?php

namespace Adhoc\Auth;

/**
 * Model
 *
 * @author prometheus
 */
class Model extends \Adhoc\Model
{
	public function isAuthorized($userid, $password)
	{
		$query = "
SELECT
	*
FROM
	{users}
WHERE
	md5(`userName`) = ".$this->connection->quote(md5($userid))." AND
	`userPass` = ".$this->connection->quote(md5($password))."
";

		// desktop.admin | power

		$stmt = $this->connection->query($query);

		if (!is_object($stmt)) return FALSE;

		$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (is_array($users) and count($users) > 0)
		{
			return $users[0];
		}

		return FALSE;
	}
}
