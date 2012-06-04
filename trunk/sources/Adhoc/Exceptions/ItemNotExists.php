<?php

namespace Adhoc\Exceptions;

class ItemNotExists extends \Exception
{
	public function __construct($key)
	{
		parent::__construct('Collection item "'.$key.'" not found!');
	}
}