<?php

namespace Adhoc\MVC\View;

abstract class Engine
{
	/**
	 * The owner view of this engine.
	 * @var \Adhoc\MVC\View
	 */
	protected $view;
	
	/**
	 * An attached representation for the view which in- and specifies the
	 * engine's output format. For example a template file's path, a preset
	 * object, etc. 
	 * @var mixed
	 */
	protected $representation;
	
	/**
	 * Applies a view to the engine. If you don't force this with the
	 * <code>$force = true</code> argument an exception should throw if you
	 * try to do this twice or more.
	 * @param \Adhoc\MVC\View $view
	 * @param bool $force
	 * @return Engine
	 * @throws EngineApplyViewException
	 */
	public function applyView(\Adhoc\MVC\View $view, $force=false)
	{
		if (!$force and !is_null($this->view))
		{
			throw new EngineApplyViewException();
		}
		$this->view = $view;
		return $this;
	}
	
	/**
	 * Gets the attached view.
	 * @return \Adhoc\MVC\View
	 */
	public function getView()
	{
		return $this->view;
	}
	
	/**
	 * Returns <code>true</code> if view is available and
	 * <code>false</code> otherwise.
	 * @return bool
	 */
	public function hasView()
	{
		return isset($this->view);
	}
	
	/**
	 * Attaches a representation formula.
	 * @param $representation
	 * @return Engine
	 */
	public function attachRepresentation($representation)
	{
		$this->representation = $representation;
		return $this;
	}
	
	/**
	 * Gets the representation formula.
	 * @return mixed
	 */
	public function getRepresentation()
	{
		return $this->representation;
	}
	
	/**
	 * Returns <code>true</code> if representation is available,
	 * <code>false</code> otherwise.
	 * @return bool
	 */
	public function hasRepresentation()
	{
		return isset($this->representation);
	}
	
	/**
	 * Renders the view using the attatched representation formula and returns
	 * the output of rendering.
	 * @return mixed
	 */
	public function render()
	{
		
	}
}