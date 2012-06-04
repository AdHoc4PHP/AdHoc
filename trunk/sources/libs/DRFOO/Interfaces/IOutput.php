<?php

namespace DRFOO\Interfaces;

/**
 * IOutput Interface for DR.FOO-standard Output classes
 * 
 * An Output must handle only one Format per Resolver and results in only
 * one MIME-type.
 */
interface IOutput
{
	/**
	 * Constructor
	 *
	 * Creates the Output object, binds the default output stream for it
	 * and registers the instance for its Resolver.
	 *
	 * @param IResolver $resolver
	 * @param IFormat $format
	 */
	public function __construct(IResolver $resolver, IFormat $format);
	
	/**
	 * Destructor
	 *
	 * Flushes and closes the output stream before the instance destroyed.
	 */
	public function __destruct();
	
	/**
	 * Creates a new instance for this class.
	 *
	 * @param IResolver $resolver
	 * @param IFormat $format
	 * @return IOutput
	 */
	public static function create(IResolver $resolver, IFormat $format);
	
	/**
	 * Registers an Output instance for its Resolver.
	 *
	 * @param IResolver $resolver
	 * @param IOutput $output
	 */
	public static function registerOutput(IResolver $resolver, IOutput $output);
	
	/**
	 * Binds a PHP stream for this Output.
	 *
	 * @param resource $stream
	 */
	public function setStreamTo($stream);
	
	/**
	 * Returns the PHP stream bounded to this Output.
	 *
	 * @return resource
	 */
	public function getStream();
	
	/**
	 * Replaces the default Format by another (especially an error-) Format.
	 * Using this method will causing an E_USER_WARNING level error message!
	 *
	 * @param IFormat $format
	 * @return IOutput This Output instance
	 */
	public function rewriteBy(IFormat $format);
	
	/**
	 * Opens the bounded output stream for write and writes all Format-data to it.
	 * If that stream is the STDOUT and application requested via HTTP, sends the
	 * neccessary HTTP mime header before the data dump.
	 *
	 * @return IOutput This Output instance
	 */
	public function flush();
	
	/**
	 * Closes the bounded output stream. DO NOT close that stream again!
	 */
	public function close();
}