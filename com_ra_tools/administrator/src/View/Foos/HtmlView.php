<?php

namespace Ramblers\Component\ra_tools\Administrator\View\Foos;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

//use Ramblers\Component\ra_tools\Administrator\Helper\FooHelper;
class HtmlView extends BaseHtmlView {

    protected $state;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null): void {
        $this->items = $this->get('Items');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        if (!count($this->items) && $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
// Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

//        ToolbarHelper::title(Text::_('COM_FOOS_MANAGER_FOOS'), 'address foo');
        $canDo = ContentHelper::getActions('com_ra_tools');
        if ($canDo->get('core.create')) {
            $toolbar->addNew('foo.add');
        }
    }

}
