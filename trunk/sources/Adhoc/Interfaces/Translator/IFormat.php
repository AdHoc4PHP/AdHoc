<?php

namespace Adhoc\Interfaces\Translator;

interface IFormat
{
	/**
	 * Constructor.
	 *
	 * Creates the formatter for owner translator passed in the argument.
	 *
	 * @param \Adhoc\Interfaces\ITranslator $translator
	 */
	public function __construct(\Adhoc\Interfaces\ITranslator $translator);
	
	/**
	 * Returns the formatted version of passed $string used $arguments for
	 * replacing any part of that.
	 *
	 * @param string $string
	 * @param arra $arguments
	 * @return string
	 */
	public function forString($string, $arguments = array());
	
	/**
	 * Returns formatted version of passed URL modified by $locale.
	 *
	 * @param string $url
	 * @param string $locale
	 * @return string
	 */
	public function forUrl($url, $locale);
	
	/**
	 * Returns formatted version of passed folder modified by $locale.
	 *
	 * @param string $folder
	 * @param string $locale
	 * @return string
	 */
	public function forFolder($folder, $locale);
	
	/**
	 * Returns formatted version of passed file modified by $locale.
	 *
	 * @param string $file
	 * @param string $locale
	 * @return string
	 */
	public function forFile($file, $locale);
}