<?php

namespace Adhoc\Twig;

/**
 * Description of TwigLib
 *
 * @author prometheus
 */
class TwigLib extends \Twig_Extension
{
	public function getFilters()
	{
		return array(
			'url'			=> array('adhoc_twiglib_url', FALSE),
			'url_switch'	=> array('adhoc_twiglib_url_switch', FALSE),
			'get_locale'	=> array('adhoc_twiglib_get_locale', FALSE),
			't'				=> array('adhoc_twiglib_translate', FALSE)
		);
	}

	public function getName()
	{
		return '';
	}
}

function adhoc_twiglib_url($url)
{
	return \Adhoc\Locale::url($url);
}

function adhoc_twiglib_url_switch($to)
{
	return \Adhoc\Locale::urlSwitch($to);
}

function adhoc_twiglib_get_locale()
{
	return \Adhoc\Locale::getSelected();
}

function adhoc_twiglib_translate($s)
{
	return \Adhoc\Locale::t($s);
}

?>