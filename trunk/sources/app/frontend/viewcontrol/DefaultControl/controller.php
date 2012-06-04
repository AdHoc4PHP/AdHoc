<?php
/**
 * Description of DefaultControl
 *
 * @author prometheus
 */
class DefaultControl extends AdhocController
{
	protected $role = 'member';
	
	public function defaultAction()
	{
		$twig = AdhocTwig::create($this->appPath);

		$data = array();
		$data['sidebarItems'] = $this->application->getNavigationItems('');
		$data['cfg'] = $this->application->cfg();

		$tpl = $twig->loadTemplate(AdhocLocale::file('DefaultControl/default.tpl.xhtml'));
		return $tpl->render(
			$data
		);
	}
}

?>
