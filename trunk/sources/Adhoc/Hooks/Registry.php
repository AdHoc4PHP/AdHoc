<?php

namespace Adhoc\Hooks;

/**
 * This is Adhoc system's common registry trap.
 *
 * You can use this trap to store any kind of information or data globally in
 * your context or the entire application's context.
 *
 * @trap Registry
 * @hook GetList {@link Adhoc\Hooks\Registry\GetList}
 * @hook Get {@link Adhoc\Hooks\Registry\Get}
 * @hook Set {@link Adhoc\Hooks\Registry\Set}
 * @hook Remove {@link Adhoc\Hooks\Registry\Remove}
 * @hook Has {@link Adhoc\Hooks\Registry\Has}
 * @hook Flatten {@link Adhoc\Hooks\Registry\Flatten}
 * @author prometheus
 */
class Registry extends \Adhoc\Trap
{
	public function __construct()
	{
		$this->
			registerHook(new Registry\GetList($this))->
			registerHook(new Registry\Get($this))->
			registerHook(new Registry\Set($this))->
			registerHook(new Registry\Remove($this))->
			registerHook(new Registry\Has($this))->
			registerHook(new Registry\Flatten($this));
	}
}