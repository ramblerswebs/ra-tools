<?php

namespace Ramblers\Component\Ra_tools\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class MiscModel extends BaseDatabaseModel {

    protected $message;

    protected function populateState() {
        $app = Factory::getApplication();

        $this->setState('display_type', $app->input->getWord('display_type'));
        $this->setState('area_list', $app->input->getWord('area_list'));

        $this->setState('page_into', $app->input->getCmd('page_intro'));
        $this->setState('show_area', $app->input->getInt('show_area'));
        $this->setState('programme', $app->input->getInt('programme'));

        $this->setState('area', $app->input->getWord('area'));
        $this->setState('params', $app->getParams());
    }

}
