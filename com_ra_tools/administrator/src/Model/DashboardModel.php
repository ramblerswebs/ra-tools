<?php

/**
 * @component   com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class Ra_toolsModel extends BaseDatabaseModel {

    protected $message;

    public function getMsg() {
        if (!isset($this->message)) {
            $this->message = 'Hello Foo!';
        }

        return $this->message;
    }

}
