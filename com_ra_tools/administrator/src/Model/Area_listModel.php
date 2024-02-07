<?php

/**
 * @component   com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 18/07/23 CB left join on nations
 * 21/11/23 CB correct spelling of search_felds
 */

namespace Ramblers\Component\Ra_tools\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
//use Joomla\CMS\Form\Form;
//use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Item Model for a single walk.
 *
 * @since  1.6
 */
class Area_listModel extends ListModel {

    protected $search_fields;

    public function __construct($config = []) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.code',
                'a.name',
                'n.name',
                'a.website',
                'a.co_url',
            );
            $this->search_fields = $config['filter_fields'];
        }
        parent::__construct($config);
    }

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, UPPER(a.code) as code, a.name');
        $query->select("n.name as nation");
        $query->select('a.website,a.co_url');

        $query->from($db->quoteName('#__ra_areas', 'a'));
        $query->leftJoin('#__ra_nations AS n ON n.id = a.nation_id');
        // Filter by search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $query = ToolsHelper::buildSearchQuery($search, $this->search_fields, $query);
            }
        }

        // Add the list ordering clause, defaut to name ASC
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        if ($orderCol == 'n.name') {
            $orderCol = $db->quoteName('n.name') . ' ' . $orderDirn . ', ' . $db->quoteName('a.name');
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage('sql = ' . (string) $query, 'notice');
        }
        return $query;
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc') {
        // List state information.
        parent::populateState($ordering, $direction);
    }

}
