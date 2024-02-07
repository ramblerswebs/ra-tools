<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

/**
 * Description of GrouplistController
 *
 * @author charles
 */
class GrouplistController extends FormController {

    public function cancel($key = null) {
        die('AAAgh');
        $return = parent::cancel($key);
        $this->setRedirect('index.php?option=com_ra_tools&view=area_list');
        return $return;
    }

}
