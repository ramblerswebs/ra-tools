<?php

/**
 * @version     1.0.8
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 10/12/22 CB created from com ramblers
 * 14/12/22 CB remove report on schema
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$app = Factory::getApplication();
$user = Factory::getApplication()->loadIdentity();

$objHelper = new ToolsHelper;
// set callback in globals so reports can return as appropriate
Factory::getApplication()->setUserState('com_ra_wf.callback', 'reports');
echo "<h2>Reports</h2>";

$back = 'index.php?option=com_ra_tools&view=reports';

$objTable = new ToolsTable();
$objTable->width = 30;

$objTable->add_column("Report", "R");
$objTable->add_column("Action", "L");
$objTable->generate_header();
/*
  $objTable->add_item("<b>Summaries</b>");
  $objTable->add_item("");
  //$objTable->generate_line("", 1);
  $objTable->generate_line("");

  // if (ComponentHelper::isEnabled('com_ra_wf', true);


  $objTable->add_item("Total walks by Group");
  $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_wf&task=reports.countWalks", "Go", False, "link-button button-p0555"));
  $objTable->generate_line();

  $objTable->add_item("Groups without walks");
  $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_wf&task=reports.groupsNoWalks", "Go", False, "link-button button-p0555"));
  $objTable->generate_line();

  $objTable->add_item("Download CSV of walk leaders");
  $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_wf&task=reports.walkLeaders", "Go", False, "link-button button-p0555"));
  $objTable->generate_line();

  $objTable->add_item("Show walks without Leader");
  $objTable->add_item($objHelper->buildButton('index.php?option=com_ra_tools&task=reports.NoLeader', 'Go', False, 'red'));
  $objTable->generate_line();

 */
$objTable->add_item("Show Events from JSON feed, by Area");
$objTable->add_item($objHelper->buildButton('index.php?option=com_ra_tools&task=reports.showEvents', 'Go', False, 'red'));
$objTable->generate_line();

if ($user->id == 0) {
    echo "<b>More reports are available if you log in</b><br>";
} else {
    $objTable->add_item("Logfile");
    $objTable->add_item($objHelper->buildLink("index.php?option=com_ra_wf&task=reports.showLogfile&offset=1", "Go", False, "link-button button-p0555"));
    $objTable->generate_line();
}

$objTable->generate_table();
echo "<p>&nbsp;</p>";
?>




