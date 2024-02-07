<?php

/**
 * @package     Mywalks.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 30/11/23 CB use Factory::getApplication()->loadIdentity()
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Controller for a single area
 *
 * @since  1.6
 */
class ProfileController extends FormController {

    public function showDay() {
        die('Profile controller test');
    }

    public function update() {
        /*
         * Invoked from the menu
         * Find the id of the current user, pass this to the view to be edited
         *
         */
        $user_id = Factory::getApplication()->loadIdentity()->id;
        if ($user_id == 0) {
            JFactory::getApplication()->enqueueMessage("You are not logged in" . $message, 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false));
            return false;
        }
        $this->setRedirect(Route::_('index.php?option=com_ra_tools&view=profile&id=' . $user_id, false));
    }

}
