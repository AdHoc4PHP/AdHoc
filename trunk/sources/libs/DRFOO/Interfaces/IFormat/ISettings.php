<?php

namespace DRFOO\Interfaces\IFormat;

/**
 * ISettings Interface for DR.FOO-standard Format classes
 * 
 * <p>A Format's settings should contains relational and/or restrictive
 * settings with or without any sub-item specific config item.</p>
 * 
 * <p>You max have a pseudo standard for your setting options in the
 * relations and restrictions, for example: if you have an ID relation
 * you may pass an array('id'=&gt;12) as $relations ctor arg's value in
 * all of your Formats. So it is highly recommended that you specify
 * your own standard for these purposes.</p>
 * 
 * <p>You can access restriction and relation settings on the above way:
 * <code>$ISettingsObj-&gt;restrictions-&gt;Key = 'value';
 * // same as $ISettingsObj-&gt;restrictions['Key'] = 'value';
 * $ISettingsObj-&gt;relations-&gt;Key = 'value';
 * // same as $ISettingsObj-&gt;relations['Key'] = 'value';</code></p>
 * @author prometheus
 *
 */
interface ISettings
{
	/**
	 * Constructor
	 * 
	 * You could initilize the relation and/or restriction settings for
	 * the parent Format.
	 * @param DRFOO\Interfaces\IFormat $format
	 * @param array $relations
	 * @param array $restrictions
	 */
	public function __construct(DRFOO\Interfaces\IFormat $format, $relations = null, $restrictions = null);
	
	/**
	 * Creates a new instance for this class.
	 * 
	 * You could initilize the relation and/or restriction settings for
	 * the parent Format.
	 * @param DRFOO\Interfaces\IFormat $format
	 * @param array $relations
	 * @param array $restrictions
	 * @return ISettings
	 */
	public static function create(\DRFOO\Interfaces\IFormat $format, $relations = null, $restrictions = null);
	
	/**
	 * Sets a new key/value pair as setting item for parent Format assigned to an item
	 * of that Format named in $key. You cannot use the 'relations' and 'restrictions'
	 * names as a name of any Format item or settings item (they are reserved)!
	 * @param string $key
	 * @param mixed|ISettings $value
	 * @return ISettings Returns this instance for chaining.
	 */
	public function set($key, $value);
}