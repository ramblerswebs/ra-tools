<?php

namespace Ramblers\Component\Ra_tools\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class FoosModel extends ListModel {

    public function __construct($config = []) {
        parent::__construct($config);
    }

    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $db->quoteName(['id', 'name'])
        );
        $query->from($db->quoteName('#__ra_areas'));

        return $query;
    }

}
