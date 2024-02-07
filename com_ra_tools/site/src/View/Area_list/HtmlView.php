<?php

/*
 * 17/09/23 CB allow selection by Nation
 * 16/10/23 CB Clusters, Chair email
 */

namespace Ramblers\Component\Ra_tools\Site\View\Area_list;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    protected $items;
    protected $cluster;
    protected $nation;
    protected $pagination;
    protected $params;
    protected $state;
    public $filterForm;
    protected $activeFilters;

    public function display($tpl = null) {
        $app = Factory::getApplication();
        $active = $app->getMenu()->getActive();
        $this->params = $active->getParams();
//        var_dump($this->params);
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');
        $this->_prepareDocument();

        $this->cluster = strtoupper($app->input->getCmd('cluster', ''));
        $nation_id = $app->input->getCmd('nation', '0');
        if ($nation_id == 0) {
            $this->nation = '';
        } else {
            if ($nation_id == 1) {
                $this->nation = 'England';
            } elseif ($nation_id == 2) {
                $this->nation = 'Scotland';
            } elseif ($nation_id == 3) {
                $this->nation = 'Wales';
            }
        }
//        echo "cluster $this->cluster<br>";

        return parent::display($tpl);
    }

    public function generateEmail($contact_id, $name, $website) {
        $objHelper = new ToolsHelper;
        if ($contact_id == "0") {

            if (substr($website, 0, 27) == 'https://www.ramblers.org.uk') {
                return '';
                $area_name = strtolower(substr($website, 28)) . 'ramblers.org.uk';
            } else {
                //return substr($website, 0, 27);
                $position = strpos($website, 'ramblers');
                $area_name = strtolower(substr($website, 8));
                if (substr($area_name, 0, 4) == 'www.') {
                    $area_name = substr($area_name, 4);
                }
                if (substr($area_name, -1) == '/') {
                    $area_name = substr($area_name, 0, -1);
                }
            }
//            return $area_name;
            $chair = 'chairman@' . $area_name;
            return '<a href="mailto:' . $chair . '">' . $chair . '</a>';
            return $chair;
        } else {
            $link = 'index.php?option=com_contact&view=contact&id=' . $contact_id;
            return $objHelper->buildLink($link, $name, True, "");
            //return $link;
        }
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
