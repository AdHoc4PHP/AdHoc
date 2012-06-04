<?php

namespace DRFOO\Interfaces;

/**
 * IDispatcher Interface for DR.FOO-standard Dispatcher classes
 * 
 * A Dispatcher has all of the abilities what a Resolver has, but
 * can attach any Resolver to itself (or Resolvers able to
 * subscribe to it) and able to sanitize the user's query.
 */
interface IDispatcher extends IResolver
{
	/**
	 * Attach any Resolver to this Dispatcher.
	 * @return IDispatcher
	 */
	public function attach(IResolver $resolver);
	
	/**
	 * Sanitize the user's query.
	 */
	public function filterUserQuery();
}