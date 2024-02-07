<?php

namespace Ramblers\Component\Ra_tools\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class GroupTable extends Table {

    public function __construct(DatabaseDriver $db) {
        $this->typeAlias = 'com_ra_tools.group';

        parent::__construct('#__ra_groups', 'id', $db);
    }

    public function generateAlias() {
        if (empty($this->alias)) {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

}
