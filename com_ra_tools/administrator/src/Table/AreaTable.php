<?php

/**
 * @package     com_ra_tools.Administrator
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Area table
 *
 * @since  1.5
 */
class AreaTable extends Table {

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   1.0
     */
    public function __construct(DatabaseDriver $db) {
        parent::__construct('#__ra_areas', 'id', $db);
    }

}
