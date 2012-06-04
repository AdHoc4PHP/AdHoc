<?php

namespace Adhoc\Hooks;

/**
 * Adhoc system's error handler trap supports multiple handle of errors and exceptions
 * in multiple dimensions of the application.
 *
 * You can make your own type of hook for handling errors appeares in runtime, you can
 * run multiple handlers on one error or exception triggered in your context.
 *
 * @trap ErrorHandler
 * @hook GetList {@link Adhoc\Hooks\ErrorHandler\GetList}
 * @hook Add {@link Adhoc\Hooks\ErrorHandler\Add}
 * @hook Remove {@link Adhoc\Hooks\ErrorHandler\Remove}
 * @hook Set {@link Adhoc\Hooks\ErrorHandler\Set}
 * @author prometheus
 */
class ErrorHandler extends \Adhoc\Trap
{
	/**
	 * Indicates wether or not registered the Adhoc system's PHP error-, exception-
	 * and shutdown (aka "fatal") handler.
	 *
	 * @var bool True if handlers are registered, false on default or otherwise.
	 */
	protected static $registered = false;
	
	/**
	 * Contructor.
	 *
	 * Registers the handlers and this trap's hooks.
	 */
	public function __construct()
	{
		ErrorHandler::registerHandlers();
		
		$this->
			registerHook(new ErrorHandler\GetList($this))->
			registerHook(new ErrorHandler\Add($this))->
			registerHook(new ErrorHandler\Remove($this))->
			registerHook(new ErrorHandler\Set($this));
	}
	
	/**
	 * Registers the handlers if they are not {@link $registered}.
	 */
	protected static function registerHandlers()
	{
		if (ErrorHandler::$registered) return;
		
		ErrorHandler::$registered = true;
		
		set_error_handler('ErrorHandler::__ERROR_HANDLER');
		set_exception_handler('ErrorHandler::__EXCEPTION_HANDLER');
		register_shutdown_function('ErrorHandler::__SHUTDOWN_FUNC');
	}
	
	/**
	 * This is the Adhoc system's PHP error handler callback. DO NOT CALL IT
	 * DIRECTLY!
	 *
	 * Exception throwing supported within this method!
	 *
	 * @protected
	 */
	public static function __ERROR_HANDLER($errno, $errstr, $errfile, $errline)
	{
		$result = Adhoc::eachTrap('ErrorHandler',
			function ($trap) use ($errno, $errstr, $errfile, $errline)
			{
				foreach ($trap->GetList() as $handler)
				{
					$result = $handler->error($errno, $errstr, $errfile, $errline);
					if (!is_null($result)) break;
				}
				return $result;
			}
		);
		if (!is_null($result)) return false;
	}
	
	/**
	 * This is the Adhoc system's PHP exception handler callback. DO NOT CALL IT
	 * DIRECTLY!
	 *
	 * Exception throwing NOT supported within this method!
	 *
	 * @protected
	 */
	public static function __EXCEPTION_HANDLER(\Exception $exception)
	{
		try
		{
			$result = Adhoc::eachTrap('ErrorHandler',
				function ($trap) use ($exception)
				{
					foreach ($trap->GetList() as $handler)
					{
						$result = $handler->exception($exception);
						if (!is_null($result)) break;
					}
				}
			);
		}
		catch (\Exception $e) {}
	}
	
	/**
	 * This is the Adhoc system's PHP shutdown handler callback. DO NOT CALL IT
	 * DIRECTLY!
	 *
	 * Exception throwing supported within this method!
	 *
	 * @protected
	 */
	public static function __SHUTDOWN_FUNC()
	{
		$result = Adhoc::eachTrap('ErrorHandler',
			function ($trap)
			{
				foreach ($trap->GetList() as $handler)
				{
					$result = $handler->shutdown();
					if (!is_null($result)) break;
				}
			}
		);
	}
}