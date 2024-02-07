<?php

namespace Ramblers\Component\Ra_tools\Administrator\View\Grouplist;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    public $area_code;
    protected $form;
    protected $item;
    public $objHelper;

    public function display($tpl = null) {
        $this->area_code = Factory::getApplication()->input->getCmd('area', '');
        $this->objHelper = new ToolsHelper;
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // required record is specified by $this->item->id

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar() {
        // Suppress the menu panel on LHS
        Factory::getApplication()->input->set('hidemainmenu', true);
        ToolbarHelper::title('Groups for ' . $this->objHelper->lookupArea($this->area_code));
    }

}
