<?php

require('../bootup.php');
require('JsonProxyApplication.php');

define('ADHOC_APP_NAME', 'json_proxy');

AdhocLocale::init(ADHOC_ROOT_DIR.'sources/app/'.ADHOC_APP_NAME.'/');

// Here you can specify your custom AdhocRequest class!
AdhocRequest::init($_REQUEST);

// Executes the application
AdhocGlobals::setObject('DefaultApplication', new JsonProxyApplication(ADHOC_APP_NAME, AdhocRequest::get()));
AdhocGlobals::get('DefaultApplication')->run();
AdhocGlobals::free('DefaultApplication');

session_write_close();

?>