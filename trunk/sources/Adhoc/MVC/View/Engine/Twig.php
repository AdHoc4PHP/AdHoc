<?php

namespace Adhoc\MVC\View\Engine;

class Twig extends \Adhoc\MVC\View\Engine
{
	/**
	 * @var Twig_Environment
	 */
	protected $environment;
	
	public function __construct()
	{
/*		if (!class_exists('Twig_Loader_Filesystem'))
		{
			require_once(ADHOC_ROOT_DIR.'sources/core/contrib/Twig/Autoloader.php');
			Twig_Autoloader::register();
		}*/
	}
	
	public function __destruct()
	{
		unset($this->representation);
	}
	
	public function attachRepresentation($representation)
	{
		unset($this->environment, $this->template);
		
		$this->environment = new \Twig_Environment(
			new \Twig_Loader_Filesystem($this->view->getWidget()->getPath(false))
		);
		
		$this->environment->addExtension(new Twig\LocaleExtension());
		
		return parent::attachRepresentation($representation);
	}
	
	/**
	 * Renders the view using the attatched representation formula and returns
	 * the output of rendering.
	 * @return string
	 */
	public function render()
	{
		$template = $this->environment->loadTemplate(\Adhoc\Locale::file($this->representation));
		return $template->render((array)$this->view);
	}
}