<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 23/05/23 CB use db->quote for Areas/Groups
 * 06/06/23 CB correct back button for refresh groups
 * 14/06/23 CB re-write refreshGroups
 * 10/07/23 CB add function cancel
 * 17/07/23 CB remove diagnostics
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ra_tools list controller class.
 *
 * @since  1.6
 */
class Group_listController extends AdminController {

    public function cancel($key = null, $urlVar = null) {
        $this->setRedirect('index.php?option=com_ra_tools&view=dashboard');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Group_list', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }

    public function refreshGroups() {
        $db = Factory::getContainer()->get(DatabaseInterface::class);//use Joomla\Database\DatabaseInterface;

$db = Factory::getContainer()->get(DatabaseInterface::class);

        // set to 1 for debugging
        $debug = 1;
        // Read the Organisation feed, update tables #__ra_groups and #__ra_groups as required
        $group_count = 0;
        $group_insert = 0;
        $update_count = 0;

        $display = 0;
        $objHelper = new ToolsHelper;
//        $JsonHelper = new JsonHelper;
//        $objHelper->executeCmd('ALTER TABLE `#__ra_groups` CHANGE `longitude` `longitude` DECIMAL(14,12) NOT NULL DEFAULT '0.000000000'; ');
//            $feedurl = 'https://groups.theramblers.org.uk/?latitude=51.4589653&longitude=-2.52582669&maxpoints=100&dist=30';
        $feedurl = 'https://walks-manager.ramblers.org.uk/api/volunteers/groups?api-key=742d93e8f409bf2b5aec6f64cf6f405e';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $feedurl);
        curl_setopt($ch, CURLOPT_HEADER, false); // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // allow xx seconds for timeout
//	curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_tools"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . "\n");
            print('Http return: ' . $httpCode . "\n");
            echo 'Access failed';
            return;
        }
        $group_list = json_decode($data);
        //       var_dump($group_list);
        // $group_list = $JsonHelper->getJson('group-event', 'groups=' . $group_code););


        $sql = "SELECT id,area_id,code,name,details, website,co_url, latitude, longitude "
                . "FROM #__ra_groups WHERE code='";

        $record_count = 0;
        foreach ($group_list as $item) {
            $record_count++;
            $display = 0;

            if ($item->scope == 'G') {
//                echo "$record_count $item->group_code $item->name<br>";
                $area_id = $objHelper->getAreaCode($item->group_code);
                if ((int) $area_id == 0) {
                    echo "Area not found<br>";
                    $area_id = 1;
                }

                $group_count++;
                Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->set("area_id = " . $db->quote($area_id))
                        ->set("code = " . $db->quote($item->group_code))
                        ->set("name = " . $db->quote($item->name))
                        ->set("details = " . $db->quote($item->description))
                        ->set("website = " . $db->quote($item->external_url))
                        ->set("co_url = " . $db->quote($item->url))
                        ->set("latitude = " . $db->quote((float) $item->latitude))
                        ->set("longitude = " . $db->quote((float) $item->longitude))
                ;

                $sql_lookup = $sql . $item->group_code . "'";
                $row = $objHelper->getItem($sql_lookup);
                if (is_null($row)) {
                    $query->insert('#__ra_groups');
                    $result = $db->setQuery($query)->execute();
//                    echo $query . '<br>';
                    $group_insert++;
                } else {
                    // Matching record has been found
                    $update = 0;
                    /*
                      echo 'Updating name for ' . $row->code . ' from ' . $row->name . ' to ' . $item->name . '<br>';
                      echo 'Updating details for ' . $row->code . ' from ' . $row->details . ' to ' . $item->description . '<br>';
                      echo 'Updating co_url for ' . $row->code . ' from ' . $row->co_url . ' to ' . $item->url . '<br>';
                      echo 'Updating website for ' . $row->code . ' from ' . $row->website . ' to ' . $item->external_url . '<br>';
                      echo 'Updating latitude for ' . $row->code . ' from ' . $row->latitude . ' to ' . $item->latitude . '<br>';    // 52.367166890772
                      echo 'Updating longitude for ' . $row->code . ' from ' . $row->longitude . ' to ' . $item->longitude . '<br>'; // 52.123456789 12
                     */
                    if ($row->area_id <> $area_id) {
                        echo 'Updating area_id for ' . $row->code . ' from ' . $row->area_id . ' to ' . $area_id . '<br>';
                        $update = 1;
                    }
                    if ($row->name <> $item->name) {
                        echo 'Updating name for ' . $row->code . ' from ' . $row->name . ' to ' . $item->name . '<br>';
                        $update = 1;
                    }
                    if ($row->details <> $item->description) {
                        echo 'Updating details for ' . $row->code . ' from ' . $row->details . ' to ' . $item->description . '<br>';
                        $update = 1;
                    }
                    if ($row->co_url <> $item->url) {
                        echo 'Updating co_url for ' . $row->code . ' from ' . $row->co_url . ' to ' . $item->url . '<br>';
                        $update = 1;
                    }
                    if ($row->website <> $item->external_url) {
                        echo 'Updating website for ' . $row->code . ' from ' . $row->website . ' to ' . $item->external_url . '<br>';
                        $update = 1;
                    }
                    if ($row->latitude <> $item->latitude) {
                        echo 'Updating latitude for ' . $row->code . ' from ' . $row->latitude . ' to ' . $item->latitude . '<br>';
                        $update = 1;
                    }
                    if ($row->longitude <> $item->longitude) {
                        echo 'Updating longitude for ' . $row->code . ' from ' . $row->longitude . ' to ' . $item->longitude . '<br>';
                        $update = 1;
                    }
                    if ($update) {
                        $update_count++;
                        $query->update('#__ra_groups')
                                ->where('id=' . $row->id);
//                        echo $query . '<br>';
                        $result = $db->setQuery($query)->execute();
                    }
                }
            }
        }
        $message = $group_count . ' Groups,  ';
        if ($group_insert > 0) {
            $message .= $group_insert . ' records inserted ';
        }
        if ($update_count == 0) {
            $message .= 'No updates necessary';
        } else {
            $message .= $update_count . ' records updated';
        }
        echo '<br>' . $record_count . ' records read<br>';

        if ($debug) {
            echo $message . '<br>';
            $target = 'administrator/index.php?option=com_ra_tools&view=dashboard';
            echo $objHelper->backButton($target);
        } else {
            Factory::getApplication()->enqueueMessage($message, 'notice');
            $this->setRedirect(Route::_('administrator/index.php?option=com_ra_tools&view=group_list', false));
        }
    }

}
