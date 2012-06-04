<?php

namespace Adhoc\Exceptions;

class FileNotFound extends \Exception
{
	public function __construct($filePath, $code = 0)
	{
		$rootPath = ADHOC_ROOT_DIR;
		parent::__construct('File not found: '.substr($filePath, strlen($rootPath)), $code);
	}
}