<?php

/**
 * Various common functions used throughout the project
 *
 * @version     1.0.13
 * @package     com_ra_tools
 * @author charlie

 * 13/03/23 CB converted to Joomla 4
 * 23/05/23 CB getActions (canDo is now deprecated)
 * 18/07/23 CB take images from media, not assets, showLogo
 * 24/07/23 CB change display of location using OSM
 * 02/08/23 CB align with code from laptop (buildButton)
 * 06/07/23 CB canUserEdit
 * 23/08/23 CB lookupColourCode: set class = $colour, not $class .= colour (gave error with certain versions of PHP)
 * 05/09/23 CB showPrinter - add website base for icon
 * 18/09/23 CB correct buildBotton, add radius to imageButton
 * 11/10/23 Cb function top
 * 30/11/23 CB replace Factory::getDbo with Factory::getContainer()->get('DatabaseDriver');
 * 11/12/23 CB remove redundant code from backButton
 */
/*
  replace Factory::getUser()
  with    Factory::getApplication()->loadIdentity()
 *
 *
  REPLACE
  $user = Factory::getUser($userid);
  WITH
  use Joomla\CMS\User\UserFactoryInterface;
  $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userid);
 *

  There is a long list of old style form field classes that have no equivalent in Joomla 5. For example:

  JFormFieldList
  JFormFieldText

  In Joomla 5 the namespaced classes are:

  \Joomla\CMS\Form\Field\ListField
  \Joomla\CMS\Form\Field\TextField
 */

namespace Ramblers\Component\Ra_tools\Site\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class ToolsHelper {

    public $error;
    public $userId;
    public $rows;
    public $image_folder = "/media/com_ra_tools/";
    protected $website_root;

    function __construct() {
        $this->rows = 0;
    }

    function addSlash($folder) {
// Checks that last character is a forwards slash, adds one if necessary
        if (substr($folder, (strlen($folder) - 1)) == "/") {
            echo "Last char OK;";
        } else {
            $folder .= "/";
        }
        return $folder;
    }

    function anchor($label = 'Top') {
        // returns a linkto the given anchor, e.g. <a name=top>:</a>
        $anchor = strtolower($label);
        return '<a href="#' . $anchor . '">' . $label . '</a>';
    }

    function auditButton($id, $table, $callback) {
// display a button to show audit records
// $table needs no prefix (eg ra_walks)
// within $callback, = should be replaced with EQ, and & replaced with --
        $target = 'index.php?option=com_ra_wf&task=reports.showAudit&table=' . $table . '&id=' . $id;
        if ($callback == '') {
            return $this->buildLink($target, "Show Audit", True, "link-button button-p4485");
        } else {
            $target .= '&callback=' . $callback;
            return $this->buildLink($target, "Show Audit", False, "link-button button-p4485");
        }
    }

    function backButton($target) {
        return $this->buildButton($target, 'Back', false, 'granite');
    }

    function buildButton($url, $text, $newWindow = 0, $colour = '') {
        $class = $this->lookupColourCode($colour, 'B');
        //       echo "colour=$colour, code=$code, class=$class<br>";
        return $this->buildLink($url, $text, $newWindow, $class);
    }

    function buildError($ExistingMessage, $NewMessage) {
        if ($ExistingMessage == "") {
            return $NewMessage;
        } else {
            return ($ExistingMessage . ", " . $NewMessage);
        }
    }

    function buildGroups($parameter) {
//      Builds string suitable for a database query,
//      If single group is given, returns
///      = 'G001'
//      If multiple groups are given, returns them in format:
//      IN ('GG01,'GG02') etc
        if (strlen($parameter) == 2) {
            return "LIKE '" . $parameter . "%' ";
        } else {
            if (strpos($parameter, ",") === false) {
                return "= '" . $parameter . "' ";
            } else {
                $groups = explode(",", $parameter);
                $this->group_list = "";
                foreach ($groups as $group) {
                    $this->group_list .= "'" . $group . "',";
                }
// Remove trailing comma
                return "IN (" . substr($this->group_list, 0, (strlen($this->group_list) - 1)) . ")";
            }
        }
    }

