<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J4xdemos\Component\Ra_tools\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Ra_tools table
 *
 * @since  1.5
 */
class Ra_toolsTable extends Table {

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   1.0
     */
    public function __construct(DatabaseDriver $db) {
        parent::__construct('#__mywalks', 'id', $db);
    }

    public function store($updateNulls = true) {
        return parent::store($updateNulls);
    }

}
