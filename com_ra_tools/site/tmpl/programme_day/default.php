<?php

/**
 * @version     4.0.12
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 05/06/22 CB remove diagnostic display
 * 01/07/22 CB changes for new version of Ramblers Library
 * 18/02/23 CB printed programmes
 * 27/03/23 CB updated for Joomla 4
 * 20/11/23 CB use table-responsive for navigation table
 * 04/12/23 CB remove diagnostic display
 * 22/01/24 CB use LookaheadWeeks
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

//use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$objHelper = new ToolsHelper;
// Load the component params
//echo "Displaying $this->display_type for $this->group, day=$this->day, intro=$this->intro<br>";
if ($this->intro != '') {
    echo $this->intro . "<br>";
}
// Generate the seven entries at the top of the page, as a table with a single row
// The current day is shown in bold, others as buttons
//echo '<table style="margin-right: auto; margin-left: auto;">';
echo '<div class="table-reponsive">' . PHP_EOL;
echo '<table>';
echo "<tr>";
$week = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
for ($i = 0; $i < 7; $i++) {
    echo "<td>";
    $weekday = $week[$i];
    $target = 'index.php?option=com_ra_tools&view=programme_day&day=' . $weekday . '&Itemid=' . $this->menu_id;

//    $link = URI::base() . $target;
    $link = $target;
    if ($this->day == $weekday) {
        echo '<b>' . $this->day . '<b>';
    } else {
        if ($i < 5) {
            $colour = 'p7474';
        } else {
            $colour = 'p0555';
        }
        //echo $objHelper->buildLink($target . $weekday, $weekday, False, "link-button button-" . $colour);
        echo $objHelper->buildLink($link, $weekday, False, "link-button button-" . $colour);
    }
    echo "</td>";
}
echo "</tr>";
echo "</table>";
echo '</div>' . PHP_EOL;    // table-reponsive
$options = new RJsonwalksFeedoptions($this->group);
$objFeed = new RJsonwalksFeed($options);
if ($this->show_cancelled == '0') {
    $objFeed->filterCancelled();
}
if ($this->limit > 0) {
    $objFeed->limitNumberWalks($this->limit);
}
if ($this->lookahead_weeks > "0") {
    $datefrom = new DateTime();
    $weeks = (int) number_format($this->lookahead_weeks, 2);
//  DateInterval is described in https://www.php.net/manual/en/class.dateinterval.php
    $period = new DateInterval('P' . $weeks . 'W');
    $dateto = new DateTime();
    $dateto->add($period);
    $objFeed->filterDateRange($datefrom, $dateto);
}
$objFeed->filterDayofweek(array($this->day));
/*
  if (!$days == "0") {
  $datefrom = new DateTime(); // set date to today
  $dateto = new DateTime();   // set date to today
  date_add($dateto, date_interval_create_from_date_string($days . "days"));
  $objFeed->filterDateRange($datefrom, $dateto);
  }
 */

switch ($this->display_type) {
    case 'simple':
        $display = new RJsonwalksStdFulldetails();
        break;
    case "map":
        $display = new RJsonwalksLeafletMapmarker();
        break;
    case "list":
        $display = new RJsonwalksStdDisplay();
        $tabOrder = ['List'];
        $display->setTabOrder($tabOrder);
        break;
    case "tabs":
        $display = new RJsonwalksStdDisplay();
        break;
    default:
        $display = new RJsonwalksStdFulldetails();
}

if ($intro != '') {
    echo $intro . "<br>";
}
if ($group_type == "list") {
    $display->displayGroup = true;
}

$display->displayGradesIcon = false;
$display->emailDisplayFormat = 2;      // don't show actual email addresses

$objFeed->Display($display);           // display walks information
//echo "group=" . $group . ", dow=" . $dow . "<br>";
echo "group=" . $this->group;
if ($this->limit > 0) {
    echo ', limit=' . $this->limit;
}

