<?php

namespace \Adhoc\Expanders;

class HttpRouter extends \Adhoc\Expander
{
	protected $route;
	
	protected $parts = array();
	
	/**
	 * @throws \UnexpectedValueException
	 */
	public function parseUserQuery()
	{
		$this->route = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
		$parts = array();
		if (!empty($this->route))
		{
			$parts = explode('/', $this->route);
			array_shift($parts);
		}
		
		$app = $this->manager->getApplication();
		$cfg = $app->cfg();
		$this->parts['controller'] = (isset($cfg['defaultController'])? $cfg['defaultController'] : 'DefaultControl');
		if (!$this->controllerExists($this->parts['controller']))
		{
			throw new \UnexpectedValueException('Application\'s default controller "'.$this->parts['controller'].'" not in the path!');
		}
		
		if (count($parts) > 0)
		{
			if ($this->controllerExists($parts[0]))
			{
				require($appPath.'viewcontrol/'.$parts[0].'/controller.php');
				$this->parts['controller'] = array_shift($parts);
			}
			else
			{
				throw new \UnexpectedValueException('Application\'s controller "'.$this->parts['controller'].'" not in the path!');
			}
			
			$this->parts = array_merge($this->parts, $parts);
		}
	}
	
	protected function controllerExists($controller)
	{
		$result = true;
		if (!class_exists($parts[0], false))
		{
			$appPath = $this->manager->getApplication()->getPath();
			if (!file_exists($appPath.'viewcontrol/'.$parts[0].'/controller.php') or !is_readable($appPath.'viewcontrol/'.$parts[0].'/controller.php'))
			{
				$result = false;
			}
		}
		return $result;
	}
}