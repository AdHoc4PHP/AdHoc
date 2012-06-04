<?php

namespace Adhoc;

/**
 * Error handler class. Throws PHP errors as {@link http://php.net/ErrorException SPL ErrorException}.
 * 
 * @author Tyrael (original author)
 * @see https://github.com/Tyrael/php-error-handler
 * @author prometheus
 * @throws \InvalidArgumentException
 */
class ErrorHandler
{
	protected $errorHandler;
	protected $exceptionHandler;
	protected $errorsAsExceptions;
	protected $canceled;
	public $silentErrors = array();
	
	public function __construct($errorHandler=NULL, $errorsAsExceptions=true)
	{
		if ($errorHandler && !is_callable($errorHandler))
		{
			throw new \InvalidArgumentException('$errorHandler passed, but it isn\'t callable!');
		}
		$this->errorHandler = $errorHandler;
		$this->errorsAsExceptions = (bool)$errorsAsExceptions;
		
		if ($errorsAsExceptions)
		{
			$this->errorHandler = array($this, 'errorHandler');
			$this->exceptionHandler = $errorHandler;
			set_error_handler($this->errorHandler);
			set_exception_handler($this->exceptionHandler);
		}
		else if (isset($this->errorHandler))
		{
			set_error_handler($this->errorHandler);
		}
		
		register_shutdown_function(array($this, 'shutdownHandler'));
	}
	
	public function __destruct()
	{
		if (isset($this->errorHandler))
		{
			restore_error_handler();
		}
		$this->canceled = true;
	}

	public function shutdownHandler()
	{
		try
		{
			$error = error_get_last();
			if (in_array($error['type'], $this->silentErrors))
			{
				$error = false;
			}
			
			if ($error)
			{
				if ($this->errorsAsExceptions)
				{
					call_user_func(
						$this->exceptionHandler,
						new ErrorException(
							$error['message'],
							-$error['type'],
							$error['type'],
							$error['file'],
							$error['line']
						)
					);
				}
				else
				{
					call_user_func(
						$this->errorHandler,
						$error['type'],
						$error['message'],
						$error['file'],
						$error['line']
					);
				}
			}
		}
		catch (\Exception $e)
		{
			error_log("Exception cannot be thrown from the shutdownHandler:\n".print_r($e, true), 4);
        }
	}

	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext=NULL)
	{
		throw New \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}
