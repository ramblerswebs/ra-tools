<?php

/**
 * @module	mod_ra_tool walks widebar
 * @author	Charlie Bigley
 * version  1.0.0
 * @website	https://demo.stokeandnewcastleramblers.org.uk
 * @copyleft	Copyleft 2021 Charlie Bigley webmaster@stokeandnewcastleramblers.org.uk All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl.html GNU/GPL

 * 23/12/22 CB created from mod_ra_sidebar
 * 22/07/23 CB edited fro Joomla 4
 */
use Joomla\CMS\Component\ComponentHelper;

// no direct access
defined("_JEXEC") or die("Restricted access");

$group_type = $params->get('group_type', 'single');
$display_type = $params->get('display_type');
$max = (int) $params->get('max');
$cancelled = $params->get('cancelled');
//var_dump($params);
$ramblers_params = ComponentHelper::getParams('com_ra_tools');
//var_dump($ramblers_params);
if ($group_type == "single") {
    $group = $ramblers_params->get('default_group', 'NS03');
} else {
    $group = $ramblers_params->get('group_list');
}
//echo $display_type;
$options = new RJsonwalksFeedoptions($group);
$objFeed = new RJsonwalksFeed($options);

if ($display_type == "REventCalendar") {
    if ($cancelled == "0") {
        $objFeed->filterCancelled();
    }
    if ($max > 0) {
        $objFeed->noWalks($max);
    }
    $events = new REventGroup();
    $events->addWalks($objFeed); // add walks to the group of events
    $objCalendar = new REventCalendar(250); // code to display the walks in a particular format, size: 250 or 400
    $objCalendar->setMonthFormat("Y M");    // optional format of Month/Year
    $objCalendar->Display($events);
} elseif ($display_type == "RJsonwalksStdNextwalks") {
    if ($cancelled == "0") {
        $objFeed->filterCancelled();
    }
    if ($max > 0) {
        $objFeed->noWalks($max);
    }
    $display = new RJsonwalksStdNextwalks();
    $display->displayGradesIcon = false;
    $objFeed->Display($display);  // display the information
} else {
    echo 'mod_ra_tool: unrecognised option' . $display_type . '<br>';
}
//echo "group=" . $group  . ", max=" . $max . "<br>";
//echo  "<br>";

