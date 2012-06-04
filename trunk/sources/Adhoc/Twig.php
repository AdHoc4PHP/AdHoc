<?php

namespace Adhoc;

/**
 * Description of Twig
 *
 * @author prometheus
 */
class Twig
{
	public static function init()
	{
		require_once(_ROOT_DIR.'sources/libs/Twig/Autoloader.php');
		\Twig_Autoloader::register();
	}

	public static function create($appPath)
	{
		$loader = new \Twig_Loader_Filesystem($appPath.'viewcontrol', $appPath.'viewcache');
		$twig = new \Twig_Environment($loader);
		$twig->addExtension(new Twig\Lib());

		return $twig;
	}
}

?>