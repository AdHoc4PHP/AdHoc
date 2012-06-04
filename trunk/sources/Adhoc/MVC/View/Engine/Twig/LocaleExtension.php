<?php

namespace Adhoc\MVC\View\Engine\Twig;

class LocaleExtension extends \Twig_Extension
{
	public function getFunctions()
	{
		$t = new Twig_Function_Method($this, 't');
		$file = new Twig_Function_Method($this, 'file');
		$url = new Twig_Function_Method($this, 'url');
		$lang = new Twig_Function_Method($this, 'getSelected');
		return array(
			't'						=> $t,
			'locale_translate'		=> $t,
			'locale_file'			=> $file,
			'file'					=> $file,
			'locale_url'			=> $url,
			'url'					=> $url,
			'locale_getDefault'		=> new Twig_Function_Method($this, 'getDefault'),
			'locale_urlSwitch'		=> new Twig_Function_Method($this, 'urlSwitch'),
			'locale_getSelected'	=> $lang,
			'lang'					=> $lang
		);
	}
	
	public function t($somewhat, $lang=null) { return \Adhoc\Locale::t($somewhat, $lang); }
	
	public function file($path) { return \Adhoc\Locale::file($path); }
	
	public function url($url, $lang=null) { return \Adhoc\Locale::url($url, $lang); }
	
	public function getDefault() { return \Adhoc\Locale::getDefault(); }
	
	public function urlSwitch($to) { return \Adhoc\Locale::urlSwitch($to); }
	
	public function getSelected() { return \Adhoc\Locale::getSelected(); }
	
	public function getName() { return 'adhoc_locale'; }
}