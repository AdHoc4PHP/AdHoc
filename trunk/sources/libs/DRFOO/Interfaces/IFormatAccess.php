<?php

namespace DRFOO\Interfaces;

/**
 * IFormatAccess Interface for DR.FOO-standard Format classes
 * 
 * Formats can able to work like an array, they are iterable and
 * can organized as a tree without any restrictions on they size.
 */
interface IFormatAccess extends \ArrayAccess
{
	/**
	 * This is necessary for deep clones of the Format.
	 */
	public function __clone();
	
	/**
	 * Return this Format as a standard PHP array.
	 * 
	 * Result is in mixed type when this Format item is a single item
	 * without any subitems. This method is recursive.
	 *
	 * @return array|mixed
	 */
	public function asArray();
}