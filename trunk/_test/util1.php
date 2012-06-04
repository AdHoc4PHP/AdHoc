<?php
require('../sources/Adhoc/Util.php');

use \Adhoc\Util as Util;

if (Util::version('5.1') > Util::version('5.0.1')) echo '5.1 > 5.0.1 and ';
if (Util::version('1.0-rc1') < Util::version('1.1-rc2')) echo '1.0-rc1 < 1.1-rc2 and ';
if (Util::version('1.0-rc1') < Util::version('1.0-rc2')) echo '1.0-rc1 < 1.0-rc2 and ';
if (Util::version('2.1') > Util::version('2.1b')) echo '2.1 > 2.1b';