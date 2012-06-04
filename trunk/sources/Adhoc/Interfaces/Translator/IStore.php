<?php

namespace Adhoc\Interfaces\Translator;

interface IStore
{
	/**
	 * Constructor
	 *
	 * @param \Adhoc\Interfaces\ITranslator $translator Owner of this store.
	 */
	public function __construct(\Adhoc\Interfaces\ITranslator $translator);
	
	/**
	 * Returns the owner translator object of this store.
	 *
	 * @return \Adhoc\Interface\ITranslator
	 */
	public function getTranslator();
	
	/**
	 * Reads the entire store in specified locale.
	 *
	 * @param string $locale
	 */
	public function read($locale);
	
	/**
	 * Writes the entire store for specified locale.
	 *
	 * @param string $locale
	 */
	public function write($locale);
	
	/**
	 * Gets a string from the store (store must loaded before).
	 *
	 * @param string $string
	 * @return bool|string The specified value-pair of the specified string or false
	 * if not found that.
	 */
	public function get($string);
	
	/**
	 * Sets an existing string to the specified (store must loaded before). This method
	 * overwrites the key string only!
	 *
	 * @param string $string
	 * @param string $toString
	 */
	public function set($string, $toString);
	
	/**
	 * Adds a new string for the store. If second argument specified you may have a
	 * value assigned for the key.
	 *
	 * @param string $string
	 * @param string $value
	 */
	public function add($string, $value = '');
	
	/**
	 * Assignes (overwrites) the value of the specified key-string (store must loaded before).
	 *
	 * @param string $string
	 * @param string $value
	 */
	public function assign($string, $value);
}