<?php

/****************************************************************************/
/*                                                                          */
/* YOU MAY WISH TO MODIFY OR REMOVE THE FOLLOWING LINES WHICH SET DEFAULTS  */
/*                                                                          */
/****************************************************************************/

// Sets the default charset so that setCharset() is not needed elsewhere
Swift_Preferences::getInstance()->setCharset('utf-8');

// Without these lines the default caching mechanism is "array" but this uses
// a lot of memory.
// If possible, use a disk cache to enable attaching large attachments etc
// ORIGINAL: if (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()))
if (is_writable(realpath(ADHOC_ROOT_DIR.'_tmp')))
{
  Swift_Preferences::getInstance()
    -> setTempDir(ADHOC_ROOT_DIR.'_tmp')
    -> setCacheType('disk');
}
