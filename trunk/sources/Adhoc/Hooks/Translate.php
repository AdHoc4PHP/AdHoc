<?php

namespace Adhoc\Hooks;

/**
 * This is Adhoc system's translation trap.
 *
 * You can register many types of translating engine, manage and use the
 * translating system in your application at any level.
 *
 * @trap Translate
 * @defaulthook Translate {@link Adhoc\Hooks\Translate\Translate}
 * @hook RegisterTranslator {@link Adhoc\Hooks\Translate\RegisterTranslator}
 * @hook GetTranslator {@link Adhoc\Hooks\Translate\GetTranslator}
 * @hook GetLocale {@link Adhoc\Hooks\Translate\GetLocale}
 * @hook SetLocale {@link Adhoc\Hooks\Translate\SetLocale}
 * @author prometheus
 */
class Translate extends \Adhoc\Trap
{
	public function __construct()
	{
		$this->
			registerHook(new Translate\RegisterTranslator($this))->
			registerHook(new Translate\GetTranslator($this))->
			registerHook(new Translate\Translate($this))->
			registerHook(new Translate\GetLocale($this))->
			registerHook(new Translate\SetLocale($this));
	}
}