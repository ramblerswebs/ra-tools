<?php

namespace Ramblers\Component\Ra_tools\Site\View\Programme_area;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView {

//    protected $component_params;
    protected $cancelled;
    protected $intro;
    protected $menu_params;
    protected $area;
    protected $display_type;

    public function display($tpl = null) {

        $app = Factory::getApplication();

//        $this->component_params = $app->getParams();
//        var_dump($this->component_params);
//        echo '<br>end of params from app<br>';

        $menu = $app->getMenu()->getActive();
        if (is_null($menu)) {
            echo 'Menu params are null<br>';
        } else {
            $this->menu_params = $menu->getParams();
        }
        var_dump($this->menu_params);
        $this->area = $this->menu_params->get('area');
        $this->cancelled = $this->menu_params->get('cancelled');
        $this->display_type = $this->menu_params->get('display_type');
        $this->intro = $this->menu_params->get('intro');

        $wa = $this->document->getWebAssetManager();
        $wa->useScript('keepalive')
                ->useScript('form.validate');
        return parent::display($tpl);
    }

}
