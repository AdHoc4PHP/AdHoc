<?php

namespace Adhoc\MVC;

/**
 * View implements a representable set of associated data. Any view can
 * hold associated values like an array and has the ability to render itself
 * in many formats (representations) using {@link View\Engine engines}.
 * @author prometheus
 */
class View extends \ArrayObject
{
	/**
	 * Used engine object to manage and create the output.
	 * @var View_Engine
	 */
	protected $engine;
	
	/**
	 * Widget that owned this view.
	 * @var Application
	 */
	protected $widget;
	
	/**
	 * Constructs this view.
	 * @param Widget $widget
	 * @param View\Engine $engine
	 * @param array $defaults Default associated values for this view.
	 */
	public function __construct(Widget $widget, View\Engine $engine, $defaults=array())
	{
		$this->widget = $widget;
		$this->engine = $engine;
		$this->engine->applyView($this);
		
		parent::__construct($defaults);
	}
	
	/**
	 * Changes the used engine to another one.
	 * @param View\Engine $toEngine
	 * @param bool $unsetOld If <code>false</code>, this method returns the
	 * previously used engine - otherwise returns to new one and unsets the old
	 * @return View\Engine
	 */
	public function changeEngine(View\Engine $toEngine, $unsetOld=true)
	{
		$result = $this->engine;
		if ($unsetOld)
		{
			unset($this->engine);
			$result = $toEngine;
		}
		$this->engine = $toEngine;
		$this->engine->applyView($this);
		
		return $result;
	}
	
	/**
	 * Returns the used engine.
	 * @return View\Engine
	 */
	public function getEngine()
	{
		return $this->engine;
	}
	
	/**
	 * Returns the used widget.
	 * @return Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}
}