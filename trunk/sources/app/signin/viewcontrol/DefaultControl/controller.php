<?php
/**
 * Description of DefaultControl
 *
 * @author prometheus
 */
class DefaultControl extends AdhocController
{
	public function __construct(AdhocApplication $app)
	{
		parent::__construct($app);
		
		$this->view = new AdhocView($this, new AdhocView_Engine_Twig());
	}
	
	public function defaultAction()
	{
		$error = FALSE;
		
		if (!$this->application->getAuth()->isAuthorized()) $this->auth();
		
		if (isset($_POST['userid']) and isset($_POST['password']))
		{
			$error = TRUE;
		}
		
		$data = array_merge(
			array(
				'cfg'		=> $this->application->cfg(),
				'cfgJson'	=> json_encode($this->application->cfg(true))
			),
			array(
				'error'		=> $error
			)
		);
		foreach ($data as $k=>$v) $this->view[$k] = $v;
		$this->view->getEngine()->attachRepresentation('DefaultControl/default.tpl.xhtml');

		return $this->view->getEngine()->render();
	}

	public function SignoffAction()
	{
		$cfg = $this->application->cfg();
		$this->application->getAuth()->logout(AdhocLocale::t($cfg['appRoot']));
	}

	protected function auth()
	{
		$cfg = $this->application->cfg();
		$userId = (isset($_POST['userid'])? $_POST['userid'] : '');
		$password = (isset($_POST['password'])? $_POST['password'] : '');
		if ($this->application->getAuth()->auth($userId, $password))
		{
			$this->application->response->redirect(AdhocLocale::t($cfg['authorize']['fallbackTarget']));
			throw new AdhocFallbackException();
		}
	}
}

?>
