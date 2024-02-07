<?php

/**
 * @version     4.0.12
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 02/06/23 CB JoomlaUsersByGroup - LEFT JOIN
 * 20/08/23 CB Show Admin'Site in menu report
 * 18/08/23 CB areasLatitude
 * 21/11/23 CB correct column count in schema table
 * 11/12/23 Cb use buttons for logFile Prev/Next
 * 11/01/24 CB correction for schema / tablename quoting`
 * 22/01/24 CB show additional paths
 * 22/01/24 CB contactsByCategory
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHtml;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class ReportsController extends FormController {

    protected $criteria_sql;
    protected $back;
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
        $this->back = 'administrator/index.php?option=com_ra_tools&view=reports';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    public function areasLatitude() {
        // display address of Areas, sorted by Latitude
        ToolBarHelper::title('Areas, sorted by Latitude');
        $sql = 'SELECT latitude, longitude, code, name ';
        $sql .= "FROM #__ra_areas ";
        $sql .= 'ORDER BY latitude ';
        $this->objHelper->showQuery($sql);
        $sql = "SELECT COUNT(*) FROM #__ra_areas ";
        echo 'Number of Areas ' . $this->objHelper->getValue($sql) . '<br>';
        echo $this->objHelper->backButton($this->back);
    }

    public function areasLongitude() {
        // display address of Areas, sorted by Longitude
        ToolBarHelper::title('Areas, sorted by Longitude');
        $sql = 'SELECT longitude, latitude, code, name ';
        $sql .= "FROM #__ra_areas ";
        $sql .= 'ORDER BY longitude ';
        $this->objHelper->showQuery($sql);
        $sql = "SELECT COUNT(*) FROM #__ra_areas ";
        echo 'Number of Areas ' . $this->objHelper->getValue($sql) . '<br>';
        echo $this->objHelper->backButton($this->back);
    }

    public function contactsByCategory() {
        ToolBarHelper::title('Contacts by Category');
        $sql = 'SELECT c.id, c.name, c.con_position, c.email_to, ';
        $sql .= 'u.username, u.email, ';
        $sql .= 'cat.title, c.state ';
        $sql .= 'FROM #__contact_details AS c ';
        $sql .= 'LEFT JOIN #__users AS u ON u.id =  c.user_id ';
        $sql .= 'INNER JOIN #__categories AS cat ON cat.id =  c.catid ';
        $sql .= "WHERE c.con_position IS NOT NULL ";
        $sql .= 'AND c.published=1 ';
        $sql .= 'AND cat.extension="com_contact" ';
        $sql .= 'AND cat.title="committee" ';
        $sql .= 'ORDER BY cat.title, c.name' . $order;

        $objTable = new ToolsTable;
        $objTable->add_header("Category,Name,Role,User name,email, Status");
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->title);
            $objTable->add_item($row->name);
            $objTable->add_item($row->con_position);
            $objTable->add_item($row->username);
            $objTable->add_item($row->email);
            $objTable->add_item($row->state);
            $objTable->generate_line();
        }
        $objTable->generate_table();

        echo $this->objHelper->backButton($this->back);
    }

    public function countUsers() {
        ToolBarHelper::title($this->prefix . 'User count by Group');
        $sql = "SELECT a.ra_group_code AS 'GroupCode', g.name, count(u.id) AS 'Number', ";
        $sql .= "MIN(w.walk_date) AS 'Earliest', ";
        $sql .= "MAX(w.walk_date) as 'Latest' ";
        $sql .= "FROM #__ra_profiles AS a ";
        $sql .= 'INNER JOIN #__users AS u ON u.id = a.id ';
        $sql .= 'LEFT JOIN #__ra_groups AS g ON g.code = a.ra_group_code ';
        $sql .= 'LEFT JOIN #__ra_walks AS w ON w.leader_user_id = a.id ';
        $sql .= 'GROUP BY a.ra_group_code ';
        $sql .= 'ORDER BY a.ra_group_code ';
        $rows = $this->objHelper->getRows($sql);
        //      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_tools&task=reports.countUsers';
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header("Code,Group,Count,Earliest walk,Latest walk");
        $target = 'administrator/index.php?option=com_ra_tools&task=reports.showUsersForGroup&group=';
        foreach ($rows as $row) {
            if ($row->GroupCode == '') {
                $objTable->add_item('');
            } else {
                // URI cannot handle commas as part of the parameters
                //$param = str_replace(',', '%5C%2C%20', $row->GroupCode);
                $param = str_replace(',', '_', $row->GroupCode);
                $objTable->add_item($this->objHelper->buildLink($target . $param, $row->GroupCode));
                //$objTable->add_item($this->objHelper->buildLink($target . $row->GroupCode, $row->GroupCode));
            }
            $objTable->add_item($row->name);
            $objTable->add_item($row->Number);
            $objTable->add_item($row->Earliest);
            $objTable->add_item($row->Latest);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton($this->back);
//        echo "<p>";
    }

    public function extractContacts() {
        ToolBarHelper::title($this->prefix . 'Contact details');
        $sql = 'SELECT cat.title, c.name, c.email_to, u.email ';
        $sql .= 'FROM `#__contact_details`  AS c ';
        $sql .= 'LEFT JOIN #__users AS u ON u.id =  c.user_id ';
        $sql .= 'INNER JOIN #__categories AS cat ON cat.id =  c.catid ';
        $sql .= 'ORDER BY cat.title, name';
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            echo $row->title . ',' . $row->name;
            echo ',' . $row->email, ',' . $row->email_to . '<br>';
        }
        echo $this->objHelper->backButton($this->back);
    }

    private function lookupPath($value) {
        if ($value == 'not specified') {
            return '';
        }
        if (is_dir(JPATH_ROOT . '/' . $value . '/')) {
            return 'Y';
        } else {
            return 'N';
        }
    }

    private function setScopeCriteria() {
        switch ($this->scope) {
            case ($this->scope == 'D');
                $this->query->where('state<>1');
                break;
            case ($this->scope == 'F');
                $this->query->where('state=1');
                $this->query->where('datediff(walk_date, CURRENT_DATE) >= 0');
                break;
            case ($this->scope == 'H');
                $this->query->where('state=1');
                $this->query->where('datediff(walk_date, CURRENT_DATE) < 0');
        }
    }

    private function setSelectionCriteria($mode, $opt) {
        if ($mode == 'G') {
            $this->query->where("groups.code='" . $opt . "'");
        } else {
            if ($opt == 'NAT') {

            } else {
                $this->query->where("SUBSTR(groups.code,1,2)='" . $opt . "'");
            }
        }
    }

    public function showClusters() {
        ToolBarHelper::title($this->prefix . 'Clusters and their contacts'); // `#__contact_details`
        $sql = 'SELECT c.code, c.name, c.contact_id, con.state, ';
        $sql .= 'con.con_position, con.name AS `contact`, con.email_to ';
        $sql .= 'FROM `#__ra_clusters` AS c ';
        $sql .= 'LEFT JOIN `#__contact_details` AS con ON con.id =  c.contact_id ';
        $sql .= 'ORDER BY c.code';

        $objTable = new ToolsTable;
        $objTable->add_header("Code,Cluster,Contact ID,Contact name,email,Status");
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->code);
            $objTable->add_item($row->name); // con_position
            $objTable->add_item($row->contact_id);
            $objTable->add_item($row->contact);
            $objTable->add_item($row->email_to);
            $objTable->add_item($row->state);
            $objTable->generate_line();
        }
        $objTable->generate_table();

        echo $this->objHelper->backButton($this->back);
    }

    public function showColours() {
        echo '<link rel="stylesheet" type="text/css" href="/media/com_ra_tools/css/ramblers.css">';
        $c = array();

        $c[] = 'mustard';
        $c[] = 'orange';
        $c[] = 'red';
        $c[] = 'darkgreen';
        $c[] = 'lightgreen';
        $c[] = 'maroon';
        $c[] = 'mud';
        $c[] = 'grey';
        $c[] = 'teal';

        $c[] = 'sunset';
        $c[] = 'granite';
        $c[] = 'rosycheeks';
        $c[] = 'sunrise';
        $c[] = 'cloudy';
        $c[] = 'mintcakedark';
        $c[] = 'cancelled';
        $c[] = 'lightgrey';
        $c[] = 'midgrey';
        $target = 'index.php';
        ToolBarHelper::title('Examples of available colour styles');
        foreach ($c as $colour) {
            echo '<h3>' . $colour . '</h3>';
            $class = ToolsHelper::lookupColourCode($colour, 'B');
            echo ' class="' . $class . '"';
            echo $this->objHelper->buildButton($target, 'Button', 0, $colour) . '<br>';

            $class = ToolsHelper::lookupColourCode($colour, 'T');
            echo ' class="' . $class . '"';
            $objTable = new ToolsTable();
            $title = ' class="' . $class . '" One,Two,Three,Four,Five';
            $objTable->add_header('One,Two,Three,Four,Five', $class);

            for ($i = 1;
                    $i < 6;
                    $i++) {
                $objTable->add_item($i);
            }
            $objTable->generate_line();
            $objTable->generate_table();
        }

        $target = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($target);
    }

    function showExtensions() {
        ToolBarHelper::title($this->prefix . 'Extensions and Modules %ra%');
// Extensions and Modules %ra%
        $objTable = new ToolsTable();

        $objTable->add_header("Type,Element,Name,Version,DB version,id");
        $sql = "SELECT a.extension_id, a.name, a.type, a.element, ";
        $sql .= "s.version_id, a.manifest_cache ";
        $sql .= "FROM #__extensions as a ";
        $sql .= "LEFT JOIN #__schemas as s on s.extension_id = a.extension_id ";
        $sql .= "WHERE name like '%ramb%' ";
        $sql .= "OR element like '%_ra_%' ";
        $sql .= "OR element like '%ramb%' ";
        $sql .= "order by a.type, a.element";

//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {

            $objTable->add_item($row->type);
            $objTable->add_item($row->element);
            $objTable->add_item($row->name);
            $decode = json_decode($row->manifest_cache);
            $objTable->add_item($decode->version);
            $objTable->add_item($row->version_id);
            $objTable->add_item($row->extension_id);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($back);
    }

    public function showFeed() {
        ToolBarHelper::title($this->prefix . 'Feed update for ' . $this->objHelper->lookupGroup($group_code));
        $group_code = $this->objApp->input->getCmd('group_code', 'NS03');
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);

        $objTable = new ToolsTable();

        $objTable->add_header("Date,Message");
        $sql = "SELECT date_amended, field_value ";
        $sql .= "FROM #__ra_groups_audit AS audit ";
        $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
        $sql .= "WHERE `groups`.code='" . $group_code . "' ";
        $sql .= 'ORDER BY date_amended DESC ';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->date_amended);
            $objTable->add_item($row->field_value);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "administrator/index.php?option=com_ra_tools&view=reports_group&group_code=" . $group_code . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($back);
//        if ($csv == '') {
//            $target = "administrator/index.php?option=com_ra_tools&task=reports.showFeed&csv=feed&group_code=" . $group_code . '&scope=' . $this->scope;
//            echo $this->objHelper->buildLink($target, "Extract as CSV", False,  "btn btn-small button-new");
//        }
    }

    public function showFeedSummary() {
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        echo "<h2>Feed Summary</h2>";
        $objTable = new ToolsTable();
        $objTable->set_csv($csv);

        $objTable->add_header("Date,Message");
        $sql = "SELECT log_date, message ";
        $sql .= "FROM #__ra_logfile ";
        $sql .= "WHERE record_type='B9' AND ref=2 ";
        $sql .= 'ORDER BY log_date DESC ';
        $sql .= "Limit 28";
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        foreach ($rows as $row) {
            $objTable->add_item($row->log_date);
            $objTable->add_item($row->message);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $back = "administrator/index.php?option=com_ra_tools&view=reports_area&area=NAT&scope=" . $this->scope;
        echo $this->objHelper->backButton($back);
        if ($csv == '') {
            $target = "administrator/index.php?option=com_ra_tools&task=reports.showFeedSummary&csv=feedSummary";
            echo $this->objHelper->buildLink($target, "Extract as CSV", False, "btn btn-small button-new");
        }
    }

    public function showFeedSummaryArea() {
        $area = $this->objApp->input->getCmd('area_code', 'NS');
        $this->scope = $this->objApp->input->getCmd('scope', '');
        $current_group = '';
        $groups_count = 0;
        $groups_found = 0;
        $area_code = 'NS';
        echo "<h2>Feed update for " . $this->objHelper->lookupArea($area) . "</h2>";
        $sql = "SELECT code from #__ra_groups where code LIKE '" . $area . "%' ORDER BY code";
        $objTable = new ToolsTable();
        $objTable->add_header("Group,Date,Message");

        $groups = $this->objHelper->getRows($sql);
        $groups_count = $this->objHelper->rows;
        foreach ($groups as $group) {
            $sql = "SELECT `groups`.code, date_amended, field_value ";
            $sql .= "FROM #__ra_groups_audit AS audit ";
            $sql .= "INNER JOIN #__ra_groups `groups` ON `groups`.id = audit.object_id ";
            $sql .= "WHERE `groups`.code='" . $group->code . "' ";
            $sql .= 'ORDER BY date_amended DESC LIMIT 7';
//            echo $sql . '<br>';
            $rows = $this->objHelper->getRows($sql);
            foreach ($rows as $row) {
                if ($current_group == $row->code) {

                } else {
                    $groups_found++;
                    $current_group = $row->code;
                }
                $objTable->add_item($group->code);
                $objTable->add_item($row->date_amended);
                $objTable->add_item($row->field_value);
                $objTable->generate_line();
            }
        }

        $objTable->generate_table();
        echo $groups_found . " groups out of " . $groups_count;
        $back = "administrator/index.php?option=com_ra_tools&view=reports_area&area=" . $area . '&scope=' . $this->scope;
        echo $this->objHelper->backButton($back);
    }

    public function showJoomlaUsersByGroup() {


//        ToolBarHelper::title($this->prefix . 'Joomla users by group');
        $objHelper = new ToolsHelper;

        $sql = 'SELECT g.id, g.title, COUNT(u.id) as Num ';
        $sql .= 'FROM #__usergroups AS g ';
        $sql .= 'LEFT JOIN #__user_usergroup_map AS map ON map.group_id = g.id ';
        $sql .= 'INNER JOIN #__users AS u on u.id = map.user_id ';
        $sql .= 'GROUP BY g.id, g.title ';
        $sql .= 'ORDER BY g.id ';
        $rows = $this->objHelper->getRows($sql);
//      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_tools&task=reports.showJoomlaUsersByGroup';
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header('Group_id,Title,Count');
        $target = 'administrator/index.php?option=com_ra_tools&task=reports.showJoomlaUsersForGroup&group=';
        foreach ($rows as $row) {
            $objTable->add_item($row->id);
            $objTable->add_item($row->title);
            $link = $target . $row->id . '&group_name=' . $row->title;
            $objTable->add_item($objHelper->buildLink($link, $row->Num, false));
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton('administrator/index.php?option=com_ra_tools&view=reports');
    }

    public function showJoomlaUsersForGroup() {

        $group_id = $this->objApp->input->getInt('group', '');
        $group_name = $this->objApp->input->getCmd('group_name', '');
        ToolBarHelper::title($this->prefix . 'Joomla users for group ' . $group_name);
        $objHelper = new ToolsHelper;
        $sql = 'SELECT u.id AS UserId, u.name, u.username, u.email ';
        $sql .= 'FROM #__users AS u ';
        $sql .= 'INNER JOIN #__user_usergroup_map AS map ON map.user_id = u.id ';
        $sql .= 'WHERE map.group_id=' . $group_id;
        $rows = $this->objHelper->getRows($sql);
//      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_tools&task=reports.showJoomlaUsersForGroup&group=' . $group_id;
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header('id,Name,Username,Email');
        $target = 'administrator/index.php?option=com_ra_tools&task=reports.showUsersForGroup&group=';
        foreach ($rows as $row) {
            $objTable->add_item($row->UserId);
            $objTable->add_item($row->name);
            $objTable->add_item($row->username);
            $objTable->add_item($row->email);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton('administrator/index.php?option=com_ra_tools&task=reports.showJoomlaUsersByGroup');
    }

    public function showLogfile() {

        $offset = $this->objApp->input->getCmd('offset', '0');
        $next_offset = $offset - 1;
        $previous_offset = $offset + 1;

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

        echo $this->objHelper->buildButton("administrator/index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $previous_offset, "Previous day", False, 'grey');
        if ($next_offset >= 0) {
            echo $this->objHelper->buildButton("administrator/index.php?option=com_ra_tools&task=reports.showLogfile&offset=" . $next_offset, "Next day", False, 'teal');
        }
        $target = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showMenus() {
        ToolBarHelper::title($this->prefix . 'Menu items');
//        echo "<h2>Reporting</h2>";
//        echo "<h4>Ramblers menu items</h4>";
//      Show link that allows page to be printed
        $target = 'administrator/index.php?option=com_ra_tools&task=reports.showMenus';
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $sql = 'SELECT  p.title AS "Parent", m.link, m.title, m.published, m.link, ';
        $sql .= "CASE WHEN p.menutype='main' THEN 'Admin' WHEN p.menutype='mainmenu' THEN 'Site' ELSE '' END AS 'Type', ";
        $sql .= 'm.id, m.parent_id ';
        $sql .= 'FROM `#__menu` AS m ';
        $sql .= 'INNER JOIN `#__menu` AS p ON p.id = m.parent_id ';
        $sql .= "WHERE m.link like 'index.php?option=com_ra%' ";
        $sql .= 'ORDER BY m.menutype, m.link';
        $rows = $this->objHelper->getRows($sql);
        $objTable = new ToolsTable();
        $objTable->add_header('Location,Component, Parent,Menu,Link,Published');
        foreach ($rows as $row) {
//            $objTable->add_item($row->link);
            $objTable->add_item($row->Type);
            $component = substr($row->link, 17);
            $pointer = strpos($component, '&');
            if ($pointer == 0) {
                $view = '';
            } else {
                $view = substr($component, $pointer + 1);
                $component = substr($component, 0, $pointer);
            }
            if ($component == 'com_ra_tools') {
                $objTable->add_item('Ramblers');
            } elseif ($component == 'com_ra_wf') {
                $objTable->add_item('Walks Follow');
            } elseif ($component == 'com_ra_mailman') {
                $objTable->add_item('MailMan');
            } else {
                $objTable->add_item($component);
            }

            $objTable->add_item($row->Parent);
            $objTable->add_item($row->title);
            $objTable->add_item($view);
            if ($row->published == '1') {
                $icon = 'publish';    // tick
            } else {
                $icon = 'delete';     // cross
            }
            $objTable->add_item('<span class="icon-' . $icon . '"></span>');
            $objTable->add_item($row->link);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        $target = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showPaths() {
        ToolBarHelper::title('Reports: Paths ');
        /*
         * Should find any menu entries for routes, geofiles etc and check that they too exist
         */
