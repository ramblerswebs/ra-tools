<?php

namespace Ramblers\Component\Ra_tools\Site\View\Profile;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {

    protected $user;
    protected $form;
    protected $item;

    public function display($tpl = null) {
        $layout = Factory::getApplication()->input->getCmd('layout', '');
        die("Layout $layout<br>");
        $this->user = Factory::getUser();
        if ($layout == 'register') {
            if ($this->user->id == 0) {
                //return Error::raiseWarning(404, "Please login to gain access to this function");
//            throw new \Exception('Please login to gain access to this function', 404);
                echo '<h4>Please login to gain access to this function</h4>';
                return false;
            }
        } else {  // Self registering
            if ($this->user->id > 0) {
                //return Error::raiseWarning(404, "Please login to gain access to this function");
//            throw new \Exception('Please login to gain access to this function', 404);
                echo '<h4>PYou are already Registered</h4>';
                return false;
            }
        }
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        return parent::display($tpl);
    }

}
