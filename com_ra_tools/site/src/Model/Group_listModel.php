<?php

/*
 * 17/06/23 CB remove reference to a.hosted
 * 03/09/23 CB use ToolsHelper, not BackendHelper
 */

namespace Ramblers\Component\Ra_tools\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
//use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class Group_listModel extends ListModel {

    protected $filter_fields;

    public function __construct($config = []) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.code',
                'a.name',
                'a.website',
                'a.co_url',
                'areas.name',
            );
            $this->filter_fields = $config['filter_fields'];
        }
        parent::__construct($config);
    }

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, UPPER(a.code) as code, a.name');
        $query->select("areas.name as area");
        $query->select('a.group_type, a.website,a.co_url,a.group_type,a.latitude, a.longitude');

        $query->from('`#__ra_groups` AS a');
        $query->innerJoin('#__ra_areas AS areas ON areas.id = a.area_id');

        // Filter by search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $query = ToolsHelper::buildSearchQuery($search, $this->filter_fields, $query);
            }
        }

        // Add the list ordering clause, default to name ASC
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage('sql = ' . $this->_db->replacePrefix($query), 'notice');
        }
        return $query;
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc') {
        // List state information.
        parent::populateState($ordering, $direction);
    }

}