    static function buildLink($url, $text, $newWindow = 0, $class = "") {
        // N.B. cannot be used from batch programs, because Uri::root() is not available
        $q = chr(34);
        $out = PHP_EOL . "<a ";
//        echo "BuildLink: url = $url, substr=" . substr($url, 0, 4) . ", text=$text, root=" . Uri::root() . "<br>";
        if (!$class == "") {
            $out .= "class=" . $q . $class . $q;
        }
        $out .= " href=" . $q;
        if (substr($url, 0, 4) == "http") {

        } else {

//            echo substr(Juri::base(), strlen(Juri::base()) - 14, 13) . '<br>';
//            echo substr(Juri::base(), 0, strlen(Juri::base()) - 14);
//            if (substr(Juri::base(), strlen(Juri::base()) - 14, 13) == 'administrator') {
//                $out .= substr(Juri::base(), 0, strlen(Juri::base()) - 14);
//            } else {
            $out .= Uri::root();    // this seems to be derived from configuration.php/ live_site in the website root
//            }
        }
        $out .= $url . $q;
        if ($newWindow) {
            $out .= " target =" . $q . "_blank" . $q;
        } else {
            $out .= " target =" . $q . "_self" . $q;
        }
        $out .= ">";
        if ($text == "")
            $out .= $url;
        else
            $out .= $text;
        $out .= "</a>" . PHP_EOL;
//        echo "BuildLink: output= $out";
        return $out;
    }

    function buildLinkRoute($mode, $gpx, $folder = "", $class = "") {

        if ($folder == "") {
            $app = Factory::getApplication();
            $params = $app->getParams();
            $folder = $params->get('routes') . "/";
        } else {
            $path = $folder . "/";
        }

        if ($mode == "I") {   // Image link required
            $target = "index.php?option=com_ra_tools&task=misc.showRoute&gpx=";
            $target .= $folder . "/" . $gpx;
// Factory::getApplication()->enqueueMessage("target before is " . $target, 'info');
// Spaces and forwards slashes are stripped out, but replaced in
// the Controller routes.showRoute
            $target2 = str_replace("/", "xFz", $target);
            $target2 = str_replace(" ", "qHj", $target2);
// Factory::getApplication()->enqueueMessage("target after is " . $target2, 'info');
            return $this->imageButton("I", $target2, true);
        } else {
            $target = $folder . $gpx;
            if ($mode == "D") {  // Download link
                return $this->imageButton("D", $target, true);
            } else {
                return $this->buildLink($target, $gpx, 1, $class);
            }
        }
    }

    /**
     * Build the query for search from the search columns
     *
     * @param    string        $searchWord        Search for this text

     * @param    string        $searchColumns    The columns in the DB to search for
     *
     * @return    string        $query            Append the search to this query
     */
    public static function buildSearchQuery($searchWord, $searchColumns, $query) {

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $where = array();

        foreach ($searchColumns as $i => $searchColumn) {
            $where[] = $db->qn($searchColumn) . ' LIKE ' . $db->q('%' . $db->escape($searchWord, true) . '%');
        }

        if (!empty($where)) {
            $query->where('(' . implode(' OR ', $where) . ')');
        }

        return $query;
    }

    static function canDo($component) {
        // Checks that the current user is allowed to add a new record
        //$canDo = JHelperContent::getActions($component);
        //If ($canDo->get("core.create")) {
        if (Factory::getApplication()->loadIdentity()->authorise('core.create', $component)) {
            return true;
        } else {
            return "Sorry, you don't have access permission for " . $component;
        }
    }

    /**
     * Gets the edit permission for an user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     */
    public static function canUserEdit($component, $item) {
        $permission = false;
        $user = Factory::getApplication()->getIdentity();

        if ($user->authorise('core.edit', $component) || (isset($item->created_by) && $user->$component)) {
            $permission = true;
        }

        return $permission;
    }

    static function convert_to_ASCII($name) {
        $new = '';
//        echo strlen($name) . '<br>.';
        for ($i = 0; $i < (strlen($name)); $i++) {
//            echo '->' . substr($name, $i, 1) . '=';
            $token = ord(substr($name, $i, 1));
//            echo $token . '/';
            $new .= str_pad($token, 3, "0", STR_PAD_LEFT);
//            echo ' ' . $new;
//            echo '<br>';
        }
        return $new;
    }

    static function convert_from_ASCII($token) {

        $max = (strlen($token) / 3);
//        echo strlen($token) . $max . '<br>.';
        $new = '';
        $pointer = 0;
        for ($i = 0; $i < $max; $i++) {
//            echo '->' . substr($token, $pointer, 3) . '=';
            $temp = chr(substr($token, $pointer, 3));
//            echo $temp . '/';
            $new .= chr(substr($token, $pointer, 3));
//            echo ' ' . $new;
//            echo '<br>';
            $pointer = $pointer + 3;
        }
        return $new;
    }

