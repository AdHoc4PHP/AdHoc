<?php

namespace Adhoc\Response;

class JSON extends \Adhoc\Response
{
	/**
	 * 
	 * @param array|object $content
	 * @return JSON
	 */
	public static function create($content)
	{
		return new JSON($content='');
	}
	
	protected function addDefaultHeaders()
	{
		$this->addHeader('Content-type', 'application/json');
	}
	
	public function getContentString()
	{
		return json_encode($this->getContent());
	}
	
	/**
	 * Redirects are NOT ALLOWED for JSON responses! This will results an
	 * exception!
	 * @param string $to
	 * @param int $code 301..307
	 * @return JSON
	 */
	public function redirect($to, $code=301)
	{
		throw new \Exception('\\Adhoc\\Response\\JSON::redirect() - Not allowed because coding security reasons!', 666);
		return $this;
	}
}