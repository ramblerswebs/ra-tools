<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 12/06/23 CB add refreshAreas
 * 14/06/23 show group location
 * 10/07/23 CB add function cancel
 * 17/07/23 CB remove diagnostics
 * 17/09/23 CB add SW Scotland
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Database\DatabaseInterface;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

/**
 * Ra_tools list controller class.
 *
 * @since  1.6
 */
class Area_listController extends AdminController {

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
    public function getModel($name = 'Area_list', $prefix = 'Administrator', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }

    public function refreshAreas() {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // set to 1 for debugging
        $debug = 1;
        // Read the Organisation feed, update tables #__ra_areas and #__ra_groups as required
        $area_count = 0;
        $area_insert = 0;
        $update_count = 0;

        $display = 0;
        $objHelper = new ToolsHelper;
        $JsonHelper = new JsonHelper;

//        $objHelper->executeCmd('DELETE FROM #__ra_areas WHERE id<10');
//            $feedurl = 'http://www.ramblers.org.uk/api/lbs/groups/';
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
        $area_list = json_decode($data);
        //       var_dump($area_list);
        // $area_list = $JsonHelper->getJson('group-event', 'groups=' . $group_code););


        $sql_area = "SELECT id,code,name,details, website,co_url, latitude, longitude "
                . "FROM #__ra_areas WHERE code='";

        $record_count = 0;
        foreach ($area_list as $item) {
            $record_count++;
            $display = 0;

            if ($item->scope == 'A') {
//                echo "$record_count $item->group_code $item->name<br>";
                $area_count++;
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true);
                $query->set("code = " . $db->quote($item->group_code))
                        ->set("name = " . $db->quote($item->name))
                        ->set("details = " . $db->quote($item->description))
                        ->set("website = " . $db->quote($item->external_url))
                        ->set("co_url = " . $db->quote($item->url))
                        ->set("latitude = " . $db->quote((float) $item->latitude))
                        ->set("longitude = " . $db->quote((float) $item->longitude))
                ;

                $sql = $sql_area . $item->group_code . "'";

                if ($debug) {
//                echo $sql . '<br>';
                }
                $row = $objHelper->getItem($sql);
                if (is_null($row)) {
                    $query->insert('#__ra_areas');
                    $result = $db->setQuery($query)->execute();
//                    echo $query . '<br>';
                    $area_insert++;
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
                    if ($row->name <> $item->name) {
                        echo 'Updating name for ' . $row->code . ' from ' . $row->name . ' to ' . $item->name . '<br>';
                        $update = 1;
                    }
                    if ($row->details <> $item->description) {
                        echo 'Updating description for ' . $row->code . ' from ' . $row->details . ' to ' . $item->description . '<br>';
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
//                        echo $item->group_code . ': ' . 'Updating longitude for row ' . $row->id . ' from ' . $row->longitude . ' to ' . $item->longitude . '<br>';
                        echo 'Updating longitude for ' . $row->code . ' from ' . $row->longitude . ' to ' . $item->longitude . '<br>';
                        $update = 1;
                    }
                    if ($update) {
                        $update_count++;
                        $query->update('#__ra_areas')
                                ->where('id=' . $row->id);
//                        echo $query . '<br>';
                        $result = $db->setQuery($query)->execute();
                    }
                }
            }
        }
        $message = $area_count . ' Areas,  ';
        if ($area_insert > 0) {
            $message .= $group_insert . ' records inserted ';
        }
        if ($update_count == 0) {
            $message .= 'No updates necessary';
        } else {
            $message .= $update_count . ' records updated';
        }
        echo '<br>' . $record_count . ' records read<br>';

        $update_sql = "UPDATE `#__ra_areas` set nation_id = 2 WHERE code in ('CY','LB','SC','SL','GP','CF','RB','WS');";
        $objHelper->executeCmd($update_sql);
        $update_sql = "UPDATE `#__ra_areas` set nation_id = 3 WHERE code in ('CA','CE','GG','LW','PE','SW');";
        $objHelper->executeCmd($update_sql);

        if ($debug) {
            echo $message . '<br>';
            $target = 'administrator/index.php?option=com_ra_tools&view=dashboard';
            echo $objHelper->backButton($target);
        } else {
            Factory::getApplication()->enqueueMessage($message, 'notice');
            $this->setRedirect(Route::_('administrator/index.php?option=com_ra_tools&view=area_list', false));
        }
    }

}
