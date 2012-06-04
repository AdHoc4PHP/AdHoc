<?php

namespace Adhoc;

/**
 * Description of Locale
 *
 * @author prometheus
 */
class Locale
{
	protected static $config = array(
		'default'	=> 'hu',
		'available'	=> array('hu')
	);

	protected static $dictionary = array();

	protected static $appPath;

	protected static $selected;

	public static function init($appPath)
	{
		self::$appPath = $appPath;
		if (file_exists($appPath.'locale.config.json'))
		{
			$cfg = json_decode(file_get_contents($appPath.'locale.config.json'), TRUE);
			self::$config = $cfg['settings'];
			self::$dictionary = $cfg['dictionary'];
		}
		self::$selected = self::$config['default'];
		$isRequested = FALSE;
		if (isset($_REQUEST['lang']) and in_array($_REQUEST['lang'], self::$config['available']))
		{
			self::$selected = $_REQUEST['lang'];
			$isRequested = TRUE;
		}
		if (!$isRequested and isset($_SESSION['lang']))
		{
			self::$selected = $_SESSION['lang'];
		}
		$_SESSION['lang'] = self::$selected;
	}

	public static function t($somewhat, $lang=NULL)
	{
		$result = $somewhat;
		if (is_null($lang))
		{
			$lang = self::$selected;
		}
		if (isset(self::$dictionary[$lang][$somewhat]))
		{
			$result = self::$dictionary[$lang][$somewhat];
		}
		return $result;
	}

	public static function file($path)
	{
		$inf = pathinfo($path);
		$result = $inf['dirname'].'/'.$inf['filename'].'_'.self::$selected.'.'.$inf['extension'];
		return $result;
	}

	public static function url($url, $lang=NULL)
	{
		$req = array();
		$urlInfo = parse_url($url);
		if (!isset($urlInfo['query'])) $urlInfo['query'] = '';
		parse_str($urlInfo['query'], $req);

		if (isset($req['q']))
		{
			$req['q'] = self::t($req['q'], $lang);
		}
		if (!isset($req['lang']))
		{
			$req['lang'] = self::$selected;
		}
		if (!is_null($lang))
		{
			$req['lang'] = $lang;
		}

		$result = '';
		$urlInfo['query'] = http_build_query($req);
		$result .= (isset($urlInfo['scheme'])? $urlInfo['scheme'].'://' : '');
		$result .= isset($urlInfo['user'])? $urlInfo['user'] : '';
		$result .= (isset($urlInfo['pass'])? ':'.$urlInfo['pass'] : '');
		$result .= (isset($urlInfo['user']) || isset($urlInfo['pass'])? '@' : '');
		$result .= isset($urlInfo['host'])? $urlInfo['host'] : '';
		$result .= (isset($urlInfo['port'])? ':'.$urlInfo['port'] : '');
		$result .= isset($urlInfo['path'])? $urlInfo['path'] : '';
		$result .= isset($urlInfo['query'])? '?'.$urlInfo['query'] : '';
		$result .= isset($urlInfo['fragment'])? '#'.$urlInfo['fragment'] : '';

		return $result;
	}

	public static function getDefault()
	{
		return self::$config['default'];
	}

	public static function urlSwitch($to)
	{
		return self::url($_SERVER['REQUEST_URI'], $to);
	}

	public static function getSelected()
	{
		return self::$selected;
	}
}

?>