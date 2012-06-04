<?php

namespace DRFOO\Interfaces;

/**
 * IResolver Interface for DR.FOO-standard Resolver classes
 * 
 * A Resolver can be called as a single function or via the
 * resolve() method. All Resolver must use the resolve() method
 * for resolving the user's query and returning a standard and
 * predeclared Format.
 */
interface IResolver
{
	/**
	 * Contructor.
	 *
	 * Gives the user's query for processing that in here
	 * or in the {@link resolve resolve()} method.
	 */
	public function __construct(IUserQuery $env);
	
	/**
	 * This class is able to called like a function.
	 *
	 * Method passes the user call to the {@link resolve} method and
	 * returns its returning value.
	 */
	public function __invoke();
	
	/**
	 * This method resolves the user's query on its own level
	 * within the resolving queue.
	 * @return Format
	 */
	public function resolve();
	
	/**
	 * Marks to use a non-default Format as the return type
	 * of the resolve() method.
	 * @return IResolver
	 */
	public function useFormat(IFormat $format);
	
	/**
	 * Returns the latest used Format.
	 * @return Format
	 */
	public function getFormat();
	
	/**
	 * Returns the appropirate Output for this Resolver.
	 *
	 * Creates the Output by the optionally specified (or the default)
	 * user restriction request dedicated for this Resolver and
	 * reuse that instance at any time you call this method. Normally
	 * you use this method only once and only in the {@link resolve} method.
	 *
	 * @return IOutput
	 */
	public function getOutput();
	
	/**
	 * Able to subscribe this resolver to another one,
	 * as a part of the entire resolving queue.
	 */
	public function subscribeTo(IDispatcher $dispatcher);
}