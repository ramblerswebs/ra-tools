<?php

/**
 * @component   com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 24/04/23 CB added dummy prepareTable
 */

namespace Ramblers\Component\Ra_tools\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class AreaModel extends AdminModel {

    public $typeAlias = 'com_ra_tools.area';

    public function getForm($data = [], $loadData = true) {
        $form = $this->loadForm($this->typeAlias, 'area', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData() {
        $app = Factory::getApplication();

        $data = $this->getItem();

        $this->preprocessData($this->typeAlias, $data);

        return $data;
    }

    protected function prepareTable($table) {
        // do not need to call the generic Joomla code
    }

}
