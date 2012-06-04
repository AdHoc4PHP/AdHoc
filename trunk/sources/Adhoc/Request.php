<?php

namespace Adhoc;

/**
 * Description of Request
 *
 * @author prometheus
 */
class Request {
	protected static $request;

	public static function init($request)
	{
		$request = Util::unquoteAll($request);

		if (isset($request['q']))
		{
			$request['q'] = Locale::t($request['q'], Locale::getDefault());
			$request['q'] = explode('/', $request['q']);
		}
		else
		{
			$request['q'] = array();
		}
		self::$request = $request;
	}

	public static function get()
	{
		return self::$request;
	}

	public static function setQuery()
	{
		self::$request['q'] = func_get_args();
	}
}

?>