//      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_tools&task=reports.showPaths';
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        echo '<table class="table table-striped">';
        echo ToolsHtml::addTableHeader(array("Component", "Description", "Value", "Found"));

        if (ComponentHelper::isEnabled('com_ra_tools', true)) {
            $params = ComponentHelper::getParams('com_ra_tools');

            $value = $params->get('document_library', 'not specified');
            echo ToolsHtml::addTableRow(array('com_ra_tools', 'Folder for document library', $value, $this->lookupPath($value)));

//            $value = $params->get('pdf_directory', 'not specified');
//            echo ToolsHtml::addTableRow(array('com_ra_tools', 'pdf_directory', $value, $this->lookupPath($value)));

            $value = $params->get('routes', 'not specified');
            echo ToolsHtml::addTableRow(array('com_ra_tools', 'Folder for storing GPX files', $value, $this->lookupPath($value)));
        }

        if (ComponentHelper::isEnabled('com_ra_walks', true)) {
            $params = ComponentHelper::getParams('com_ra_walks');
            $value = $params->get('walks_folder', 'not specified');
            echo ToolsHtml::addTableRow(array('com_ra_walks', 'walks_folder', $value, $this->lookupPath($value)));
        }
        if (ComponentHelper::isEnabled('com_ra_wf', true)) {

        }
        if (ComponentHelper::isEnabled('com_ra_wg', true)) {
            $value = $params->get('com_ra_wg', 'not specified');
            echo ToolsHtml::addTableRow(array('com_ra_wg', 'Folder for storing GPX files', $value, $this->lookupPath($value)));
        }

        echo "</table>" . PHP_EOL;
        if ((JDEBUG) AND ($this->objHelper->isSuperuser())) {
            $sql = 'SELECT extension_id from #__extensions WHERE element="com_ra_tools"';
//$extension_id = $objHelper->getValue($sql);
//$sql = 'SELECT version_id from #__schemas WHERE extension_id=' . $extension_id;
//echo 'Seeking  ' . $sql . '<br>';
//$version = $objHelper->getValue($sql) . '<br>';
//echo 'Version of database schema is ' . $version . PHP_EOL;

            echo 'Version of PHP is <b>' . phpversion() . '</b>, ini file is <b>' . php_ini_loaded_file() . '</b><br>' . PHP_EOL;
            echo '<br>';
            echo 'JPATH ROOT=' . JPATH_ROOT . '<br>';
            echo 'JPATH BASE=' . JPATH_BASE . '<br>';
            echo 'JPATH_LIBRARIES=' . JPATH_LIBRARIES . '<br>';
            echo 'JPATH COMPONENT=' . JPATH_COMPONENT . '<br>';
            echo 'JPATH JPATH_COMPONENT_ADMINISTRATOR=' . JPATH_COMPONENT_ADMINISTRATOR . '<br>';
            echo 'Templates folder=' . JPATH_THEMES . '<br>';
            $uri = Uri::getInstance();

//            echo 'Juri::base=' . Juri::base() . '<br>';
//    echo 'JPATH CACHE=' . JPATH_CACHE . '<br>';
            echo 'Current Url=' . $uri->toString() . '<br>';
            echo 'root(true)' . $uri::root(true) . '<br>';
            echo Factory::getApplication()->getMenu()->getActive()->route . '<br>'; // https://joomla.stackexchange.com/questions/32098/joomla-4-url-processing-routing
        }
        $target = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($target);
    }

    public function showSchema() {
        $config = Factory::getConfig();
        $database = $config->get('db');
        $dbPrefix = $config->get('dbprefix');
        $total_size = 0;
        ToolBarHelper::title('Ramblers Reports');
        $target = 'index.php?option=com_ra_tools&task=reports.showSchema';
        ToolBarHelper::title($this->prefix . "Database schema for " . $database);
        echo $this->objHelper->showPrint($target);
        $objTable = new ToolsTable();
        $objTable->add_header("Table, Record count, Column count, Index count, Data size, Index size, Total size MB");
        $sql = "SELECT TABLE_NAME, DATA_LENGTH, INDEX_LENGTH, ";
        $sql .= "ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS Size ";
        $sql .= "FROM information_schema.TABLES ";
        $sql .= "WHERE TABLE_SCHEMA = '" . $database . "' AND ";
        $sql .= "TABLE_NAME LIKE '" . $dbPrefix . "ra_%' ";
//        $sql .= " OR TABLE_NAME = '" . $dbPrefix . "users') ";
        $sql .= "ORDER BY TABLE_NAME";
//        echo $sql;
        /*
          UNION
          SELECT 'TOTALS:' AS 'TABLE_NAME',
          sum(DATA_LENGTH) AS 'DATA_LENGTH',
          sum(INDEX_LENGTH) AS 'INDEX_LENGTH',
          sum(data_length + INDEX_LENGTH) AS 'Size'";
          if (JDEBUG) {
          //           Factory::getApplication()->enqueueMessage($this->sql, 'notice');
          echo $sql;
          }
         */

        $tables = $this->objHelper->getRows($sql);
        foreach ($tables as $table) {
            $target = 'administrator/index.php?option=com_ra_tools&task=reports.showTableSchema&table=' . $table->TABLE_NAME;
            $objTable->add_item($this->objHelper->buildLink($target, $table->TABLE_NAME));

            $sql2 = "SELECT COUNT(*) FROM " . $table->TABLE_NAME;
            $target = 'administrator/index.php?option=com_ra_tools&task=reports.showTable&table=' . $table->TABLE_NAME;
            $count = $this->objHelper->getvalue($sql2);
            $objTable->add_item($this->objHelper->buildLink($target, number_format($count)));

            $sql2 = "SELECT COUNT(COLUMN_NAME) FROM information_schema.COLUMNS WHERE TABLE_NAME='" . $table->TABLE_NAME . "'";
            $objTable->add_item($this->objHelper->getvalue($sql2));

            $sql2 = "SELECT COUNT(INDEX_NAME) FROM information_schema.STATISTICS ";
            $sql2 .= "WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='" . $table->TABLE_NAME . "'";
            $objTable->add_item($this->objHelper->getvalue($sql2));

            $objTable->add_item(number_format($table->DATA_LENGTH));
            $objTable->add_item(number_format($table->INDEX_LENGTH));
            $objTable->add_item($table->Size);
            $total_size = $total_size + $table->DATA_LENGTH + $table->INDEX_LENGTH;
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Number of tables in ' . $database . ': ' . $objTable->num_rows . ', ';
        echo 'Total size: ' . $total_size / 1000 / 1000 . ' MB' . '<br>';
        $back = "administrator/index.php?option=com_ra_tools&view=reports";
        echo $this->objHelper->backButton($back);
    }

    public function showSummary() {
        $csv = substr($this->objApp->input->getCmd('csv', ''), 0, 1);
        $group_code = $this->objApp->input->getCmd('group_code', 'NS03');
        $scope = $this->objApp->input->getCmd('scope', 'F');
        echo "<h2>Walks history for " . $this->objHelper->lookupGroup($group_code) . "</h2>";
        $objTable = new ToolsTable();
        if ($csv === 'Y') {
            $objTable->set_csv('Summary');
        }
        $objTable->add_header("Month, Total walks,Joint walks,Guest walks,Total leaders,Total miles, Min miles,Max miles,Avg miles");
        $sql = "SELECT ym,num_walks,joint_walks,guest_walks, ";
        $sql .= "num_leaders,total_miles,min_miles,max_miles,avg_miles ";
        $sql .= "FROM #__ra_snapshot ";
        $sql .= "WHERE group_code='" . $group_code . "' ";
        $sql .= 'ORDER BY ym ';
//        echo $sql;
        $rows = $this->objHelper->getRows($sql);
        $total_miles = 0;
        $total_walks = 0;
        foreach ($rows as $row) {
            $total_miles += $row->total_miles;
            $total_walks += $row->num_walks;

            $objTable->add_item($row->ym);
            if ($row->num_walks == 0) {
                $objTable->add_item('');
            } else {
//                $target = 'index.php?option=com_ra_tools&view=reports_matrix&mode=G&row=M&col=W&opt=' . $group_code;
//                $objTable->add_item($this->objHelper->buildLink($target, $row->num_walks));
                $objTable->add_item($row->num_walks);
            }
            $objTable->add_item(number_format($row->joint_walks));
            $objTable->add_item($row->guest_walks);
            $objTable->add_item($row->num_leaders);
            $objTable->add_item($row->total_miles);
            $objTable->add_item($row->min_miles);
            $objTable->add_item($row->max_miles);
            $objTable->add_item($row->avg_miles);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo 'Total walks: ' . $total_walks . ', Total miles: ' . $total_miles . '<br>';

        $back = "administrator/index.php?option=com_ra_tools&view=reports_group&group_code=" . $group_code . '&scope=' . $scope;
        echo $this->objHelper->backButton($back);
        if (!$csv == 'Y') {
            $target = "administrator/index.php?option=com_ra_tools&task=reports.showSummary&csv=Y&group_code=" . $group_code;
            echo $this->objHelper->buildLink($target, "Extract as CSV", False, "btn btn-small button-new");
        }
    }

    function showTable() {
// display given number of records from the specified table
        $table = $this->objApp->input->getCmd('table', '');
        $limit = $this->objApp->input->getInt('limit', '50');

        $config = Factory::getConfig();
        $database = $config->get('db');
        $dbPrefix = $config->get('dbprefix');
        ToolBarHelper::title($this->prefix . "$limit records from $database $table");
        $found_id = false;
        $sql = 'SELECT * FROM ' . '#__' . substr($table, strlen($dbPrefix));
//        echo '#__' . substr($table, strlen($dbPrefix)) . ': ' . strlen($dbPrefix) . " $dbPrefix $table<br> $sql<br>";
        $this->objHelper->showQuery($sql);
        $back = "administrator/index.php?option=com_ra_tools&task=reports.showSchema";
        echo $this->objHelper->backButton($back);
        return;

        $columns = $this->objHelper->getRows($sql);
        if ($columns === false) {
            echo "Error for:<br>$sql<br>";
            echo $this->objHelper->error;
            return false;
        }
        if ($this->objHelper->rows == 0) {
            echo "No data found for:<br>$sql<br>";
            echo $this->objHelper->error;
            return false;
        }
        $ipointer = 0;
        foreach ($columns as $column) {
            $fields[$ipointer] = $column->COLUMN_NAME;
            $ipointer++;
        }
        $sql = 'SELECT ';
        $ipointer = 0;

        foreach ($fields as $field) {
            if ($field == 'id') {
                $found_id = true;
            }
            if ($field == 'password') {

            } else {
                if ($ipointer > 0) {
                    $sql .= ', ';
                }
                $sql .= $field;
                $ipointer++;
            }
        }
        $sql .= ' FROM ' . $dbPrefix;
        if (substr($table, 0, 1) == '#') {
            $sql .= substr($table, 3);
        } else {
            $sql .= $table;
        }
        if ($found_id) {
            $sql .= ' ORDER BY id DESC';
        }
        echo $sql;
        if ($this->objHelper->showSql($sql)) {
            echo "<h5>End of records for " . $table . "</h5>";
        } else {
            echo 'Error: ' . $this->objHelper->error . '<br>';
        }
        $back = "administrator/index.php?option=com_ra_tools&task=reports.showSchema";
        echo $this->objHelper->backButton($back);
    }

    public function showTableSchema() {
        $table = $this->objApp->input->getCmd('table', '');
        $config = Factory::getConfig();
        $database = $config->get('db');
//        $dbPrefix = $config->get('dbprefix');
        $objTable = new ToolsTable();
        ToolBarHelper::title($this->prefix . 'Schema for ' . $database . ' ' . $table);
        $target = 'index.php?option=com_ra_tools&task=reports.showTableSchema&table=' . $table;
        echo $this->objHelper->showPrint($target);
        $objTable->add_header("Seq,Column name,Type,Max size,Null,Key");
        $sql = "SELECT ORDINAL_POSITION,COLUMN_NAME,DATA_TYPE,IS_NULLABLE,";
        $sql .= "CHARACTER_MAXIMUM_LENGTH,COLUMN_KEY ";
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $table . "' ";
        $sql .= "ORDER BY ORDINAL_POSITION";
        $columns = $this->objHelper->getRows($sql);

        foreach ($columns as $column) {
            $objTable->add_item(number_format($column->ORDINAL_POSITION));
            $objTable->add_item($column->COLUMN_NAME);
            $objTable->add_item($column->DATA_TYPE);
            $objTable->add_item($column->CHARACTER_MAXIMUM_LENGTH);
            $objTable->add_item($column->IS_NULLABLE);
            $objTable->add_item($column->COLUMN_KEY);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo ($objTable->num_rows - 1) . ' columns in the table<br>';
        $back = "administrator/index.php?option=com_ra_tools&task=reports.showSchema";
        echo $this->objHelper->backButton($back);
    }

    public function showUsersForGroup() {
        $group = $this->objApp->input->getCmd('group', '');
// In this parameter, commas will have been replaced by underscores
        $group_codes = str_replace('_', ',', $group);
        ToolBarHelper::title('Ramblers Reports');
        $sql = "SELECT u.name AS 'Name', u.email ";
        $sql .= "from #__ra_profiles AS a ";
        $sql .= 'INNER JOIN #__users AS u ON u.id = a.id ';
        $sql .= 'WHERE a.ra_group_code ' . $this->objHelper->buildGroups($group_codes);
        $rows = $this->objHelper->getRows($sql);
//      Show link that allows page to be printed
        $target = 'index.php?option=com_ra_tools&task=reports.showUsersForGroup&group=' . $group;
        echo '<h4>Users following ' . $group_codes . '</h4>';
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header("Name,Email");
        foreach ($rows as $row) {
            $objTable->add_item($row->Name);
            $objTable->add_item($row->email);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton('administrator/index.php?option=com_ra_tools&task=reports.countUsers');
//        echo "<p>";
    }

}
