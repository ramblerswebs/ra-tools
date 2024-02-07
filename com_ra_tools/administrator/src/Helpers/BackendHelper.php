<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Administrator\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Ra_tools component helper.
 *
 * @since  4.0
 */
class BackendHelper {

    /**
     * Build the query for search from the search columns
     *
     * @param	string		$searchWord		Search for this text

     * @param	string		$searchColumns	The columns in the DB to search for
     *
     * @return	string		$query			Append the search to this query
     */
    public static function buildSearchQuery($searchWord, $searchColumns, $query) {
        $db = Factory::getDbo();

        $where = array();

        foreach ($searchColumns as $i => $searchColumn) {
            $where[] = $db->qn($searchColumn) . ' LIKE ' . $db->q('%' . $db->escape($searchWord, true) . '%');
        }

        if (!empty($where)) {
            $query->where('(' . implode(' OR ', $where) . ')');
        }

        return $query;
    }

    public static function getWalkTitle($id) {
        if (empty($id)) {
            // throw an error or ...
            return false;
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('title');
        $query->from('#__mywalks');
        $query->where('id = ' . $id);
        $db->setQuery($query);
        return $db->loadObject();
    }

}
