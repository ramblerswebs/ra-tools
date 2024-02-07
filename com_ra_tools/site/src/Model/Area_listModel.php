<?php

/*
 * 24/07/23 CN include latitude & longitude
 * 21/08/23 use db->replacePrefix, only show sql if JDEBUG
 * 02/09/23 CB dont use BackendHelper
 * 17/09/23 CB allow selection by Nation
 * 13/10/23 CB allow selection by cluster
 * 16/10/23 CB Clusters, Chair email
 */

namespace Ramblers\Component\Ra_tools\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
//use Joomla\CMS\Form\Form;
//use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class Area_listModel extends ListModel {

    protected $filter_fields;

    public function __construct($config = []) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.code',
                'a.name',
                'a.cluster',
                'n.name',
                'c.name',
                'a.website',
                'co_url',
            );
            $this->filter_fields = $config['filter_fields'];
        }
        parent::__construct($config);
    }

    protected function getListQuery() {
        $nation_id = Factory::getApplication()->input->getCmd('nation', '0');
        $cluster = strtoupper(Factory::getApplication()->input->getCmd('cluster', ''));

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, a.code, a.name,a.website,a.co_url, a.nation_id, a.cluster');
        $query->select("n.name AS nation");
        $query->select("a.chair_id, c.name as chair");
        $query->select('a.latitude,a.longitude');

        $query->from($db->quoteName('#__ra_areas', 'a'));
        $query->innerJoin('#__ra_nations AS n ON n.id = a.nation_id');
        $query->leftJoin('#__contact_details AS c ON c.id = a.chair_id');
        // Filter by search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $query = ToolsHelper::buildSearchQuery($search, $this->filter_fields, $query);
            }
        }

        if ($nation_id == 0) {
            if ($cluster != '') {
                if ($cluster == 'nk') {
                    $query->where('a.cluster=""');
                } else {
                    $query->where('a.cluster=' . $this->_db->quote($cluster));
                }
            }
        } else {
            $query->where('a.nation_id =' . $nation_id);
        }
        // Add the list ordering clause, default to name ASC
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        if ($orderCol == 'n.name') {
            $orderCol = $db->quoteName('n.name') . ' ' . $orderDirn . ', ' . $db->quoteName('a.name');
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage('sql = ' . $this->_db->replacePrefix($query), 'notice');
        }
//        die('sql = ' . $this->_db->replacePrefix($query));
        return $query;
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc') {
        // List state information.
        parent::populateState($ordering, $direction);
    }

}
