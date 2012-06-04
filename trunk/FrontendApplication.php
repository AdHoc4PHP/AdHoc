<?php

/**
 * Description of rontendApplication
 *
 * @author prometheus
 */
class FrontendApplication extends AdhocApplication
{
	public function __construct($app, $request)
	{
		parent::__construct($app, $request);

		//AdhocTwig::init();
	}

	public function beforeRun()
	{
		$cfg = FrontendApplication::cfg();
		if (!isset($cfg['authInfo'])) throw new AdhocAuthException('Unauthorized user!');
	}
	
	public function run()
	{
		try
		{
			$this->response->setContent(parent::run(true));
		}
		catch (AdhocAuthException $e)
		{
			if (!$this->auth->isAuthorized())
			{
				$this->response->redirect(AdhocLocale::t($this->config['authorize']['fallbackTarget']));
			}
			else
			{
				$this->exceptionResponse($e);
			}
		}

		$this->response->send();
	}

	public function getNavigationItems($selected)
	{
		$result = array();
		return $result;
	}

	public function cfg()
	{
		@session_start();
		$result = parent::cfg();
		$result['authInfo'] = $this->auth->getAuthData();
		return $result;
	}
}

?>