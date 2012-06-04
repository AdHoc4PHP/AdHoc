<?php

require('bootup.php');
require('FrontendApplication.php');

define('ADHOC_APP_NAME', 'frontend');

AdhocLocale::init(ADHOC_ROOT_DIR.'sources/app/'.ADHOC_APP_NAME.'/');

// Here you can specify your custom AdhocRequest class!
AdhocRequest::init($_REQUEST);

// Executes the application
AdhocGlobals::setObject('DefaultApplication', new FrontendApplication(ADHOC_APP_NAME, AdhocRequest::get()));
AdhocGlobals::get('DefaultApplication')->run();
AdhocGlobals::free('DefaultApplication');

session_write_close();

?>