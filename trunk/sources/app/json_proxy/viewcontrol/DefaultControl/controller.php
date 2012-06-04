<?php
/**
 * Description of DefaultControl
 *
 * @author prometheus
 */
class DefaultControl extends AdhocController
{
	public function __construct($request, $appPath)
	{
		parent::__construct($request, $appPath);
		$this->connection = JsonProxyApplication::getConnection();
	}

	public function defaultAction()
	{
		return json_encode(array('success'=>true, 'data'=>array(array('message'=>'Ez a MicroWork PHP keretrendszer JSON Web Proxy-ja. Proxy verzió: 1.0')), 'totalCount'=>1));
	}
}

?>
