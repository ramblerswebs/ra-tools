<?php

/**
 * @package     Mywalks.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 26/05/23 CB created
 * 27/05/23 CB show headings for CSV, clean phone number, show shape, meeting
 * 29/05/23 CB add exportGwen
 * 30/05/23 CB sort reports by group/date/title
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 * 09/10/24 CB delete walksCsv
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
//use Joomla\CMS\Router\Route;
//use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Joomla\Database\DatabaseInterface;

/**
 * Controller for a single area
 *
 * @since  1.6
 */
class AreaController extends FormController {

    private $show_message;
    private $walksfound = 0;
    private $walksupdated = 0;
    private $walkscreated = 0;

    function countWalks() {
        $app = Factory::getApplication();

        $callback = $app->getUserState('com_ra_wf.callback', 'walk_list');
        $code = $app->input->getCmd('code', 'NAT');

        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();

        echo "<h2>Total walks by Group";
        if (strlen($code) == 2) {
            echo ' for ' . $this->objHelper->lookupArea($code);
        }
        echo "</h2>";

        $objTable->add_header("Home Group, Guest Group, Count");
        $sql = "SELECT organising_group, group_code, COUNT(id) as 'Num' ";
        $sql .= 'FROM #__ra_walks ';
        if (strlen($code) == 2) {
            $sql .= 'WHERE ((organising_group like "' . $code . '%") ';
            $sql .= 'OR (group_code like "' . $code . '%")) ';
        }
        $sql .= 'GROUP BY organising_group,group_code ';
        $sql .= 'ORDER BY organising_group,group_code ';
//        echo $sql;
        $rows = $objHelper->getRows($sql);
        $total_walks = 0;
        foreach ($rows as $row) {
            $objTable->add_item($row->organising_group);
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->Num);
            $total_walks += $row->Num;
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo number_format($total_walks) . ' Walks<br>';
        // Depending on from where it was invoked, control will be passed back either to reports or to reports_area

        echo $objHelper->backButton('index.php?option=com_ra_wf&view=' . $callback);
    }

    private function debug($code) {
        $objHelper = new ToolsHelper;
        $sql = "SELECT COUNT(id) FROM #__ra_walks  ";
        $sql .= "WHERE group_code = '" . $row->code . "'";
//            echo $sql;
        $count_walks = $objHelper->getValue($sql);
        echo $sql . ' ' . $count_walks . '<br>';
    }

    private function doesWalkExist($walkid) {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true);

// Select everything from the table that matches the walk id.
        $query->select(' a.*')
                ->from('`#__ra_walks` AS a')
                ->where('a.walk_id = ' . (int) $walkid)
        ;

        $db->setQuery((string) $query);

        $results = $db->loadObjectList();

// See if we got anything back - ie does the walk exist
        if (count($results) == 0) {
            return false;
        } else {
            return true;
        }
    }

// doesWalkExist ($walkid)

    public function getJson($type, $param, $count = 'N') {
        $url = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=';
        $url .= $type;
        $url .= '&api-key=742d93e8f409bf2b5aec6f64cf6f405e';
        $url .= '&' . $param;
//        $url .= '&limit=3';
//        $url .= '&dow=7';
//        $url = 'https://www.ramblers.org.uk/api/lbs/walks?groups=NS01&dow=1&limit=3';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // allow xx seconds for timeout
//			curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_wf"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . "\n");
            print('Http return: ' . $httpCode . "\n");
            echo 'Access failed : ' . $url, $httpCode;
            return;
        }

        $temp = json_decode($data);
        if ($count == 'Y') {
            return $temp->summary;
        } else {
            return $temp->data;
        }
    }

    /**
     *   Process the walks data
     */
    private function processwalks($walkslist) {
        $this->walksfound = count($walkslist);
//        die("Walks in feed = $this->walksfound \n");

        foreach ($walkslist as $walk) {
            $this->writeWalk($walk);
        }
    }

