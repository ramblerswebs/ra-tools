<?php

/**
 * @package     com_ra_tools
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 06/06/23 CB Created
 * 07/06/23 added showWalk
 * 12/06/23 CB countWalks and duffWalks from area controller
 * 05/07/23 CB Future walks only, summary for noLeaders
 * 24/07/23 CB correct divide by zero
 * 21/08/23 Cb when reading JSON feed, include & before ids
 * 23/08/23 CB remove display of buttons from noLeaders
 * 23/11/23 CB correct use of AssetManager
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Site\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
//use Joomla\CMS\HTML\HTMLHelper;
//use Joomla\CMS\Language\Text;
//use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
//use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHtml;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\Walk;
use Ramblers\Component\Ra_tools\Site\Helpers\Walkbase;

class ReportsController extends FormController {

    protected $criteria_sql;
    protected $db;
    protected $objApp;
    protected $objHelper;
    protected $prefix;
    protected $query;
    protected $scope;

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getContainer()->get('DatabaseDriver');
        $this->objHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        $this->prefix = 'Reports: ';
        // Import CSS
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    public function countWalks() {
        echo '<h3>Future Walks by Area</h3>';
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $objTable->add_header("Code,Area,Count");
        $sql = 'SELECT code, name FROM #__ra_areas ORDER BY code';
        $rows = $objHelper->getRows($sql);
        $total = 0;
        foreach ($rows as $row) {
            $sql = "SELECT COUNT(id) FROM `#__ra_walks`  ";
            $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
            $sql .= "AND group_code LIKE '" . $row->code . "%'";
//            echo $sql;
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks > 0) {
                $objTable->add_item($row->code);
                $objTable->add_item($row->name);
                $objTable->add_item($count_walks);
                $total = $total + $count_walks;
                $objTable->generate_line();
            }
        }
        $objTable->generate_table();
        echo 'Total number of walks ' . $total . '<br>';
        $target = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($target);
    }

    public function duffWalks() {

        $sql = 'SELECT COUNT(*) FROM `#__ra_walks` ';
        $sql .= 'WHERE distance_miles < 0 ';
        $count = $objHelper->getValue($sql);
        if ($count > 0) {
            echo '<h3>Future Walks with negative length</h3>';
            $objTable = new ToolsTable();
            $objTable->add_header("Group,id,Date,Title,Miles");
            $sql = 'SELECT walk_id, group_code, walk_date,title, distance_miles ';
            $sql .= 'FROM `#__ra_walks` ';
            $sql .= 'WHERE distance_miles < 0 ';
            $sql .= 'ORDER BY group_code,walk_date,title';
            $rows = $objHelper->getRows($sql);
            foreach ($rows as $row) {
                $objTable->add_item($row->group_code);
                $objTable->add_item($objHelper->buildLink($target . $row->walk_id, $row->walk_id, true));
                $objTable->add_item($row->walk_date);
                $objTable->add_item($row->title);
                $objTable->add_item($row->distance_miles);
                $objTable->generate_line();
            }
            $objTable->generate_table();
            echo $count . ' walks with negative length<br>';
        } echo '<h3>Future Walks with no Start</h3>';
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $objTable->add_header("Group,id,Date,Title,GR,Lat,Long");
        $sql = 'SELECT group_code,walk_id,walk_date,title, start_gridref, start_latitude, start_longitude ';
        $sql .= 'FROM `#__ra_walks` ';
        $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
        $sql .= 'AND (start_gridref = "") OR (start_latitude = 0) OR (start_longitude = 0) ';
        $sql .= 'ORDER BY group_code,walk_date,title';
        $rows = $objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_tools&task=reports.showWalk&id=';
        $count = 1;
        foreach ($rows as $row) {
            $count++;
            $objTable->add_item($row->group_code);
            //           $. $row->walk_id;
            $objTable->add_item($objHelper->buildLink($target . $row->walk_id, $row->walk_id, true));
            $objTable->add_item($row->walk_date);
            $objTable->add_item($row->title);
            $objTable->add_item($row->start_gridref);
            $objTable->add_item($row->start_latitude);
            $objTable->add_item($row->start_longitude);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Number of walks without a valid start point ' . $count . '<br>';

        $target = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($target);
    }

    public function groupsNoWalks() {
//      $area = Factory::getApplication()->input->getCmd('area', 'NS');
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $objTable->add_header("Area,Code,Name");
        echo "<h3>Groups without walks in WM</h3>";
        $sql = 'SELECT ';
        $sql .= 'areas.name AS area_name, ';
        $sql .= 'groups.code AS group_code, ';
        $sql .= 'groups.name AS group_name ';
//        $sql .= 'areas.name AS area_name ';
//            $sql .= 'groups.code AS group_code, ';
        $sql .= 'FROM `#__ra_walks` AS `walks` ';
        $sql .= 'RIGHT JOIN `#__ra_groups` AS `groups` ON `groups`.`code`=`walks`.`group_code` ';
        $sql .= 'INNER JOIN `#__ra_areas` AS `areas` ON `groups`.`area_id`=`areas`.`id` ';
//        $sql .= 'WHERE (datediff(walks.walk_date, CURRENT_DATE) >= 0) ';
        $sql .= 'GROUP BY groups.name, groups.code, areas.name ';
        $sql .= 'HAVING COUNT(walks.id) =0 ';

        $sql .= 'ORDER BY areas.name,groups.name';
        $rows = $objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_tools&task=reports.showWalk&id=';
        $count = 0;
        foreach ($rows as $row) {
            $count++;
            $objTable->add_item($row->area_name);
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->group_name);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Number of Groups without any walks ' . $count . '<br>';

        $target = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($target);
//        echo $objTable->num_rows - 1 . ' Groups ';
//           $target = "index.php?option=com_ra_wf&view=reports";
//       echo $this->objHelper->backButton($target);
    }

    public function noDescription() {
        echo '<h3>Walks with no Description</h3>';
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $objTable->add_header("Group,id,Date,Title,GR,Lat,Long");
        $sql = 'SELECT group_code,walk_id,walk_date,title, start_gridref, start_latitude, start_longitude ';
        $sql .= 'FROM `#__ra_walks` ';
        $sql .= 'WHERE (description = "(blank)") ';
        $sql .= 'ORDER BY group_code,walk_date,title';
        $rows = $objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_tools&task=reports.showWalk&id=';
        $count = 0;
        foreach ($rows as $row) {
            $count++;
            $objTable->add_item($row->group_code);
//           $. $row->walk_id;
            $objTable->add_item($objHelper->buildLink($target . $row->walk_id, $row->walk_id, true));
            $objTable->add_item($row->walk_date);
            $objTable->add_item($row->title);
            $objTable->add_item($row->start_gridref);
            $objTable->add_item($row->start_latitude);
            $objTable->add_item($row->start_longitude);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Number of walks without a Description ' . $count . '<br>';
        $back = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($back);
    }

    public function noLeader() {
        echo '<h3>Future Walks with no Leader</h3>';
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $objTable->add_header("Group,Total walks, Without leader,Perc");
        $sql = 'SELECT group_code, COUNT(id) as num ';
        $sql .= 'FROM `#__ra_walks`s ';
        $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
        $sql .= 'AND (contact_display_name = "") ';
        $sql .= 'GROUP BY group_code ';
        $sql .= 'ORDER BY group_code';
        $rows = $objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_tools&task=reports.noLeaderDetails&code=';
        $total1 = 0;
        $total2 = 0;
        foreach ($rows as $row) {
            $objTable->add_item($row->group_code);
            $sql = 'SELECT COUNT(id) FROM `#__ra_walks` ';
            $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
            $sql .= 'AND (group_code ="' . $row->group_code . '") ';
            $sql .= 'GROUP BY group_code ';
            $count = $objHelper->getValue($sql);
            $total1 += $count;
            $link = $target . $row->group_code;
            $objTable->add_item($objHelper->buildLink($link . '&all=Y', $count));

            $link = $target . $row->group_code;
            $objTable->add_item($objHelper->buildLink($link, $row->num));
            $total2 += $row->num;

            $objTable->add_item((INT) ($row->num * 100 / $count) . '%');
            $objTable->generate_line();
        }
        if ($total1 == 0) {
            $objTable->add_item('No walks found without leader');
        } else {
            $objTable->add_item('Total');
            $objTable->add_item(number_format($total1));
            $objTable->add_item(number_format($total2));
            $objTable->add_item((int) ($total2 * 100 / $total1) . '%');
        }
        $objTable->generate_line();
        $objTable->generate_table();

        $sql = 'SELECT COUNT(id) FROM `#__ra_walks` ';
        $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
        $count = $objHelper->getValue($sql);
        echo 'Total number of future walks for all Groups ' . number_format($count) . '<br>';

        $target = 'index.php?option=com_ra_tools&view=area_list';
        echo $objHelper->backButton($target);
    }

    public function noLeaderDetails() {
        $group_code = Factory::getApplication()->input->getCmd('code', 'NS03');
        $all_walks = Factory::getApplication()->input->getCmd('all', 'N');
        $objHelper = new ToolsHelper;
        $details = $group_code . ' ' . $objHelper->lookupGroup($group_code);
        echo '<h3>Future Walks ';
        if ($all_walks == 'N') {
            echo 'with no Leader ';
        }
        echo 'for ' . $details . '</h3>';
        $objTable = new ToolsTable();
        $objTable->add_header("Group,id,Date,Title,Leader,GR,Lat,Long");
        $sql = 'SELECT group_code,walk_id,walk_date,title, contact_display_name,start_gridref, start_latitude, start_longitude ';
        $sql .= 'FROM `#__ra_walks` ';
        $sql .= 'WHERE (datediff(walk_date, CURRENT_DATE) >= 0) ';
        $sql .= 'AND (group_code ="' . $group_code . '") ';
        if ($all_walks == 'N') {
            $sql .= 'AND (contact_display_name = "") ';
        }
        $sql .= 'ORDER BY group_code,walk_date,title';
        $rows = $objHelper->getRows($sql);
        $target = 'index.php?option=com_ra_tools&task=reports.showWalk&id=';
        $count = 0;
        foreach ($rows as $row) {
            $count++;
            $objTable->add_item($row->group_code);
//           $. $row->walk_id;
            $objTable->add_item($objHelper->buildLink($target . $row->walk_id, $row->walk_id,));
            $objTable->add_item($row->walk_date);
            $objTable->add_item($row->title);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($row->start_gridref);
            $objTable->add_item($row->start_latitude);
            $objTable->add_item($row->start_longitude);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Number of walks ' . $count . '<br>';
        $target = 'index.php?option=com_ra_tools&task=reports.noLeader';
        echo $objHelper->backButton($target);
    }

    public function showEvent() {
        $JsonHelper = new JsonHelper;
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();

        $id = Factory::getApplication()->input->getInt('id', '100121751');

        $temp = $JsonHelper->getJson('group-event', '&ids=' . $id);
        $event = $temp[0];
//        var_dump($event->location);
        echo "<h2>Event $id</h2>";

//        echo $event->url[1] . '<br>';
//        echo $event->title . '<br>';

        $objTable->add_column("Event ID", "R");

        $details = $id;
        $details .= $objHelper->imageButton("I", $JsonHelper->getUrl('group-event') . "&ids=" . $id, True);

        $objTable->add_column($details, "L");
        $objTable->generate_header();

        $objTable->add_item("Group ");
        $group_code = $event->group_code;
        $details = $group_code . ' ' . $objHelper->lookupGroup($group_code);
        $objTable->add_item($details);
        $objTable->generate_line();
        $objTable->add_item("Start");
        $objTable->add_item($event->start_date_time);
        $objTable->generate_line();

        $objTable->add_item("Finish");
        $objTable->add_item($event->end_date_time);
        $objTable->generate_line();

        $objTable->add_item("Title");
        $objTable->add_item($event->title);
        $objTable->generate_line();

        $objTable->add_item("Organiser");
        $objTable->add_item($event->event_organiser->name);
        $objTable->generate_line();

        $objTable->add_item("Description");
        $objTable->add_item($event->description);
        $objTable->generate_line();

        $objTable->add_item("Location");
        $objTable->add_item($event->location->description);
        $objTable->generate_line();

        $objTable->add_item("what 3 words");
        $objTable->add_item($event->location->w3w);
        $objTable->generate_line();

        $objTable->add_item("Post code");
        $objTable->add_item($event->location->postcode);
        $objTable->generate_line();

        $objTable->add_item("Weblink");
        $objTable->add_item($objHelper->buildLink($event->external_url, $event->external_url, true));
        $objTable->generate_line();

        $objTable->generate_table();
        $back = 'index.php?option=com_ra_tools&task=reports.showEventsArea&code=' . substr($group_code, 0, 2);
        echo $objHelper->backButton($back);
    }

    public function showEvents() {
        $JsonHelper = new JsonHelper;
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();
        $target = 'index.php?option=com_ra_tools&task=reports.showEventsArea&code=';

        echo '<h2>Events from WalksManager</h2>';
        $objTable->add_header("Code,Name,Count");

        $sql = 'SELECT code, name FROM #__ra_areas ';
        $sql .= 'ORDER BY code ';
//        $sql .= 'LIMIT 3';
        $areas = $objHelper->getRows($sql);
        foreach ($areas as $area) {

            $count = $JsonHelper->getCountEvents($area->code);
            if ($count > 0) {
                $objTable->add_item($area->code);
                $objTable->add_item($area->name);
                $objTable->add_item($objHelper->buildLink($target . $area->code, $count, true));
                $objTable->generate_line();
            }
        }

        $objTable->generate_table();
        echo $objHelper->backButton($back);
    }

    public function showEventsArea() {
        $JsonHelper = new JsonHelper;
        $objHelper = new ToolsHelper;
        $objTable = new ToolsTable();

        $target = 'index.php?option=com_ra_tools&task=reports.showEvent&id=';
//        $target = $JsonHelper->getUrl('group-event') . 'ids=';

        $group_code = Factory::getApplication()->input->getWord('code', 'NS03');
        $callback = Factory::getApplication()->input->getWord('callback', '');
        $details = $group_code . ' ' . $objHelper->lookupGroup($group_code);
        if ($callback == 'area_list') {
            $back = 'index.php?option=com_ra_tools&view=area_list';
        } else {
            $back = 'index.php?option=com_ra_tools&task=reports.showEvents';
        }
        echo '<h2>Events for ' . $details . '</h2>';
        $objTable->add_header("Group,id,Date,Time,Title,Organiser,GR,W3W");

        $event_list = $JsonHelper->getJson('group-event', 'groups=' . $group_code);

        foreach ($event_list as $event) {
            $date = substr($event->start_date_time, 0, 10);
            $start_time = substr($event->start_date_time, 11, 5);
            //                $end_time = substr($event->end_date_time, 11, 5);
            $objTable->add_item($event->group_code);
            $objTable->add_item($objHelper->buildLink($target . $event->id, $event->id));
            $objTable->add_item($date);
            $objTable->add_item($start_time);
            $objTable->add_item($event->title);
            $objTable->add_item($event->event_organiser->name);
            if (is_null($event->location)) {
                $objTable->add_item('');
                $objTable->add_item('');
            } else {
                $objTable->add_item($event->location->grid_reference_10);
                $objTable->add_item($event->location->w3w);
            }

            $objTable->generate_line();
        }
        $objTable->generate_table();

        echo $this->objHelper->backButton($back);
    }

    public function showWalks() {
        $code = Factory::getApplication()->input->getCmd('code', 'NS03');
        $scope = Factory::getApplication()->input->getCmd('scope', 'F');
        $name = $this->objHelper->lookupGroup($code);
//        ToolBarHelper::title($this->prefix . 'Walks for ' . $code . ' ' . $name);
        echo "<h2>Walks for $code $name</h2>";

        $target = 'index.php?option=com_ra_tools&task=reports.showWalk&id=';

        $objTable = new ToolsTable();
        $objTable->add_column("Walk Date", "L");
        $objTable->add_column("Title", "L");
        $objTable->add_column("Group", "L");
        $objTable->add_column("Difficulty", "C");
        $objTable->add_column("GR", "L");
        $objTable->add_column("Mi", "L");
        $objTable->add_column("Leader", "L");
        $objTable->add_column("Walk ID", "C");
        $objTable->generate_header();

        $sql = "SELECT walks.id,date_format(walk_date,'%a %e-%m-%y') AS Date,
           start_time, meeting_time,
           walks.title, group_code, difficulty, distance_miles, start_gridref, contact_display_name,
            walks.walk_id, datediff(walk_date, CURRENT_DATE) AS days_to_go, leader_user_id, walks.id, max_walkers, state ";
        $sql .= "FROM `#__ra_walks` AS `walks` ";
        $sql .= 'WHERE group_code="' . $code . '" ';
        if ($scope == "F") {
            $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
        }
        $sql .= 'ORDER BY group_code,walk_date,title ';
        $sql .= 'LIMIT 10';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $details = $row->Date . '<br>';
            if ($row->meeting_time == '') {
//                        $details .= '--/';
            } else {
                $details .= 'M: ' . $row->meeting_time . '/';
            }
            if ($row->start_time == '') {
                $details .= '--';
            } else {
                $details .= 'S:' . $row->start_time;
            }
            ;
            $objTable->add_item($details);
            $objTable->add_item($row->title);
//$objTable->add_item($row->group_code . ',' . $row->leader_user_id);
            $objTable->add_item($row->group_code);
            $objTable->add_item($row->difficulty);
            $objTable->add_item($row->start_gridref);
            $objTable->add_item($row->distance_miles);
            $objTable->add_item($row->contact_display_name);
            $objTable->add_item($this->objHelper->buildLink($target . $row->walk_id, $row->walk_id));

            $objTable->generate_line();
        }
        $objTable->generate_table();

        if (1) {
            $back = "index.php?option=com_ra_tools&view=area&code=" . substr($code, 0, 2);
        } else {

        }
        echo $this->objHelper->backButton($back);
    }

    public function showLeaders() {
        $this->scope = $this->objApp->input->getCmd('scope', 'F');
        $self = 'index.php?option=com_ra_tools&task=reports.showLeaders';
        $callback = $self . '&scope = ' . $this->scope;
        ?>
        <script type = "text/javascript">
            function changeScope(target) {
                window.location = target + "&scope=" + document.getElementById("selectScope").value;
                return true;
            }
        </script>
        <?php

        $sql = "SELECT  date_format(walk_date,'%a %e-%m-%y') AS Date,";
        $sql .= "walks.title as 'Title', ";
        $sql .= "walks.contact_display_name as 'Leader', ";
        $sql .= "walks.organising_group as 'Group', ";
        $sql .= "walks.walk_id as WalkId, ";
        $sql .= "walks.id as 'Internal',";
//        $sql .= "walks.leader_user_id, ";
        $sql .= "profile.user_id as Ref ";
        $sql .= "FROM `#__ra_walks` as walks  ";
//        $sql .= "LEFT JOIN __ra_profiles as profile on profile.user_e = walks.contact_display_name ";
        $sql .= 'LEFT JOIN __ra_profiles as profile ON walks.leader_user_id = profile.user_id ';
        $sql .= "WHERE (walks.leader_user_id > 0) ";

        switch ($this->scope) {
            case ($this->scope == 'D');
                $sql .= "AND state=0 ";
                break;
            case ($this->scope == 'F');
                $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
                break;
            case ($this->scope == 'H');
                $sql .= 'AND datediff(walk_date, CURRENT_DATE) < 0 ';
        }

        $sql .= "order by walk_date";
        echo "<h2>Reporting</h2>";
        echo "<h4>Walk Leaders who are registered</h4>";
        $target = 'index.php?option=com_ra_tools&task=reports.showLeaders';
        ToolsHelper::selectScope($this->scope, $target);
        echo '<br>';

        $this->objHelper->showSql($sql);
        $back = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($back);
    }

    public function showLogfile() {

        $offset = $this->objApp->input->getInt('offset', '0');
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;
        $rs = "";

        $date_difference = (int) $offset;
        $today = date_create(date("Y-m-d 00:00:00"));
        if ($date_difference === 0) {
            $target = $today;
        } else {
            if ($date_difference > 0) { // positive number
                $target = date_add($today, date_interval_create_from_date_string("-" . $date_difference . " days"));
            } else {
                $target = date_add($today, date_interval_create_from_date_string($date_difference . " days"));
            }
        }
        ToolBarHelper::title($this->prefix . 'Logfile records for ' . date_format($target, "D d M"));

        $sql = "SELECT date_format(log_date, '%a %e-%m-%y') as Date, ";
        $sql .= "date_format(log_date, '%H:%i:%s.%u') as Time, ";
        $sql .= "record_type, ";
        $sql .= "ref, ";
        $sql .= "message ";
        $sql .= "FROM #__ra_logfile ";
        $sql .= "WHERE log_date >='" . date_format($target, "Y/m/d H:i:s") . "' ";
        $sql .= "AND log_date <'" . date_format($target, "Y/m/d 23:59:59") . "' ";
        $sql .= "ORDER BY log_date DESC, record_type ";
        if ($this->objHelper->showSql($sql)) {
            echo "<h5>End of logfile records for " . date_format($target, "D d M") . "</h5>";
        } else {
            echo 'Error: ' . $this->objHelper->error . '<br>';
        }

        echo $this->objHelper->buildButton("index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $previous_offset, "Previous day", False, 'grey');
        if ($next_offset >= 0) {
            echo $this->objHelper->buildButton("index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $next_offset, "Next day", False, 'teal');
        }
        $target = "index.php";
        echo $this->objHelper->backButton($target);
    }

//=====================================================================================
    public function showWalk() {

        /**
         * 07/06/23 CB Code copied from template walk
         */
        echo "<!-- Code from com_ra_wf/views/walkdetail/tmpl/walkdetail_template.php -->" . PHP_EOL;
        $objApp = Factory::getApplication();
        $id = (int) $objApp->input->getCmd('id', '100121751');

        $objHelper = new ToolsHelper;
        $objWalk = new Walk;