    public function countGroupFollowers($code) {
        // Finds number of walk/followers for given Area or Group
        $sql = 'SELECT COUNT(p.id) FROM #__ra_profiles as p ';
        $sql .= 'INNER JOIN #__ra_walks_follow AS f ON f.user_id = p.id ';
        $sql .= 'INNER JOIN #__ra_walks AS w ON w.id = f.walk_id ';
        $sql .= 'WHERE w.organising_group ';
        if (strlen($code) == 2) {
            $sql .= "like'" . $code . "%' ";
        } else {
            $sql .= "='" . $code . "' ";
        }
//        $sql .= "GROUP BY p.id ";
        return $this->getValue($sql);
    }

    function countFutureEvents($type_id) {
// find the number of future, active Event of the specified type
        $sql = "SELECT count(id) as num ";
        $sql .= "from #__ra_events ";
        $sql .= "WHERE datediff(event_date, CURRENT_DATE) >=0 ";
        $sql .= "AND state=1 ";
        $sql .= "AND event_type_id=" . $type_id;
        return $this->getValue($sql);
    }

    function createAuditRecord($field_name, $old_value, $new_value, $object_id, $table) {
//        echo "Helper::createAudit:$table - $field_name: id=$object_id ,$old_value -> $new_value <br>";
        if (trim($old_value) == trim($new_value)) {

        } else {
            if (trim($old_value) == "") {
                $record_type = "A";
                $field_value = $new_value;
            } else {
                if (trim($new_value) == "") {
                    $record_type = "D";
                    $field_value = $old_value;
                } else {
                    $record_type = "U";
                    $field_value = $old_value . "->" . $new_value;
                }
            }

            $sql = "INSERT INTO #__" . $table . "_audit ( ";
            $sql .= "date_amended,";
            $sql .= "object_id,";
            $sql .= "field_name,";
            $sql .= "record_type,";
            $sql .= "field_value) Values (";
            $sql .= "'" . date("Y") . "-" . date("m") . "-" . date("d") . " ";
            $sql .= date("H") . ":" . date("i") . ":" . date("s") . "',";
            $sql .= $object_id . ",";
            $sql .= "'" . $field_name . "',";
            $sql .= "'" . $record_type . "',";
//      $sql .= "'" & gTool.CheckApostrophy(Left$(strFieldValue, 100)) & "',"
            if (strpos($field_value, "'") > 0) {
//			 echo chr(92) . "|" ;
                $sql .= "'" . substr($field_value, 0, strpos($field_value, "'"));
                $sql .= "|";
                $sql .= substr($field_value, strpos($field_value, "'") + 1) . "')";
            } else {
                $sql .= "'" . HTMLSpecialChars($field_value) . "')";
            }
//            echo "DatabaseAccess; CreateAuditRecord $sql<br>";
            if ($this->executeCommand($sql)) {
                $this->message = "CreateAudit: Updated $field_name for record $object_id in table $table: old=" . $old_value . ", new=" . $new_value;
                return 1;
            } else {
                $this->message .= "<br>$sql";
                return 1;
            }
        }
    }

    function executeCmd($sql) {
// Deprecated !
        $this->executeCommand($sql);
    }

