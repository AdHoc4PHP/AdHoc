<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class Has extends \Adhoc\Hook
{
	/**
	 * Returns a flatten collection of existing Registry traps' data items from top
	 * to bottom. Bubbling used (if any key has null value then if it is an
	 * existing key with the same name in one of the lower levels, lower leveled
	 * value WILL overwrites the higher leveled null value - if a key has not
	 * null value in the higher level then use that value in the result for that
	 * key instead).
	 *
	 * @return object Simple object, properties are Registry keys and those values
	 * are values of those keys generated on the way written above
	 */
	public function &__invoke()
	{
		$result = new stdClass();
		Adhoc::eachTrap('Registry',
			function ($trap) use (&$result)
			{
				$data =& $trap->GetList();
				foreach ($data as $k=>$v)
				{
					if (!isset($result->$k) and isset($v))
					{
						$result->$k = $v;
					}
				}
			}
		);
		
		return $result;
	}
}