<?php

namespace Adhoc\Interfaces;

interface ITranslator
{
	const NOTICE_EXCEPTION	= 0;
	const NOTICE_MESSAGE	= 1;
	const NOTICE_NONE		= 2;
	const NOTICE_ORIGINAL	= 3;
	const NOTICE_CUSTOM		= 4;
	
	/**
	 * Constructor
	 *
	 * Binds the store and the formatter to this translator, sets the locale and options for it.
	 *
	 * <p>Options are:</p><ul>
	 * <li>noticeMode: ITranslator::NOTICE_EXCEPTION, ITranslator::NOTICE_MESSAGE,
	 * ITranslator::NOTICE_NONE, ITranslator::NOTICE_ORIGINAL, ITranslator::NOTICE_CUSTOM</li>
	 * <li>noticeCallback: it's callable if it specified and has three
	 * arguments (ITranslator $translator, string $fromString, array $arguments). You must
	 * specify this when you use noticeMode as ITranslator::NOTICE_CUSTOM - in other noticeMode
	 * values this option will be ignored.</li>
	 * <li>storePath: the base path used for the store (its the path where the dictionaries
	 * found).</li>
	 * <li>storeMode: IStore::MODE_FILE, IStore::MODE_FOLDER, IStore::MODE_CUSTOM - in file and
	 * folder mode store uses the translator's formatter to specify the exact file- or folder
	 * name.</li>
	 * <li>storeModeCallback: a callable callback with one argument (IStore $store) if storeMode
	 * is IStore::MODE_CUSTOM</li>
	 * <li>storeLocateCallback: a callable callback with one argument (IStore $store) to locate
	 * the dictionaries for selected locale, returns an array of paths - store's used if this
	 * callback is not specified.</li>
	 * <li>storeLocateTokens: a comma separated list (or an array) of tokens witch are used by
	 * default locator - first token has highest- and last has lowest priority in order of they
	 * use.</li>
	 * </li></ul>
	 *
	 * <p>You may specify other custom options if your store and/or formatter needs some.</p>
	 *
	 * <p>In these options you can specify object method callback in string on the way below:</p>
	 * <pre>@\path\to\namespace\class(ctor args)->method</pre>
	 * <p>Specified class instanced with the ctor args, then method used on this object. Use
	 * this way only in a config setting and only if no other ways.</p>
	 *
	 * @param Translator\IStore $store
	 * @param Translator\IFormat $format
	 * @param string $locale
	 * @param array $options
	 */
	public function __construct(Translator\IStore $store, Translator\IFormat $format, $locale, $options = array());
	
	/**
	 * Sets the used locale for translate() calls in next times.
	 *
	 * @param string $toLocale
	 */
	public function setLocale($toLocale);
	
	/**
	 * Changes the callback used for noticeing when an unidentifiable string being translated.
	 *
	 * Warning: using this method suddenly changes the noticeMode option to
	 * ITranslator::NOTICE_CUSTOM!
	 *
	 * @param callback $callback
	 */
	public function setNotice($callback);
	
	/**
	 * Returns the options used by this translator.
	 *
	 * @return array
	 */
	public function getOptions();
	
	/**
	 * Returns the formatter used for this store.
	 *
	 * @return Translator\IFormat
	 */
	public function getFormat();
	
	/**
	 * Translates the string passed in first argument and tries to use data passed in second
	 * argument optionaly.
	 *
	 * @param string $fromString
	 * @param array $arguments
	 * @return string The translated string or a notice if that string not found in the store.
	 */
	public function translate($fromString, $arguments = array());
}