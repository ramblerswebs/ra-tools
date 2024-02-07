<?php

/**
 * @component   com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
class Group_listModel extends ListModel {

    protected $search_felds;

    public function __construct($config = []) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'g.code',
                'g.name',
                'a.name',
                'g.website',
                'g.co_url',
            );
            $this->search_felds = $config['filter_fields'];
        }
        parent::__construct($config);
    }

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('g.id, UPPER(g.code) as code, g.name');
        $query->select('g.website,g.co_url');
        $query->select('a.name as area_name');

//        $query->select(
//                $db->quoteName(['id', 'code', 'name', 'website', 'co_url'])
//        );
        $query->from($db->quoteName('#__ra_groups', 'g'));
        $query->innerJoin('#__ra_areas AS a ON a.id = g.area_id');
        // Filter by search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('g.id = ' . (int) substr($search, 3));
            } else {
                $query = ToolsHelper::buildSearchQuery($search, $this->search_felds, $query);
            }
        }

        // Add the list ordering clause, defaut to name ASC
        $orderCol = $this->state->get('list.ordering', 'g.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        if ($orderCol == 'n.name') {
            $orderCol = $db->quoteName('n.name') . ' ' . $orderDirn . ', ' . $db->quoteName('g.name');
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage($this->_db->replacePrefix($query), 'notice');
        }
        return $query;
    }

    protected function populateState($ordering = 'g.name', $direction = 'asc') {
        // List state information
        parent::populateState($ordering, $direction);
    }

}
