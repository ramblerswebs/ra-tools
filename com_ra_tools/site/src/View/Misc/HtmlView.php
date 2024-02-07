<?php

/**
 * @version     4.0.10
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 17/07/23 CB remove diagnostics
 * 08/01/24 CB canDo
 * 05/02/24 CB prepare document
 */

namespace Ramblers\Component\Ra_tools\Site\View\Misc;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    public $canDo;
    protected $params;
    protected $menu_params;
    protected $objHelper;
    protected $remote_item;
    protected $state;
    protected $title;
    protected $user;

    public function display($tpl = null) {
        echo "<!-- Start of code from " . __FILE__ . ' -->';
        $app = Factory::getApplication();
        $this->user = Factory::getUser();
        // If showing an article from a remote site, parameter will have been passed
        $this->remote_item = $app->input->getWord('remote_item', 'N');
        if ($this->remote_item == 'Y') {
            echo 'Not yet implemented<br>';
            return;
            // Need to get website details from the state
        } else {
            // Save details of parameters to the State
        }

        // Load the component params
        //       $this->params = ComponentHelper::getParams('com_ra_tools');
//        var_dump($this->params);
//        echo '<br>end of params from component helper<br>';
        $this->params = $this->get('State')->get('params');
//        $this->params = $app->getParams();
//        var_dump($this->params);
//        echo '<br>end of params from app<br>';
        $menu = $app->getMenu()->getActive();
        if (is_null($menu)) {
            echo 'Menu params are null<br>';
        } else {
            $this->menu_params = $menu->getParams();
        }
//        var_dump($this->menu_params);
//        $x = $this->params->get('data');
//        var_dump($x);
        $this->objHelper = new ToolsHelper;
        $this->user = $app->loadIdentity();
//        $this->user = $app->getUser();
        /*
          08/01/24 CB neither of these approaches seem to work here
          //        var_dump($this->user);
          echo 'user ' . $this->user->id . '<br>';
          echo 'user name' . $this->user->username . '<br>';
          if ($this->user->id > 0) {
          $this->canDo = $this->objHelper->canDo('com_ra_tools');
          }
         */
        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');
        $this->_prepareDocument();
        return parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return void
     *
     * @throws Exception
     */
    protected function _prepareDocument() {
        $app = Factory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_RA_EVENTS_DEFAULT_PAGE_TITLE'));
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

}
