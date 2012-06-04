<?php

class Foo
{
	protected $bars = array();
	
	public function __destruct()
	{
		var_dump(__METHOD__.' called.');
		var_dump(__CLASS__.'::$bars count is '.count($this->bars));
		// foreach ($this->bars as $i=>$bar) unset($this->bars[$i]); // not working!
		// foreach ($this->bars as $bar) unset($bar); // not working!
		// foreach ($this->bars as &$bar) unset($bar); // not working!
		for ($i=count($this->bars)-1; $i>=0; $i--) unset($this->bars[$i]);
		var_dump(__CLASS__.'::$bars', $this->bars);
	}
	
	public function add(Bar $bar)
	{
		$bar->setId(count($this->bars));
		$this->bars[] = $bar;
	}
	
	public function rem($id)
	{
		unset($this->bars[$id]);
	}
}

class Bar
{
	protected $id = 0;
	
	protected $o;
	
	public function __construct(Foo $o)
	{
		$this->o = $o;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function __destruct()
	{
		var_dump(__METHOD__.' with id '.$this->id);
		$this->o->rem($this->id);
	}
}

$foo = new Foo();
$foo->add(new Bar($foo));
$foo->add(new Bar($foo));
$foo->add(new Bar($foo));
$foo->add(new Bar($foo));
unset($foo);