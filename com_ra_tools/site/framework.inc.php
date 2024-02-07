<?php

// Initialize Joomla framework
const _JEXEC = 1;

const JDEBUG = 0;

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php')) {
    require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(dirname(__FILE__)));
    require_once JPATH_BASE . '/includes/defines.php';

    // Get the framework.
    if (file_exists(JPATH_LIBRARIES . '//bootstrap.php')) {
        require_once JPATH_LIBRARIES . '//bootstrap.php';
    } else {
        require_once JPATH_LIBRARIES . '/import.php';
        // Import necessary classes not handled by the autoloaders
        jimport('joomla.application.component.helper');
        // Force library to be in JError legacy mode
        JError::$legacy = true;
    }
    if (!defined('CRLF')) {
        define('CRLF', "\r\n");
    }
    // Bootstrap the CMS libraries.
    //require_once JPATH_LIBRARIES.'//bootstrap.php';
}
