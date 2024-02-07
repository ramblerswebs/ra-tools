<?php

namespace Ramblers\Component\Ra_tools\Site\View\Programme_day;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {

    protected $day;
    protected $display_type;
    protected $group;
    protected $intro;
    protected $limit;
    protected $menu_id;

    public function display($tpl = null) {

        $app = Factory::getApplication();
        $this->day = Factory::getApplication()->input->getCmd('day', '');
        $this->menu_id = Factory::getApplication()->input->getCmd('Itemid', '');
        $menu = $app->getMenu()->getActive();
        if (is_null($menu)) {
            echo 'Menu params are null<br>';
        } else {
            $menu_params = $menu->getParams();
        }
        if ($this->day == '') {
            $this->day = $menu_params->get('day');
        }
        $this->intro = $menu_params->get('intro');
        $group_type = $menu_params->get('group_type', 'single');
        $this->limit = (int) $menu_params->get('limit');
        $this->display_type = $menu_params->get('display_type', 'simple');
        $this->show_cancelled = $menu_params->get('show_cancelled', '0');
//        var_dump($this->menu_params);
        $params = ComponentHelper::getParams('com_ra_tools');
//        var_dump($params);
//        echo '<br>end of params from component helper<br>';

        if ($group_type == "single") {
            $this->group = $params->get('default_group');
        } else {
            $this->group = $params->get('group_list');
        }

        echo "<h2>" . $this->day . " walks</h2>";
        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');
        return parent::display($tpl);
    }

}
