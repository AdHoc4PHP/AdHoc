<?php

/**
 * Description of igninApplication
 *
 * @author prometheus
 */
class SigninApplication extends AdhocApplication
{
	public function __construct($app, $request)
	{
		parent::__construct($app, $request);
	}

	public function run()
	{
		if ($this->auth->isAuthorized())
		{
			$this->response->redirect(AdhocLocale::t($this->config['authorize']['fallbackTarget']));
		}
		else
		{
			$this->response->setContent(parent::run(true));
		}

		$this->response->send();
	}
	
	protected function sanitizeConfig($addOpts=false)
	{
		$cfg = parent::sanitizeConfig($addOpts);
		unset($cfg['smtp']);
		return $cfg;
	}
}

?>