    function executeCommand($sql) {
        $config = Factory::getConfig();
        $dbPrefix = $config->get('dbprefix');
        $sql2 = str_replace("#__", $dbPrefix, $sql);
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $db->setQuery($sql2);
            $db->execute();
            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
//            if (JDEBUG) {
//                echo 'Helper::executeCommmand' . $this->error . '<br>';
//            }
            return false;
        }
    }

    function expandArea($area_code) {
// Given a two character Area code, returns a list of constituent Group code
        $list = "";
        $sql = "Select code from #__ra_groups as `groups` ";
        $sql .= "where code like '" . $area_code . "%' ";
        $rows = $this->getRows($sql);
        foreach ($rows as $row) {
            $list .= $row->code . ",";
        }
        return substr($list, 0, (strlen($list) - 1));
    }

    function exportQuery($sql, $filename) {

        $date = date('Y-m-d');
        $export_file = $filename . '-' . $date . ':' . (new DateTime())->format('g:i:s') . '.csv"';

        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
        $app->setHeader('Content-disposition', 'attachment; filename="' . $export_file, true);
        $app->setHeader('Cache-Control', 'no-cache', true);
        $app->sendHeaders();

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery($sql);

        try {
            if (!$result = $db->loadAssocList()) {
                echo "No Qualifying Rows";
                if (JDEBUG) {
                    Factory::getApplication()->enqueueMessage($sql, 'info');
                }
            } else {
                foreach (array_keys($result[0]) as $token) {
                    echo $db->escape($token) . ',';
                }
                echo PHP_EOL;
                foreach ($result as $row) {
                    foreach ($row as $token) {
                        echo $db->escape($token) . ',';
                    }
                    echo PHP_EOL;
                }
            }
        } catch (Exception $e) {
// never show getMessage() to the public
            Factory::getApplication()->enqueueMessage("Query Syntax Error: " . $e->getMessage(), 'error');
        }
        $app->close();
    }

    function feedbackButton($encoded) {
        echo $this->buildLink("components/com_ra_tools/Feedback.php?ref=" . $encoded, "Feedback", True, "link-button button-p1815");
    }

    public function generateButton($target, $type, $newWindow = False) {
// Generic function to display Home / Back / Prev / Next buttons etc
// Find the reference point for the icon
        $base = Uri::root();
        if (substr($base, -14, 13) == 'administrator') {
            $target = substr($base, 0, strlen($base) - 14) . $target;
        } else {
            $target = $base . $target;
        }
// Display a button, using the given target
        $icon = 'media/com_ra_tools/' . strtolower($type) . '.png';
        $alt = $type;
        $q = chr(34);
        $link = "<a href=" . $q . $target . $q;
        if ($newWindow) {
            $link .= " target =" . $q . "_blank";
        } else {
            $link .= " target =" . $q . "_self";
        }
        $link .= $q . "><img src=" . $q;
        //$link .= $base . $icon . $q . " alt=" . $q . $alt . $q . " width=" . $q . "60" . $q;
        // $link .= " height=" . $q . "20" . $q . "/></a>" . PHP_EOL;
        $link .= $base . $icon . $q . " alt=" . $q . $alt . $q . "/></a>" . PHP_EOL;

        return $link;
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return  CMSObject
     *
     * Usage: $canDo = ToolsHelper::getActions();
     *  if ($canDo->get('core.create')) {
     *      $toolbar->addNew('walk.add');
     *   }
     */
    public static function getActions($component = 'com_ra_tools') {
        $user = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $component));
        }

        return $result;
    }

    function getAreaCode($group_code) {
        $sql = "SELECT id FROM #__ra_areas WHERE code='";
        return $this->getValue($sql . substr($group_code, 0, 2) . "'");
    }

    function getItem($sql) {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $db->setQuery($sql);
            $db->execute();
            $this->rows = $db->getNumRows();
//            print_r($this->rows);
            $item = $db->loadObject();
            return $item;
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
//            if (JDEBUG) {
//                echo 'Helper::getItem' . $this->error . '<br>';
//            }
            return false;
        }
    }

    function getRows($sql) {
        /*
          $objTable->add_header("aa,bb");
          $rows = $this->objHelper->getRows($sql);
          foreach ($rows as $row) {
          $objTable->add_item($row->aa);
          $objTable->add_item($row->bb);
          $objTable->generate_line();
          }
          $objTable->generate_table();
         */
        $this->rows = 0;
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $db->setQuery($sql);
            $db->execute();
            $this->rows = $db->getNumRows();
//            print_r($this->rows);
            $rows = $db->loadObjectList();
            return $rows;
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
//            if (JDEBUG) {
//                echo $this->error;
//            }
            return false;
        }
    }

    function get_superuser() {
// deprecated function
        return $this->isSuperuser();
    }

    function getValue($sql, $debug = 0) {
        if ($debug == 1) {
            echo $sql . '<br>';
        }
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $db->setQuery($sql);
            return $db->loadResult();
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
            return false;
        }
    }

    function imageButton($mode, $target, $newWindow = False) {
        if (substr($target, 0, 4) == "http") {
            $root_to_base = "";
        } else {
            $root_to_base = Uri::root();
        }

        if ($mode == "D") {
            $icon = "download.png";
            $alt = "Download";
        } elseif ($mode == "DD") {
            $icon = "drilldown.png";
            $alt = "Drilldown";
        } elseif ($mode == "E") {
            $icon = "email.png";
            $alt = "Send email";
        } elseif ($mode == "E2") {
            $icon = "email2.png";
            $alt = "Send email";
        } elseif ($mode == "F") {
            $icon = "tick.png";
            $alt = "Follow";
        } elseif ($mode == "G") {
            $icon = "gps.png";
            $alt = "GPX";
        } elseif ($mode == "GO") {
            $icon = "google.png";
            $alt = "Google";
        } elseif ($mode == "I") {
            $icon = "info.png";
            $alt = "Info";
        } elseif ($mode == "L") {
            $icon = "logo_90px.png";
            $alt = "Logo";
        } elseif ($mode == "M") {
            $icon = "icon-cog.png";
            $alt = "Update";
        } elseif ($mode == "P") {
            $icon = "map_pin.png";
            $alt = "Info";
        } elseif ($mode == "R") {
            $icon = "radius.png";
            $alt = "Radius";
        } elseif ($mode == "W") {
            $icon = "walksfinder.png";
            $alt = "walksfinder";
        } elseif ($mode == "X") {
            $icon = "cross.png";
            $alt = "Delete";
        }
        $q = chr(34);
        $link = "<a href=" . $q;
        $link .= $root_to_base . $target . $q;
//        echo $target;
        if ($newWindow) {
            $link .= " target =" . $q . "_blank";
        } else {
            $link .= " target =" . $q . "_self";
        }
        $link .= $q . "><img src=" . $q;
        //$link .= Uri::root() . $this->image_folder . $icon . $q . " alt=" . $q . $icon . $q . " width=" . $q . "30" . $q;
        $link .= Uri::root() . $this->image_folder . $icon . $q . " alt=" . $q . $icon . $q . " width=" . $q . "20" . $q;
        $link .= " height=" . $q . "20" . $q . "/></a>" . PHP_EOL;
        return $link;
    }

    function isFollower($walk_id, $user_id) {
// See if the given User is following thie given Walk
        $sql = "Select id from #__ra_walks_follow where walk_id=";
        $sql .= $walk_id . " AND user_id=" . $user_id;
//        echo $sql . "<br>";
        return $this->getValue($sql);
    }

    function isSuperuser() {
// Returns true or false
// For the current user, looks up the id, and if member of SuperUser group
        $user = Factory::getApplication()->getIdentity();
        $this->userId = $user->id;
        return $user->authorise('core.admin');
    }

    function logMessage($record_type, $ref, $message) {
        $q = chr(34);
        $sql = "INSERT INTO #__ra_logfile (`log_date`, `record_type`, `ref`, `message`) VALUES ";
        $sql .= "(CURRENT_TIMESTAMP," . $q . $record_type . $q . "," . (int) $ref . "," . $q . $message . $q . ")";
        $this->executeCommand($sql, $error);
    }

    function lookupArea($area_code) {
        return $this->getValue("SELECT name FROM #__ra_areas where code='" . $area_code . "' ");
    }

    /*
      --mintcake:#9BC8AB;
      --sunset:#F08050;
      --granite:#404141;
      --rosycheeks:#F6B09D;
      --sunrise:#F9B104;
      --cloudy:#FFFFFF;
      --mintcakedark:#ABD8BB;
      --cancelled:#C60C30;
      --lightgrey: #C0C0C0;
      --midgrey: #808080;

      --pantone0110:#D7A900;  mustard
      --pantone0159:#C75B12;  orange
      --pantone0186:#C60C30;  red
      --pantone0555:#206C49;  darkgreen
      --pantone0583:#A8B400;  lightgreen
      --pantone1815:#782327;  maroon
      --pantone4485:#5B491F;  mud
      --pantone5565:#8BA69C;  gray
      --pantone7474:#007A87;  teal
     *
      case ($colour="mintcake'); code = :#9BC8AB;
      --sunset:#F08050;
      --granite:#404141;
      --rosycheeks:#F6B09D;
      --sunrise:#F9B104;
      --cloudy:#FFFFFF;
      --mintcakedark:#ABD8BB;
      --cancelled:#C60C30;
      --lightgrey: #C0C0C0;
      --midgrey: #808080;
     */

    static function lookupColourCode($colour, $option = 'B') {
        $code = '';
        switch ($colour) {
            case ($colour == 'mustard');
                $code = '0110';
                break;
            case ($colour == 'orange');
                $code = '0159';
                break;
            case ($colour == 'red');
                $code = '0186';
                break;
            case ($colour == 'darkgreen');
                $code = '0555';
                break;
            case ($colour == 'lightgreen');
                $code = '0583';
                break;
            case ($colour == 'maroon');
                $code = '1815';
                break;
            case ($colour == 'mud');
                $code = '4485';
                break;
            case ($colour == 'grey');
                $code = '5565';
                break;
            case ($colour == 'teal');
                $code = '7474';
                break;
            default;
                $class = $colour;
        }
        if ($code == '') { // colour name given
            $class .= $code;
        } else {
            if ($option == 'B') {
                $class = 'button-p' . $code;
            } else {
                $class = 'pantone' . $code;
            }
        }
        if ($option == 'B') {
            return 'link-button ' . $class;
        } else {
            return 'table table-striped ' . $class;
        }
    }

    function lookupGroup($group_code) {
        return $this->getValue("SELECT name FROM #__ra_groups where code='" . $group_code . "' ");
    }

    function lookupMonth($mm) {
        switch ($mm) {
            case ($mm == 1);
                return "Jan";
            case ($mm == 2);
                return "Feb";
            case ($mm == 3);
                return "Mar";
            case ($mm == 4);
                return "Apr";
            case ($mm == 5);
                return "May";
            case ($mm == 6);
                return "Jun";
            case ($mm == 7);
                return "Jul";
            case ($mm == 8);
                return "Aug";
            case ($mm == 9);
                return "Sep";
            case ($mm == 10);
                return "Oct";
            case ($mm == 11);
                return "Nov";
            case ($mm == 12);
                return "Dec";
            default;
                return "zzz";
        }
    }

    function readFile($filename) {
// returns the content of the specified file from the media directory

        $root_to_base = Uri::root();

        $directory = "media/com_ra_tools/";
        if (file_exists($directory . $filename)) {
            return file_get_contents($directory . $filename);
        } else {
            $this->error = "(file $filename not found)";
            return false;
        }
    }

    static function selectLimit($limit, $target) {
// Generate a drop down list of integers for Limit
// When an item is selected from the list, a Javascript function will be invoked
// to pass control the the URL specified in $target

        $options[] = 10;
        $options[] = 20;
        $options[] = 30;
        $options[] = 50;
        $options[] = 100;

        echo 'Limit: <select id=selectLimit name=limit onChange="changeLimit(' . chr(39) . $target . chr(39) . ')">';
        for ($i = 0; $i < 5; $i++) {
            echo '<option value=' . $options[$i];
            if ($options[$i] == $limit) {
                echo ' selected';
            }
            echo '>' . $options[$i];
            echo '</option>';
        }
        echo '</select> ';
    }

    static function selectScope($scope, $target) {
// Generate a drop down list, but ensure the current state is listed first
// Overly complicated - could use select to specify current seleted option
        switch ($scope) {
            case ($scope == 'F');
                $options = 'FHAD';
                break;
            case ($scope == 'H');
                $options = 'HFAD';
                break;
            case ($scope == 'D');
                $options = 'DFHA';
                break;
            default;
                $options = 'AFHD';
        }

        echo 'Scope: <select id=selectScope name=scope onChange="changeScope(' . chr(39) . $target . chr(39) . ')">';
        for ($i = 0; $i < 4; $i++) {
            echo '<option value=' . substr($options, $i, 1) . '>';
            if (substr($options, $i, 1) == "F") {              // Future walks
                echo 'Future walks';
            } elseif (substr($options, $i, 1) == "H") {   // Historic
                echo 'Past walks';
            } elseif (substr($options, $i, 1) == "D") {   // Draft/ Cancelled/Archived
                echo 'Draft/Cancelled walks';
            } else {
                echo 'All walks';
            }
            echo '</option>';
        }
        echo '</select>';
    }

    static function sanitisePath($parent_folder, $sub_folder) {
// trim whitespace and slashes from both ends of the parameters
        $parent_folder = trim($parent_folder, "/\\ \t\n\r\0\x0B");
        $sub_folder = trim($sub_folder, "/\\ \t\n\r\0\x0B");

        $sub_folder = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $sub_folder);
