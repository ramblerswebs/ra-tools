<?php

/**
 * @version     1.2.0
 * @package     com_ra_wf (Ramblers Walks Follow)
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * This view could be adopted to replace view reports_area and report_group. Some
 * work started by offering different options in the file default.xl, but these were
 * then commented out again. It is possible to invoke a different template file
 * (e.g default_area.php or defaul_group.php
 * 05/04/21 CB remove reference to Helper::ShowMenu
 * 23/05/23 CB version 4
 */

namespace Ramblers\Component\Ra_tools\Site\View\Reports;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ramblers detail view
 */
class HtmlView extends BaseHtmlView {

    protected $code;
    protected $menu_params;
    protected $params;
    protected $mode;
    protected $opt;
    protected $report_type;
    protected $scope;
    protected $scope_desc;

    /**
     * Display the view
     */
    public function display($tpl = null) {
//        echo "Start of code from " . __FILE__ . '<br>';
        // Load the component params
        $this->params = ComponentHelper::getParams('com_ra_tools');
        $app = Factory::getApplication();
//        $user = Factory::getApplication()->getIdentity();
        $menu = $app->getMenu()->getActive();
        if (is_null($menu)) {

        } else {
            $this->menu_params = $menu->getParams();
        }
        // Find from which menu we have been invoked
        $menu_id = $app->input->getInt('Itemid', '0');
        Factory::getApplication()->setUserState('com_ra_tools.menu_id', $menu_id);

        /*
          // Throw exeption if errors
          if (count($errors = $this->get('Errors')))
          {
          throw new Exception(implode("\n", $errors));
          }

         */
//        $this->loadTemplateHeader();
        $this->prepareDocument();

        parent::display();
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareDocument() {
        $app = Factory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('Reports'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

    protected function buildTarget($row, $col) {
        // builds the parameters from the chosen option for the drilldown report
        $target = 'index.php?option=com_ra_wf&view=reports_matrix&tmpl=component';
        $target .= '&mode=A&opt=' . $this->area . '&scope=' . $this->scope;
        $target .= '&row=' . $row . '&col=' . $col;
        return $target;
    }

}
