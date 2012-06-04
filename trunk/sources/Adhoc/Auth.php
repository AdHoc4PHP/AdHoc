<?php

namespace Adhoc;

class Auth
{
	/**
	 * Name of the associated item in $_SESSION variable where the user's
	 * record will be placed.
	 * @var string
	 */
	protected $SESSION_ITEM = 'auth';
	
	protected $roleFieldName = 'role';
	
	protected $defaultRole = 'guest';
	
	/**
	 * The application's PDO object wich can handle the authorize queries.
	 * @var PDO
	 */
	protected $dbConnection;
	
	public function __construct(PDO $dbc)
	{
		$this->dbConnection = $dbc;
	}
	
	/**
	 * Logs out (makes unauthorized) the logged in user.
	 * @param string|bool $redirectTo Specifing the location where should be
	 * redirected to, or false to no redirect anyway.
	 * @param string $host If not specified, HTTP request's host will be used.
	 * @throws Exceptions\Fallback
	 */
	public function logout($redirectTo='/', $host=null)
	{
		unset($_SESSION[$this->SESSION_ITEM]);
		
		if ($redirectTo !== false and is_string($redirectTo))
		{
			if (!is_string($host)) $host = $_SERVER['HTTP_HOST'];
			
			Globals::get('DefaultApplication')->response->redirect('http://'.$host.$redirectTo);
			
			// No more common tasks after this, so break the control-flow:
			throw new Exceptions\Fallback();
		}
	}
	
	/**
	 * Trying to authorize the specified user with the given password.
	 * @param string $userId
	 * @param string $password
	 * @return bool True if user authorized successfully
	 */
	public function auth($userId, $password)
	{
		if (isset($_SESSION[$this->SESSION_ITEM])) return TRUE;
		if (empty($userId) or empty($password))
		{
			$this->logout(false);
			return FALSE;
		}

		$mUser = new Auth\Model($this->dbConnection);
		//$mUser->getConnection()->debug = TRUE;

		$authInfo = $mUser->isAuthorized($userId, $password);
		if (is_array($authInfo))
		{
			try
			{
				@session_commit();
			}
			catch (\Exception $e) {};
			$params = session_get_cookie_params();
			session_set_cookie_params($params['lifetime'], '/');
			try
			{
				@session_start();
			}
			catch (\Exception $e) {};
			$_SESSION[$this->SESSION_ITEM] = $authInfo;
			return TRUE;
		}
		else
		{
			$this->logout(false);
			return FALSE;
		}
	}
	
	public function isAuthorized()
	{
		return isset($_SESSION[$this->SESSION_ITEM]);
	}
	
	public function roleMatch($role, $to)
	{
		$roles = array(
			'denied'	=> -1,
			'guest'		=> 0,
			'member'	=> 1,
			'vip'		=> 2,
			'admin'		=> 3,
			'owner'		=> 4
		);
		return ($roles[$role] > $roles[$to]);
	}
	
	public function getUserRoles()
	{
		$result = $this->defaultRole;
		if ($this->isAuthorized())
		{
			$result = $_SESSION[$this->SESSION_ITEM][$this->roleFieldName];
		}
		return $result;
	}
	
	public function getDefaultRole()
	{
		return $this->defaultRole;
	}
	
	/**
	 * Returns the authorized user's record or <code>null</code> if user is
	 * unauthorized.
	 * @return array|null
	 */
	public function getAuthData()
	{
		return (isset($_SESSION[$this->SESSION_ITEM])? $_SESSION[$this->SESSION_ITEM] : null);
	}
}