//        $objWalk->id = $id;
//  if (!$objWalk->getData()) {
//echo $objWalk->id . "<br>";
        if (!$objWalk->checkExists($id)) {
            echo $objWalk->message;
            die('Walk ' . $id . ' not found');
        }
        $days_to_go = (int) $objWalk->getDaystoGo();
// Determine the id of the current user, and if SuperUser
        $isSuperuser = $objHelper->isSuperuser();

        echo "<h2>Walk $id</h2>";

        $objTable = new ToolsTable;
//$objTable->num_columns = 3;
        $objTable->add_column("Walk ID", "R");
        $walk_id = $objWalk->walk_id;

        $details = $walk_id;
        if ($isSuperuser) {
            $details .= " (internal id=" . $objWalk->id . ")";
            $details .= $objHelper->imageButton("I", "http://www.ramblers.org.uk/api/lbs/walks/" . $walk_id, True);
        }
        $JsonHelper = new JsonHelper;
        echo $JsonHelper->showWalk($id);
        $objTable->add_column($details, "L");
        $objTable->generate_header();

        $objTable->add_item("Group ");
        $group_code = $objWalk->group_code;
        $details = $group_code . ' ' . $objWalk->getGroupname();
        $organising_group = $objWalk->organising_group;
        if ($organising_group <> $group_code) {
            $details .= " (organised by $organising_group " . $objHelper->lookupGroup($organising_group) . ')';
        }
        $objTable->add_item($details);
        $objTable->generate_line();

        $objTable->add_item("Date ");
        $details = $objWalk->getDate(3) . ', ' . abs($days_to_go) . ' days ';
        if ($days_to_go == 0) {
            $details .= 'today';
        } elseif ($days_to_go > 0) {
            $details .= 'to go';
        } else {
            $details .= 'ago';
        }
        $objTable->add_item($details);
        $objTable->generate_line();

        $objTable->add_item("Details ");
        $details = "<b>" . $objWalk->title . "</b>";
