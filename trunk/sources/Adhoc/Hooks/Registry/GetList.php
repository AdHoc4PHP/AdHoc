<?php

namespace Adhoc\Hooks\Registry;

/**
 * @author prometheus
 */
class GetList extends \Adhoc\Hook
{
	protected $data = array();
	
	/**
	 * Returns with the parent Registry trap's own datalist.
	 *
	 * @return array
	 */
	public function &__invoke()
	{
		return $this->data;
	}
}