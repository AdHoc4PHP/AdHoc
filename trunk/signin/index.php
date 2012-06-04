<?php

require('../bootup.php');
require('SigninApplication.php');

define('ADHOC_APP_NAME', 'signin');

AdhocLocale::init(ADHOC_ROOT_DIR.'sources/app/'.ADHOC_APP_NAME.'/');

// Here you can specify your custom AdhocRequest class!
AdhocRequest::init($_REQUEST);

// Executes the application
AdhocGlobals::setObject('DefaultApplication', new SigninApplication(ADHOC_APP_NAME, AdhocRequest::get()));
AdhocGlobals::get('DefaultApplication')->run();
AdhocGlobals::free('DefaultApplication');

session_write_close();

?>