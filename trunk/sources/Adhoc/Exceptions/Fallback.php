<?php

namespace Adhoc\Exceptions;

/**
 * This class is to give a standard way to falling back the running process to
 * a previiously specified catchpoint, basicly to {@link AdhocApplication::run}.
 * 
 * Throwing the \Adhoc\Exceptions\Fallback for example is a way to break the
 * current processing order after a user agent redirection header added.
 * 
 * If this exception thrown, the \Adhoc\Application's run method should be catch
 * that, but all outputs and (HTTP-) headers will be sent.
 * @author prometheus
 */
class Fallback extends \Exception
{
	/**
	 * @var mixed
	 */
	protected $content;
	
	/**
	 * @var \Exception
	 */
	protected $chained;
	
	/**
	 * This is the constructor of the AdhocFallbackException.
	 * <p><strong>If you don't specify $content for user agent response there
	 * will no content to send to the user agent.</strong><p>
	 * <p>Exception chaining gives a way to catch any thrown exception,
	 * generate a standard output, then throw AdhocFallbackException to a
	 * normal way of ending the running control-flow.</p> 
	 * @param mixed $content A content for the handler application's output
	 * @param \Exception $chained An exception wich is chained to this exception.
	 */
	public function __construct($content=null, $chained=null)
	{
		$this->content = $content;
		$this->chained = $chained;
		parent::__construct('\\Adhoc\\Exceptions\\Fallback exception thrown - normally this is handled by the application and you should not see it unhandled.', 0);
	}
	
	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * @return \Exception
	 */
	public function getChainedException()
	{
		return $this->chained;
	}
	
	/**
	 * @return bool
	 */
	public function hasChainedException()
	{
		return isset($this->chained);
	}
	
	/**
	 * @return bool
	 */
	public function hasContent()
	{
		return isset($this->content);
	}
}