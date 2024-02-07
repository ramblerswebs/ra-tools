<?php

namespace Ramblers\Component\Ra_tools\Administrator\View\Profile;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView {

    protected $form;
    protected $item;

    public function display($tpl = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // required record is specified by $this->item->id

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar() {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolbarHelper::title($isNew ? 'New Profile' : 'Edit Profile', 'address foo');

        ToolbarHelper::apply('profile.apply');
        ToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
    }

}
