<?php

/**
 * @version     4.0.7
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 05/06/22 CB remove diagnostic display
 * 01/07/22 CB changes for new version of Ramblers Library
 * 18/02/23 CB printed programmes
 * 27/03/23 CB updated for Joomla 4
 * 31/08/23 CB add option for "list"
 * 02/09/23 CB optionally restrict by lookahead_weeks
 * 20/22/23 CB allow display of specified Area or Group
 * 11/12/23 CB correction to filter out cancelled walks
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$objHelper = new ToolsHelper;

// If code is for an Area, expand into list of Groups
if (strlen($this->group) == 2) {
    $this->group = $objHelper->expandArea($this->group);
}

$options = new RJsonwalksFeedoptions($this->group);
$objFeed = new RJsonwalksFeed($options);
//if ((strlen($this->group) == 4) AND ($this->show_cancelled == '0')) {
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
/*
  if ((strlen($this->group) == 4) AND ($this->filter_radius > "0")) {
  $item = $objHelper->getItem('SELECT latitude, longitude from #__ra_groups where code="' . $this->group . '"')
  // Filter for walks within given dstance of the centroid of the GLA, Baylis Road, SE1
  $feed->filterDistanceFromLatLong($item->latitude, $item->longitude, $this->filter_radius);
  }
  $display= new RJsonwalksStdNextwalks(); // code to display the walks in a particular format
  $display->noWalks(24);
 */
switch ($this->display_type) {
    case 'simple':
        $display = new RJsonwalksStdFulldetails();
        break;
    case "map":
        // 31/08/23 following line works withou any problem
        $display = new RJsonwalksLeafletMapmarker();
        break;
    case "calendar":
        //$display = new RJsonwalksLeafletMapmarker();
        $display = new RJsonwalksStdDisplay();
        $tabOrder = ['Calendar', 'Map', 'List'];
        $display->setTabOrder($tabOrder);
        break;
    case "list":
        $display = new RJsonwalksStdDisplay();
        $tabOrder = ['List'];
        $display->setTabOrder($tabOrder);
        break;
    case "tabs":
        $display = new RJsonwalksStdDisplay();
        break;
    case "cancelled":
        $display = new RJsonwalksStdCancelledwalks();
        break;
    case "csv":
        $display = new RJsonwalksStdWalkcsv();
        break;
    case "printed":
        $display = new RJsonwalksStdWalksprinted();
        break;
    case "leaders":
        $display = new RJsonwalksStdLeaderstable();
        break;
    default:
        $display = new RJsonwalksStdDisplay();
}

if ($this->intro != '') {
    echo $this->intro . "<br>";
}
if ($group_type == "list") {
    $display->displayGroup = true;
}

$display->displayGradesIcon = false;
$display->emailDisplayFormat = 2;      // don't show actual email addresses

$objFeed->Display($display);           // display walks information
//if ($this->objHelper->isSuperuser()) {
echo "group=" . $this->group;
if ($this->limit > 0) {
    echo ', limit=' . $this->limit;
}
if ($this->lookahead_weeks > "0") {
    echo ', Dates from ' . date_format($datefrom, 'd/m/Y') . ' to ' . date_format($dateto, 'd/m/Y');
}
//}
/*
if (JDEBUG) {
    echo "display_type $this->display_type<br>";
    echo "group $this->group";
    echo ", restrict_walks $this->restrict_walks";
    echo ", limit $this->limit";
    echo ", lookahead_weeks $this->lookahead_weeks";
    echo ", show_cancelled $this->show_cancelled<br>";
}
if ((strlen($this->group) == 4) AND ($this->filter_radius > "0")) {
  echo ', within ' . $this->filter_radius . ' kms of the centre of the group';
}
 */