//        echo "sub $sub_folder<br>";
//compile the full absolute path
        $path = $parent_folder;
        if (!$sub_folder == "") {
            $path .= DIRECTORY_SEPARATOR . $sub_folder;
        }
//        echo "Helper: path=$path<br>";
        return $path;
    }

    function sendEmail($to, $reply_to, $subject, $body, $attachment = '') {

        if ((substr(JPATH_ROOT, 14, 6) == 'joomla') OR (substr(JPATH_ROOT, 14, 6) == 'MAMP/h')) {  // Development
            $this->message .= $to . ' ';
            return true;
        } else {
            $objMail = Factory::getMailer();
            $config = Factory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $objMail->setSender($sender);
            $objMail->addRecipient($to);
            $objMail->addReplyTo($reply_to);
            $objMail->isHtml(true);
            $objMail->Encoding = 'base64';
            $objMail->setSubject($subject);
            $objMail->setBody($body);

//          Add embedded image
//          This adds the logo as an attachment, which could then be referenced as cid:xxx)
//            $objMail->AddEmbeddedImage(JPATH_COMPONENT_SITE . '/media/com_ra_mailman/logo.png', 'logo', 'logo.jpg', 'base64', 'image/png');
//           Optional file attached
            if ($attachment != '') {
                $objMail->addAttachment($attachment);
            }
            $send = $objMail->Send();
        }
        return $send;
    }

    public function showLocation($latitude, $longitude, $mode = 'O') {
        /*
         * Returns an image button with a link to the location
         * mode = G - Google maps
         * mode = S = Streetmap
         * other OSM
         */


        if ((floatval($latitude) == 0) or (floatval($longitude) == 0)) {
            return '';
        }
        if ($mode == 'G') {
            $target = "https://www.google.com/maps?q=";
            $target .= $latitude;
            $target .= "," . $longitude;
            return $this->imageButton("GO", $target, True);
        } elseif ($mode == 'S') {
            $target = 'https://streetmap.co.uk/loc/' . $latitude . ',';
            if ($longitude > 0) {
                $target .= 'E' . $longitude;
            } else {
                $target .= 'W' . abs($longitude);
            }
            return $this->imageButton("I", $target, True);
        } else {
            // https://www.openstreetmap.org/?mlat=43.59021&mlon=1.40741#map=11/43.5902/1.4074
            $target = "https://www.openstreetmap.org?mlat=" . $latitude;
            $target .= "&mlon" . $longitude; // . "&zoom=13";
            $target .= '#map=13/' . $latitude . '/' . $longitude;
//            echo '<i class="fa-solid fa-map-pin"></i>';
            return $this->imageButton("P", $target, True);
        }
    }

    function showLogo($align = 'R') {
        $icon = "logo_90px.png";
// '<img src="media/com_ra_tools/logo_90px.png" alt="logo" width="40" height="64" style="float: right;">';
        return '<img src="' . $this->image_folder . "/" . $icon . '" alt="logo" width="91" height="91" style="float: right;">';
    }

    function showPrint($target, $newWindow = 0) {
// Given the URL of the current page, generates CSS to display a link for creating a
// pop-up dialogue that allows the current page to be printed (without menus etc)
// See https://docs.joomla.org/Adding_print_pop-up_functionality_to_a_component
// See also https://docs.joomla.org/Customizing_the_print_pop-up
        if (substr($target, 0, 4) == "http") {
            $root_to_base = "";
        } else {
            $root_to_base = Uri::root();
        }

        $objApp = Factory::getApplication();
        $isModal = $objApp->input->getCmd('print', '') == 1; // 'print=1' will only be present in the url of the modal window, not in the presentation of the page
        if ($isModal) {
            $href = '"#" onclick="window.print(); return false;"';
        } else {
            $href = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
            $href = "window.open(this.href,'win2','" . $href . "'); return false;";
            $href = $target . '&tmpl=component&print=1 ' . $href;
        }
        $icon = $root_to_base . "/media/com_ra_tools/print.png";
        $return = '<a href="' . $href . '" ';
        if ($newWindow) {
            $return .= 'target ="_blank" ';
        }
        $return .= '><img src="' . $icon . '" alt="Print" width="30" height="30"/></a>';
//       $return .= 'Print</a>';
        return $return;
    }

    function showQuery($sql, $class = 'table table-striped') {
// From https://stackoverflow.com/questions/49703792/how-to-securely-write-a-joomla-select-query-with-an-in-condition-in-the-where-cl

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery($sql);

        try {
            if (!$result = $db->loadAssocList()) {
                echo "No Qualifying Rows";
                if (JDEBUG) {
                    Factory::getApplication()->enqueueMessage($sql, 'info');
                }
            } else {
                echo "";
                echo "<table class=\"" . $class . "\">";
                echo "<tr><th>", implode("</th><th>", array_keys($result[0])), "</th></tr>";
                foreach ($result as $row) {
                    echo "<tr><td>", implode("</td><td>", $row), "</td></tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
// never show getMessage() to the public
            Factory::getApplication()->enqueueMessage("Query Syntax Error: " . $e->getMessage(), 'error');
        }
    }

    function showSql($sql, $csv = '', $class = 'table table-striped') {
// Parse the sql to derive the list of fields
// Usage: (assuming $sql and $filename have been defined)
// $objTable = new ToolsTable();
// $objTable->set_csv($filename);
// $objTable->add_header("aa,bb");
// $rows = $objHelper->getRows($sql);
// foreach ($rows as $row) {
// $objTable->add_item($row->aa);
// $objTable->add_item($row->bb);
// $objTable->generate_line();
// }
// $objTable->generate_table();
        $this->error = '';
        $debug = 0;
        $field_count = 0;
        $first_line = True;
        $objTable = new ToolsTable();
// if as value has been supplied, data will be written to a CSV file of that name
        $objTable->set_csv($csv);

        if ((strtoupper(substr($sql, 0, 7)) == 'SELECT ') and (strpos(strtoupper($sql), ' FROM ') > 0)) {
            $fields = explode(',', substr($sql, 6, (strpos(strtoupper($sql), ' FROM ') - 5)));
//          if a field name contains a comma, eg date_format(walk_date,'%a %e-%m-%y')
//          it may have been split unnecessarily, so check to see
//          if an entry contains an opening bracket but not closing bracket, append to it the next entry
            $ipointer = 0;
            $skip = false;
            foreach ($fields as $field) {
                if ($debug) {
                    echo "Field " . $ipointer . '=' . $field;
                    if ($skip) {
                        echo ', skip=True';
                    }
                    echo '<br>';
                }
                $open_bracket = strpos($field, '(');
                if ($open_bracket == 0) {
                    $concatenate = false;
                } else {
                    if ($debug) {
                        echo "--" . ' Found ( in ' . $field . ' at position ' . $open_bracket . '<br>';
                    }
                    $close_bracket = strpos($field, ')');
                    if ($close_bracket == 0) {
                        $concatenate = true;
                    } else {
                        $concatenate = false;

                        if ($debug) {
                            echo '-- Found ) in ' . $field . ' at position ' . $close_bracket . '<br>';
                        }
                    }
                }
                if ($concatenate) {
                    $sql_fields[$ipointer] = $fields[$ipointer] . $fields[$ipointer + 1];
                    $skip = true;
                } else {
                    if ($skip) {
                        $skip = false;
                    } else {
                        $sql_fields[$ipointer] = $fields[$ipointer];
                    }
                }
                $ipointer++;
            }
        } else {
            $this->error = "Invalid SQL: Must contain SELECT .. FROM";
            return false;
        }
        foreach ($sql_fields as $field) {
            if (strlen($field) > 0) {
                $field_count++;
                if ($debug) {
                    echo $field . '<br>';
                }
                $ipos = strpos(strtoupper($field), ' AS ');
                if ($ipos > 0) {
//                        echo 'Found' . substr($field, $ipos + 4) . '<br>';
                    $as = substr($field, $ipos + 4);
                    if (substr($as, 0, 1) == "'") {
                        $objTable->add_column(substr($as, 1, strlen($as) - 2), "L");
                    } else {
                        $objTable->add_column(substr($field, $ipos + 4), "L");
                    }
                } else {
//                        echo $field . '<br>';
                    $objTable->add_column($field, "L");
                }
            }
        }

        if ($debug) {
            echo 'Helper::showSql: sql=' . $sql . '<br>';
        }
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $db->setQuery($sql);
            $db->execute();
            $num_rows = $db->getNumRows();
            if ($debug) {
                echo 'Helper::showSql: sql=' . "Rows=$num_rows, Cols=$field_count<br>" . '<br>';
            }
            $matrix = $db->loadRowList();
            for ($row = 0; $row < $num_rows; $row++) {
                if ($first_line === True) {
                    $first_line = False;
                    $objTable->generate_header();
                }
                for ($col = 0; $col < $field_count; $col++) {
                    $objTable->add_item($matrix[$row][$col]);
                }
                $objTable->generate_line();
            }

            $objTable->generate_table();
// Count the actual rows of data
            $this->rows = $objTable->get_rows() - 1;
            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
//            echo "Helper::showSql: " . $this->error;
            return false;
        }
    }

    function standardButton($type, $target) {
        $code = '';
        switch ($type) {
            case ($type == 'Go');
                $colour = 'red';
                break;
            default;
                $colour = 'Granite';
        }
        return $this->buildButton($target, $type, false, $colour);
    }

    function testInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function validActive(&$active, &$message) {
        $active = strtoupper($active);
        if (($active === "Y") or ($active == "N")) {
            return 1;
        } else {
            $message = "Active: must be ";
            if (trim($active) == "") {
                $message .= "given";
            } else {
                $message .= "Y or N (not $active)";
            }
            return 0;
        }
    }

// end function ValidActive
    static function validateEmail($email) {
        // This should be a Rule
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
//            echo "Email address '$this->email' is considered valid.\n";
            return true;
        } else {
            return false;
        }
    }

    function walksfinderButton($walk_id) {
// display a button to show walk details from CO site
        $target = 'https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/walk-detail.aspx?walkID=';
        return $this->buildLink($target . $walk_id, "Walksfinder", True, "link-button button-p0110");
    }

}
