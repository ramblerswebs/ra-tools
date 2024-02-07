<?php

namespace Ramblers\Component\Ra_tools\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController {

    protected $default_view = 'dashboard';

    public function display($cachable = false, $urlparams = []) {
        return parent::display();
    }

}
