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

namespace Ramblers\Component\Ra_tools\Administrator\View\Group_list;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of mywalks.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView {

    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \JPagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \JObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \JForm
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar() {
        // Suppress menu side panel
        Factory::getApplication()->input->set('hidemainmenu', true);
        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title('Ramblers Groups');

//        $toolbar->standardButton('cancel')
//                ->icon('fa fa-xmark')
//                ->text('Quit')
//                ->task('group_list.cancel')
////                ->onclick('return false')
//                ->listCheck(false);

        $canDo = ContentHelper::getActions('com_ra_tools');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('group_list.add');
        }

        if ($canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                    ->text('JTOOLBAR_CHANGE_STATUS')
                    ->toggleSplit(false)
                    ->icon('icon-ellipsis-h')
                    ->buttonClass('btn btn-action')
                    ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();
        }

        // code from https://docs.joomla.org/J4.x:Joomla_4_Tips_and_Tricks:_Number_of_Records
        $toolbar->standardButton('nrecords')
                ->icon('fa fa-info-circle')
                ->text(number_format($this->pagination->total) . ' Records')
                ->task('')
                ->onclick('return false')
                ->listCheck(false);

        ToolbarHelper::cancel('group_list.cancel', 'JTOOLBAR_CANCEL');
    }

}
