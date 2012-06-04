<?php

namespace Adhoc;

abstract class Expander
{
	/**
	 * 
	 * @var ExpandManager
	 */
	protected $manager;
	
	public function __construct(ExpandManager $man)
	{
		$this->manager = $man;
	}
}