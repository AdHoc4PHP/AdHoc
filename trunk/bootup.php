<?php

define('ADHOC_ROOT_DIR', str_replace('\\', '/', dirname(realpath(__FILE__)).'/'));

require(ADHOC_ROOT_DIR.'sources/core/Util.php');

set_magic_quotes_runtime(false);
date_default_timezone_set('Europe/Budapest');
ob_start();

class Bootup
{
	protected static $classes = array(
		// dedicated core classes:
		'AdhocApplication',
		'AdhocDeprecatedException',
		'AdhocErrorHandler',
		'AdhocFallbackException',
		'AdhocFileNotFoundException',
		'AdhocGlobals',
		'AdhocLocale',
	
		'AdhocCollection',
		'AdhocDedicatedCollection',
	
		'AdhocAuth',
		'AdhocAuthException',
		'AdhocModel',
		'AdhocModel/Collection',
		'AdhocAuthModel',
	
		'AdhocCache',
		'AdhocCache/ItemIsGarbageException',
		'AdhocCache/Item',
		'AdhocCache/Engine',
		'AdhocCache/Engine/JSONFile',
		'AdhocCache/Engine/APC',
	
		'AdhocWidget',
		'AdhocWidget/Collection',
		'AdhocController',
	
	
		'AdhocExpander',
		'AdhocExpandManager',
	
		'AdhocPDO',
		'AdhocPDOStatement',
		'ExtPDO',
		'ExtPDOStatement',
	
		'AdhocRequest',
		'AdhocResponse',
		'AdhocResponseJSON',
	
		'AdhocView',
		'AdhocView/EngineApplyViewException',
		'AdhocView/Engine',
		
		// contributed "3rd-party" classes:
		'contrib/FB',
		'contrib/SwiftMailer/swift_required',
		'contrib/Twig/ExtensionInterface',
		'contrib/Twig/Extension',
		'contrib/JSMin',
	
		// depends on contrib
		'AdhocView/Engine/Twig',
		'AdhocView/Engine/Twig/LocaleExtension'
	);

	public static function boot()
	{
		foreach (self::$classes as $class)
		{
			require(ADHOC_ROOT_DIR.'sources/core/'.$class.'.php');
		}
	}
}

require_once(ADHOC_ROOT_DIR.'sources/core/contrib/Twig/Autoloader.php');
Twig_Autoloader::register();

Bootup::boot();
if (!Util::iniGetBool('session.auto_start'))
{
	session_start();
}

?>
