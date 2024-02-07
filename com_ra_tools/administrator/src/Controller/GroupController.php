<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 30/11/23 Cb use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

/**
 * Controller for a single group
 *
 * @since  1.6
 */
class GroupController extends FormController {

// These two lines ensure that after editing, control return to group_list, not groups
    protected $view_item = 'group';
    protected $view_list = 'group_list';

    private function areaHeader() {
        return ['nation_id', 'code', 'name', 'details', 'website', 'co_url', 'latitude', 'longitude'];
    }

    public function cancel($key = null) {
        $return = parent::cancel($key);
        $this->setRedirect('index.php?option=com_ra_tools&view=group_list');
        return $return;
    }

    public function save($key = null, $urlVar = null) {
//        Factory::getApplication()->enqueueMessage('Area updated', 'notice');
        $return = parent::save($key, $urlVar);
        $this->setRedirect('index.php?option=com_ra_tools&view=group_list');
        return $return;
    }

    public function unloadAreas() {
        $this->filename = JPATH_ROOT . "/tmp/area-download.csv"; // . (new DateTime())->format('Ymd-His') . ".csv";
        $handle = fopen($this->filename, 'w'); //open file for writing
        If ($handle === false) {
            JFactory::getApplication()->enqueueMessage('Cannot open file: ' . $this->filename, 'error');
            return 0;
        } else {
            echo 'Creating file ' . $this->filename . '<br>';
            $sql = 'SELECT * FROM #__ra_groups ORDER BY code';
            $objHelper = new ToolsHelper;
            $rows = $objHelper->getRows($sql);
            $fields = $this->areaHeader();
            fputcsv($handle, $fields);
            foreach ($rows as $row) {
                $data = array();
                $data[] = $row->nation_id;
                $data[] = $row->code;
//                $data[] = $row->title;
                $data[] = $row->name;
                $data[] = $row->details;
                $data[] = $row->website;
                $data[] = $row->co_url;
                $data[] = $row->latitude;
                $data[] = $row->longitude;
                fputcsv($handle, $data);
            }
            fclose($handle);
            echo '<a href="' . $this->filename . '" class="link-button ' . $this->buttonClass . '">Download CSV</a>';
        }
    }

}
