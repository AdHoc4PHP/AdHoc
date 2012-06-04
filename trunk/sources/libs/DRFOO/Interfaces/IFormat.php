<?php

namespace DRFOO\Interfaces;

/**
 * IFormat Interface for DR.FOO-standard Format classes
 * 
 * Formats knows they parents and able to return they details
 * as string.
 */
interface IFormat
{
	/**
	 * Constructor.
	 * 
	 * Knows its own parent.
	 * @param IFormat $format
	 * @param IFormat\ISettings $settings
	 */
	public function __construct(IFormat $parent, IFormat\ISettings $settings = NULL);
	
	/**
	 * Formats able to return they details as string for debugging purposes.
	 */
	public function __toString();
	
	/**
	 * Recursively checks if this Format item has a valid value.
	 * 
	 * @return bool
	 */
	public function isValid();
	
	/**
	 * Returns the format item's value.
	 *
	 * @return mixed|IFormat
	 */
	public function get();
	
	/**
	 * Changes the format item's value.
	 *
	 * @param mixed $value
	 * @return IFormat This instance.
	 */
	public function set($value);
}