<?php

namespace DRFOO\Interfaces;

/**
 * IUserQuery Interface for DR.FOO-standard Resolver and Dispatcher classes
 *
 * A UserQuery is the user's input, it's an HTTP query in the most cases.
 */
interface IUserQuery
{
	const METHOD_GET		= 'GET';
	const METHOD_POST		= 'POST';
	const METHOD_PUT		= 'PUT';
	const METHOD_DELETE		= 'DELETE';
	const METHOD_CONSOLE	= 'CONSOLE';
	const METHOD_INTERNAL	= 'INTERNAL';
	
	/**
	 * Constructor.
	 */
	public function __construct($method, $url, $arguments);
	
	/**
	 * Returns the user's query as a URL formatted string.
	 *
	 * @return string
	 */
	public function __toString();
	
	/**
	 * Returns the choosen method of that query: GET, POST, PUT, DELETE,
	 * CONSOLE, INTERNAL.
	 * <p>CONSOLE and INTERNAL methods are not HTTP methods, they are just for
	 * take testable if the application got its arguments via console shell or
	 * via a simulated (internal) calling.
	 *
	 * @return string
	 */
	public function getMethod();
	
	/**
	 * Returns the full URL query.
	 *
	 * @return string
	 */
	public function getURL();
	
	/**
	 * Returns the requested resource's path in URL format.
	 *
	 * @return string
	 */
	public function getResourcePath();
	
	/**
	 * Returns all passed user arguments except the restrictions in URL format.
	 *
	 * @return string
	 */
	public function getArguments();
	
	/**
	 * Returns all passed user request for restrictions in comma separated
	 * "key: value" paired format.
	 *
	 * @return string
	 */
	public function getRestrictions();
}