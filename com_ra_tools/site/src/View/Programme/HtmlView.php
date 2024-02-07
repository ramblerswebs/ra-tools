<?php

/*
 * 02/09/23 CB optionally restrict by lookahead_weeks
 * 20/11/23 CB allow display of specified group or area
 */

namespace Ramblers\Component\Ra_tools\Site\View\Programme;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    protected $centre_point;
    protected $show_cancelled = 0;
    protected $display_type;
    protected $group;
    protected $intro;
    protected $limit = 0;
    protected $lookahead_weeks = 0;
    public $objHelper;
    protected $params;
    protected $menu_params;
    protected $radius;
    protected $restrict_walks = 0;
    protected $title;
    protected $user;

    public function display($tpl = null) {

        // Load the component params
        $params = ComponentHelper::getParams('com_ra_tools');
        $app = Factory::getApplication();
        $this->objHelper = new ToolsHelper;
        $menu = $app->getMenu()->getActive();
        if (is_null($menu)) {

        } else {
            $menu_params = $menu->getParams();
        }
        $this->group = Factory::getApplication()->input->getCmd('group', '');
        if ($this->group == '') {
            // we have been called from a menu
            $this->intro = $menu_params->get('intro');
            $this->display_type = $menu_params->get('display_type', 'simple');

            $this->show_cancelled = $menu_params->get('show_cancelled', '0');
            $group_type = $menu_params->get('group_type', 'single');
            if ($group_type == "single") {
                $this->group = $params->get('default_group');
            } elseif ($group_type == "specified") {
                $this->group = $menu_params->get('code');
            } else {
                $this->group = $params->get('group_list');
            }
            $this->restrict_walks = $menu_params->get('restrict_walks', '0');
            if ($this->restrict_walks == 1) {
                $this->limit = $menu_params->get('limit', '100');
            } elseif ($this->restrict_walks == 2) {
                $this->lookahead_weeks = $menu_params->get('lookahead_weeks', '12');
            }
            // parameters for view radius
            $this->radius = $menu_params->get('radius', '0');
            $this->centre_point = $menu_params->get('centre_point', '');
        } else {
            // if called from frop_lis, layout will have been set to radius
            $layout = Factory::getApplication()->input->getCmd('layout', '');

            // called from neighbouring groups etc
            // get the defaults from the component parameters
            $this->intro = $params->get('intro');
            $this->display_type = $params->get('display_type', 'simple');
            $this->limit = (int) $params->get('limit');

            $title = 'Walks for ';
            if (strlen($this->group) == 2) {
                // Area
                $title .= $objHelper->lookupArea($this->group);
                $this->group = $this->objHelper->expandArea($this->group);
            } else {
                $title .= $this->objHelper->lookupGroup($this->group);
            }
            $layout = Factory::getApplication()->input->getCmd('layout', '');
            if ($layout == 'radius') {
                $this->centre_point = $this->group;
                $this->radius = 30;
            }
        }
//        var_dump($this->params);
        $this->user = Factory::getApplication()->loadIdentity();

        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');

        echo "<h2>" . $title . "</h2>";
        return parent::display($tpl);
    }

}
