<?php

namespace Adhoc;

/**
 * Standard class for handling HTTP responses.
 * 
 * @author prometheus
 *
 */
class Response
{
	/**
	 * Content of this response
	 * @var string
	 */
	protected $content;
	
	/**
	 * Headers without HTTP response code and message
	 * @var unknown_type
	 */
	protected $headers = array();
	
	/**
	 * HTTP response code
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
	 * @var int
	 */
	protected $code = 200;
	
	/**
	 * HTTP response message
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
	 * @var string
	 */
	protected $message = 'Ok';
	
	/**
	 * The {@link send} method skip sending {@link $content} if flagged.
	 * @var bool
	 */
	protected $skipContent = false;
	
	/**
	 * The {@link send} method skip sending {@link $headers} if flagged.
	 * @var bool
	 */
	protected $skipHeaders = false;
	
	/**
	 * 
	 * @param mixed $content
	 */
	public function __construct($content=null)
	{
		$this->setContent($content);
		$this->addDefaultHeaders();
	}
	
	/**
	 * 
	 * @param mixed $content
	 * @return Response
	 */
	public static function create($content=null)
	{
		return new Response($content);
	}
	
	protected function addDefaultHeaders()
	{
		$this->addHeader('Content-type', 'text/html; charset=utf-8');
	}
	
	/**
	 * 
	 * @param mixed $content
	 * @return Response
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}
	
	/**
	 * Adds a header key-value pair. Overwrite if key exists.
	 * @param string $definition
	 * @param string $value
	 * @return Response
	 */
	public function addHeader($definition, $value)
	{
		$this->headers[$definition] = $value;
		return $this;
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getContentString()
	{
		return (!empty($this->content)? $this->content : '');
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}
	
	/**
	 * @see $skipContent
	 * @param bool $state
	 * @return Response
	 */
	public function skipContent($state=true)
	{
		$this->skipContent = $state;
		return $this;
	}
	
	/**
	 * @see $skipHeaders
	 * @param bool $state
	 * @return Response
	 */
	public function skipHeaders($state=true)
	{
		$this->skipHeaders = $state;
		return $this;
	}
	
	protected function setupMessageByCode()
	{
		switch ($this->code)
		{
			case 100: { $this->message='Continue'; break; }
			case 101: { $this->message='Switching Protocols'; break; }
			case 200: { $this->message='Ok'; break; }
			case 201: { $this->message='Created'; break; }
			case 202: { $this->message='Accepted'; break; }
			case 203: { $this->message='Non-Authoritative Information'; break; }
			case 204: { $this->message='No Content'; $this->skipContent(); break; }
			case 205: { $this->message='Reset Content'; break; }
			case 206: { $this->message='Partial Content'; break; }
			case 300: { $this->message='Multiple Choices'; break; }
			case 301: { $this->message='Moved Permanently'; $this->skipContent(); break; }
			case 302: { $this->message='Found'; $this->skipContent(); break; }
			case 303: { $this->message='See Other'; $this->skipContent(); break; }
			case 304: { $this->message='Not Modified'; $this->skipContent(); break; }
			case 305: { $this->message='Use Proxy'; $this->skipContent(); break; }
			case 307: { $this->message='Temporary Redirect'; $this->skipContent(); break; }
			case 400: { $this->message='Bad Request'; break; }
			case 401: { $this->message='Unauthorized'; break; }
			case 402: { $this->message='Payment Required'; break; }
			case 403: { $this->message='Forbidden'; break; }
			case 404: { $this->message='Not Found'; break; }
			case 405: { $this->message='Method Not Allowed'; break; }
			case 406: { $this->message='Not Acceptable'; break; }
			case 407: { $this->message='Proxy Authentication Required'; break; }
			case 408: { $this->message='Request Time-out'; break; }
			case 409: { $this->message='Conflict'; break; }
			case 410: { $this->message='Gone'; break; }
			case 411: { $this->message='Length Required'; break; }
			case 412: { $this->message='Precondition Failed'; break; }
			case 413: { $this->message='Request Entity Too Large'; break; }
			case 414: { $this->message='Request-URI Too Large'; break; }
			case 415: { $this->message='Unsupported Media Type'; break; }
			case 416: { $this->message='Requested range not satisfiable'; break; }
			case 417: { $this->message='Expectation Failed'; break; }
			case 500: { $this->message='Internal Server Error'; break; }
			case 501: { $this->message='Not Implemented'; break; }
			case 502: { $this->message='Bad Gateway'; break; }
			case 503: { $this->message='Service Unavailable'; break; }
			case 504: { $this->message='Gateway Time-out'; break; }
			case 505: { $this->message='HTTP Version not supported'; break; }
		}
	}
	
	/**
	 * Sets HTTP code and message (based on the passed code).
	 * @param int $code
	 * @return Response
	 */
	public function setCode($code)
	{
		$this->code = $code;
		$this->setupMessageByCode();
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * Clears all headers.
	 * @param bool $addDefaults If true, restoring default headers.
	 * @return Response
	 */
	public function clearHeaders($addDefaults=false)
	{
		$this->headers = array();
		if ($addDefaults) $this->addDefaultHeaders();
		return $this;
	}
	
	/**
	 * Sets up this response to redirect the user agent to the specified
	 * location (URL) with the specified response code (301 by default).
	 * @param string $to
	 * @param int $code 301..307
	 * @return Response
	 */
	public function redirect($to, $code=301)
	{
		$this
			->clearHeaders()
			->setCode($code)
			->addHeader('Location', $to);
		return $this;
	}
	
	/**
	 * Sends out the response to the user agent.
	 */
	public function send()
	{
		header('HTTP/1.1 '.$this->code.' '.$this->message);
		
		if (!$this->skipHeaders)
		{
			foreach ($this->headers as $k=>$v)
			{
				header($k.': '.$v);
			}
		}
		
		if (!$this->skipContent) print $this->getContentString();
	}
}