// processwalks ( $walkslist)

    /**
     *   Write a walk to the database
     */
    private function writeWalk($walk) {

//        print ("Walk " . $walk->id);
        if ((int) $walk->id == 0) {
            print ( "Walk $this->counter has blank id field \n");
        } else {
            $this->show_message = 1;
            $this->validate($walk);
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);

            $date = substr($walk->start_date_time, 0, 10);
            $start_time = substr($walk->start_date_time, 11, 5);
            $end_time = substr($walk->end_date_time, 11, 5);
            $description = $walk->description;
            $difficulty = substr($walk->difficulty->description, 0, 10);
//            $title = substr($walk->title, 0, 120);
//            $title = substr(iconv(($walk->title, mb_detect_order(), true), "UTF-8", $walk->title), 0, 120);
            if (URI::base() == 'http://localhost/') {
                $title = substr(iconv("ASCII", "UTF-8//TRANSLIT//IGNORE", $walk->title), 0, 120);
            } else {
                $title = substr($walk->title, 0, 120);
            }

            if (is_null($walk->start_location)) {
                $start_details = '';
                $start_grid_ref = '';
                $start_latitude = 0;
                $start_longitude = 0;
                $start_postcode = '';
            } else {
                $start_details = $walk->start_location->description;
                $start_grid_ref = $walk->start_location->grid_reference_10;
                $start_latitude = (float) $walk->start_location->latitude;
                $start_longitude = (float) $walk->start_location->longitude;
                $start_postcode = $walk->start_location->postcode;
            }
            if ((is_null($walk->walk_leader)) or (is_array($walk->walk_leader))) {
                $phone = '';
                $leader_name = '';
            } else {
                $phone = substr(preg_replace('/\D/', '', $walk->walk_leader->telephone), 0, 15);
                $leader_name = $walk->walk_leader->name;
            }

            if ($walk->shape == 'linear') {
                $shape = 'L';
            } else {
                $shape = 'C';
            }

            $query->set("walk_id = " . $db->quote($walk->id))
                    ->set("walk_date = " . $db->quote($date))
                    ->set("group_code = " . $db->quote($walk->group_code))
                    ->set("organising_group = " . $db->quote($walk->group_code))
                    ->set("contact_display_name = " . $db->quote($leader_name))
// must increase size of database field
//                ->set('contact_email = ' . $db->quote($walk->walk_leader->email_form))
                    ->set('contact_tel1 = ' . $db->quote($phone))
                    ->set("title = " . $db->quote(substr($title, 0, 120)))
                    ->set("description = " . $db->quote($description))
                    ->set("start_time = " . $db->quote($start_time))
                    ->set("start_gridref = " . $db->quote($start_grid_ref))
                    ->set("start_latitude = " . $db->quote($start_latitude))
                    ->set("start_longitude = " . $db->quote($start_longitude))
                    ->set("start_postcode = " . $db->quote($start_postcode))
                    ->set("start_details = " . $db->quote($start_details))
                    ->set("distance_km = " . $db->quote((float) $walk->distance_km))
                    ->set("distance_miles = " . $db->quote((float) $walk->distance_miles))
                    ->set("difficulty = " . $db->quote($difficulty))
//                ->set("pace = " . $db->quote($walk->pace))
                    ->set("ascent_feet = " . $db->quote($walk->ascent_feet))
                    ->set("ascent_metres = " . $db->quote($walk->ascent_metres))
                    ->set("circular_or_linear = " . $db->quote($shape))
                    ->set("finish_time = " . $db->quote($end_time))
            ;

            if ($this->doesWalkExist($walk->id)) {
                $query->update('#__ra_walks')
                        ->where('walk_id=' . $walk->id);
                $result = $db->setQuery($query)->execute();
                $this->walksupdated++;
//                echo "walk $walk->id for $walk->group_code updated<br>";
            } else {
                $query->insert('#__ra_walks');  // utf8mb4_unicode_ci
                $result = $db->setQuery($query)->execute();
                $this->walkscreated++;
            }
        }
    }

    public function refreshWalks() {
        // Invoked from option=com_ra_tools&view=area
        $objHelper = new ToolsHelper;
//        $objHelper->executeCommand('DELETE FROM #__ra_walks');
        $code = Factory::getApplication()->input->getCmd('code', 'NS03');
        $name = $objHelper->lookupGroup($code);
        echo "Walks for $code $name<br>";
        $walklist = $this->getJson('group-walk', 'groups=' . $code);
        $this->processwalks($walklist);
        echo "Walks created = $this->walkscreated<br>";
        echo "Walks updated = $this->walksupdated <br>";
        $target = "index.php?option=com_ra_tools&view=area&code=" . substr($code, 0, 2);
        echo $objHelper->backButton($target);
        $url = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=group-walk';
        $url .= '&api-key=742d93e8f409bf2b5aec6f64cf6f405e';
        $url .= '&groups=' . $code;
        echo $objHelper->buildLink($url, "Show feed", true, "link-button button-p0159");
    }

    private function validate($walk) {
        $message = '<b>' . $walk->id . ', ' . substr($walk->start_date_time, 2, 8) . ':' . substr($walk->start_date_time, 11, 5) . ', ' . $walk->title . '</b><br>';
        if (is_null($walk->start_location)) {
            if ($this->show_message == 1) {
                echo $message;
                $this->show_message = 0;
            }
            echo '...Walk has no start location<br>';
        } else {
            if ($walk->start_location->postcode == '') {
                if ($this->show_message == 1) {
                    echo $message;
                    $this->show_message = 0;
                }
                echo "<b>Walk $walk->id has no value for postcode</b><br>";
            }
            if ($walk->start_location->w3w == '') {
                if ($this->show_message == 1) {
                    echo $message;
                    $this->show_message = 0;
                }
                echo "...Walk has no value for w3w<br>";
            }
        }
        if (is_null($walk->walk_leader)) {
            if ($this->show_message == 1) {
                echo $message;
                $this->show_message = 0;
            }
            echo '...Walk has no Leader or Phone number<br>';
        } elseif (is_array($walk->walk_leader)) {
            if ($this->show_message == 1) {
                echo $message;
                $this->show_message = 0;
            }
            echo '...WalkLeader is invalid<br>';
        } else {
            $no_space = str_replace(' ', '', $walk->walk_leader->telephone);
            $no_space = str_replace('or', '', $no_space);
            $test = preg_replace('/\D/', '', $no_space);
            if ($no_space != $test) {
                if ($this->show_message == 1) {
                    echo $message;
                    $this->show_message = 0;
                }
//                var_dump($walk->walk_leader->telephone);
                echo '...Walk has invalid phone number ' . $no_space . ' (' . $walk->walk_leader->telephone . ')<br>';
            }
        }
    }

}
