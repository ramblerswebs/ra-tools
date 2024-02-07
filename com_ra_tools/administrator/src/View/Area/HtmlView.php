<?php

/**
 * @package     Mywalks.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 10/07/23 CB add Cancel button
 * 17/07/23 Suppress menu side panel
 */

namespace Ramblers\Component\Ra_tools\Administrator\View\Area;

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
        // Suppress menu side panel
        Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolbarHelper::title($isNew ? 'New Area' : 'Edit Area', 'address foo');

        ToolbarHelper::apply('area.apply');
        ToolbarHelper::cancel('area.cancel', 'JTOOLBAR_CLOSE');
    }

}