//if (!$objWalk->description == $objWalk->title) {
        $details .= "<br>" . $objWalk->description;
        if ($objWalk->notes > "") {
            $details .= "<br>" . $objWalk->notes;
        }
//}
        $objTable->add_item($details);
        $objTable->generate_line();

        $objTable->add_item("Leader ");
        $details = $objWalk->contact_display_name;
        $contact = $objWalk->getContactDetails();
        if ($contact != '') {
            $details .= "<br>" . $contact;
        }

        $objTable->add_item($details);
        $objTable->generate_line();

        if (!$objWalk->state == 1) {
            $objTable->add_item("State ");
            if ($objWalk->state == 0) {
                $objTable->add_item("Draft");
                $line_colour = "#ffffaa";
            } elseif ($objWalk->state == 2) {
                $objTable->add_item("Cancelled");
                $line_colour = "#ff00ff";
            } else {
                $objTable->add_item("Status" . $objWalk->state);
                $line_colour = "#b0e0e6";
            }
            $objTable->generate_line($line_colour);
        }

// These next items of data are returned by Walk.php, not WalkBase.php
        if ($objWalk->get_meeting_description($details)) {
            $objTable->add_item("Meet ");
            $objWalk->get_meeting_location($button);
            $objTable->add_item($details . " " . $button);
            $objTable->generate_line();
        }

        if ($objWalk->get_start_description($details)) {
            $objTable->add_item("Start ");
            $objWalk->get_start_location($button);
            $objTable->add_item($details . " " . $button);
            $objTable->generate_line();
        }
        /*
          $objTable->add_item("Maximum walkers permitted");
          $details = $objWalk->max_walkers;
          if ($details == 0) {
          // Find the maximum allowed on a walk
          $details = $this->params->get("max_walkers");
          $details .= " (group default)";
          } else {
          $details .= " (set by the walk leader)";
          }
          if ($leader_user_id > 0) {
          if (($isSuperuser) or ($leader_user_id == $this->user->id)) {
          $details .= $objHelper->imageButton("M", "index.php?option=com_ra_wf&view=walk&id=" . $objWalk->id);
          }
          }

          $objTable->add_item($details);
          $objTable->generate_line();

          // Number of emails sent will have been set up the the class itself
          if ($this->count_emails > 0) {
          $objTable->add_item("Emails ");
          if (($objWalk->leader_user_id == $this->user->id) OR ($isSuperuser)) {
          $target = 'index.php?option=com_ra_wf&task=walk.showEmails&id=' . $objWalk->id;
          $details = $objHelper->buildLink($target, $this->count_emails);
          } else {
          $details = $this->count_emails;
          }
          $objTable->add_item($details);
          $objTable->generate_line();
          }
          // Number of followers will have been set up the the class itself
          if ($days_to_go < 0) {
          $objTable->add_item("Followers ");
          } else {
          $objTable->add_item("Followers to date ");
          }
          $details = '';
          if ($this->count_followers == 0) {
          $details = " none";
          } else {
          $target = 'index.php?option=com_ra_wf&task=walk.showFollowers&id=' . $objWalk->id;
          $details = $objHelper->buildLink($target, $this->count_followers);
          if ($already_following == 1) {
          if ($days_to_go < 0) {
          $follow_message = 'You followed';
          } else {
          $follow_message = 'You are already following';
          }
          $details .= $follow_message . ' this walk';
          if ($this->count_followers > 1) {
          $details .= $objHelper->buildLink($target_email . '&mode=MM', "Send email to other Followers", False, "link-button button-p0186");
          }
          }
          }
          if (($this->user->id > 0) AND ($this->count_followers > 0)) {
          if ($objWalk->leader_user_id == $this->user->id) {
          $details .= $objHelper->buildLink($target_email . '&mode=ML', "Send email to your Followers", False, "link-button button-p0186");
          }
          }
          if (($this->user->id > 0) AND ($objWalk->leader_user_id != $this->user->id)) {
          if (($already_following == 0) AND ($days_to_go > 0)) {
          $details .= $objHelper->buildLink($target_follow . $objWalk->id, "Follow", False, "link-button button-p0186");
          }
          }
          $objTable->add_item($details);
          $objTable->generate_line();
         */
