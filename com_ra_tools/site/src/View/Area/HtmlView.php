<?php

namespace Ramblers\Component\Ra_tools\Site\View\Area;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {

    protected $area;
    protected $nation;
    protected $params;

    public function display($tpl = null) {
        $app = Factory::getApplication();
        $this->area = $app->input->getCmd('code', '');

        return parent::display($tpl);
    }

}
