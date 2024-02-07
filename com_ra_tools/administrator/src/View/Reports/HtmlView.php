<?php

namespace Ramblers\Component\ra_tools\Administrator\View\Reports;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\Tools;

//use Ramblers\Component\ra_tools\Administrator\Helper\ToolsHelper;
class HtmlView extends BaseHtmlView {

    protected $params;

    public function display($tpl = null) {
        $app = Factory::getApplication();
        $this->user = Factory::getApplication()->loadIdentity();

        $this->params = ComponentHelper::getParams('com_ra_tools');

        parent::display($tpl);
    }

}
