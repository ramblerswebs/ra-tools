<?php

/**
 * @version     1.0.0
 * @package     com_ra_tools_1.2.0
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie Bigley <webmaster@bigley.me.uk> - https://www.developer-url.com
 * 30/11/22 CB created from com ramblers
 */

namespace Ramblers\Component\Ra_tools\Site\Model;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Ramblers model
 */
class Ra_toolsModelProfile extends AdminModel {

    /**
     * @var		string	The prefix to use with controller messages
     * @since	1.6
     */
    protected $text_prefix = 'COM_RA_TOOLS';
    protected $fields;

    /**
     * Method to get the record form.
     *
     * @param	array	$data		An optional array of data for the form to interrogate
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not
     * @return	JForm	A JForm object on success, false on failure
     * Note that this will use the corresponding Model to pre-populate the form fields
     */
    public function getForm($data = array(), $loadData = true) {
// Get the form src/forms/profile.xml
        try {
            $form = $this->loadForm('com_ra_tools.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));
        } catch (Exception $ex) {
            $code = $ec->getCode;
            JError::raiseError($code ? $code : 500, $ex->getMessage());
        }

        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Returns a reference to the a Table object, always creating it
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional
     * @param	array	Configuration array for model. Optional
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'profile', $prefix = 'Ra_toolsTable', $config = array()) {
        try {
            $objTable = JTable::getInstance($type, $prefix, $config);
            return $objTable;
        } catch (Exception $ex) {
            $code = $ec->getCode;
            JError::raiseError($code ? $code : 500, $ex->getMessage());
        }
        return false;
    }

    /**
     * Method to get the data that should be injected in the form
     *
     * @return	mixed	The data for the form
     * @since	1.6
     */
    protected function loadFormData() {
// Check the session for previously entered form data
        $data = JFactory::getApplication()->getUserState('com_ra_tools.profile.data', array());
        if (empty($data)) {
            $user_id = Factory::getApplication()->loadIdentity()->id;
            if ($user_id == 0) {
                die('Insufficient access');
            }
            $data = $this->getItem($user_id);
//            echo "Model/loadFormData: <br>";
//            var_dump($data);
//            die('loadFormData');
        }

        return $data;
    }

}