// These items of data are returned by WalkBase.php
        $details = $objWalk->difficulty;
        if (!$details == "") {
            $objTable->add_item("Difficulty ");
            $objTable->add_item($details);
            $objTable->generate_line();
        }

        $details = $objWalk->grade_local;
        if (!$details == "") {
            $objTable->add_item("Local Grade ");
            $objTable->add_item($details);
            $objTable->generate_line();
        }
        $details = $objWalk->pace;
        if (!$details == "") {
            $objTable->add_item("Pace ");
            $objTable->add_item($details);
            $objTable->generate_line();
        }

        $objTable->add_item("Distance ");
        $details = $objWalk->distance_miles . " miles, ";
        $details .= $objWalk->distance_km . " km";
        $objTable->add_item($details);
        $objTable->generate_line();

        $details = $objWalk->ascent_feet;
        if (!$details == "") {
            $objTable->add_item("Ascent ");
            $details .= " feet, ";
            $details .= $objWalk->ascent_metres . " metres";
            $objTable->add_item($details);
            $objTable->generate_line();
        }

        $details = $objWalk->count_walkers;
        if (!$details == "") {
            $objTable->add_item("Number of walkers");
            $objTable->add_item($details);
            $objTable->generate_line();
        }

        $details = $objWalk->finish_time;
        if (!$details == "") {
            $objTable->add_item("Finish time ");
            $objTable->add_item($details);
            $objTable->generate_line();
        }
        /*
          $objTable->add_item("Route ");
          $walks_folder = rtrim($this->params->get('walks_folder'), '/') . '/';
          $gpx_file = JPATH_BASE . $walks_folder . $id . '-1.gpx';
          //echo $gpx_file;
          if (file_exists($gpx_file)) {
          $target = 'index.php?option=com_ra_wf&task=walk.showGpx&id=' . $id;
          $details .= $objHelper->buildLink($target, "Show route", False);
          } else {
          $details = "(GPX route not yet uploaded)";
          if ($this->user->id > 0) {
          if (($objWalk->leader_user_id == $this->user->id) or ($isSuperuser)) {
          //        $details .= $gpx_file . ' ';
          $encoded = $objWalk->encode($this->user->id, "05");
          $details .= $objHelper->buildLink("components/com_ra_wf/upload_gpx.php?token=" . $encoded, "Upload", False, "link-button button-p7474");
          }
          }
          }
          $objTable->add_item($details);
          $objTable->generate_line();

          if ($days_to_go < 0) {
          $objTable->add_item("Feedback ");
          if ($this->count_feedback == 0) {
          $details = 'Not yet any feedback';
          $caption = 'Feedback';
          } else {
          $target = 'index.php?option=com_ra_wf&task=walk.showFeedback&callback=walkdetail&id=' . $objWalk->id;
          $details = $objHelper->buildLink($target, 'Show feedback');
          $caption = 'Add photos';
          if ($this->count_photos > 0) {
          $target = 'index.php?option=com_ra_wf&task=walk.showPhotos&callback=walkdetail&id=' . $objWalk->id;
          $details .= ', ' . $objHelper->buildLink($target, $this->count_photos . ' photos');
          }
          }
          // If the current user is a SuperUser, always show a "Feedback" button
          if (($already_following) or ($objHelper->isSuperuser())) {
          //        $encoded = $objWalk->encode($this->user->id, "F");
          $link = 'components/com_ra_wf/feedback.php?callback=diary&ref=' . $objWalk->encode($this->user->id, "F");
          $details .= $objHelper->buildLink($link, $caption, false, "link-button button-p0159");
          //        $details .= $objWalk->feedbackButton($encoded);
          }
          $objTable->add_item($details);
          $objTable->generate_line();
          }
         */
        $objTable->generate_table();
        $back = 'index.php?option=com_ra_tools&task=reports.showWalks&scope=F&code=' . $objWalk->group_code;
        echo $objHelper->backButton($back);
    }

//==========================================================================
}
