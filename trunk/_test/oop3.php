<?php

namespace Foo
{
	class Bar
	{
		public function t()
		{
			var_dump(get_class($this)); // Foo\Bar
		}
	}
}

namespace
{
	$bar = new Foo\Bar();
	$bar->t();
	var_dump(get_class($bar)); // Foo\Bar
}