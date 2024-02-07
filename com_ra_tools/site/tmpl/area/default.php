<?php

/**
 * @version     1.2.1
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * This template file will be included by the JViewLegacy class.
 * Therefore, here, $this refers to the Ra_toolsView Enhancements class, which extends it.
 *
 *
 * Shows list of Groups for the specified Area
  // 12/04/21 change organising_group to group_code
  // 15/05/21 use back tick around `groups`
  // 31/01/22 Don't use recordsets
  // 07/05/22 remove diagnostic display
  // 16/03/23 copied from Joomla 3
 * 23/05/23 CB added walksCsv and loadWalks
 * 05/05/23 CB Future walks, show feed, link to reports
 * 06/06/23 CB correct links to reports
 * 12/06/23 CB started to acc count of Events - backed out again
 * 17/07/23 CB only count walks if Walks Follow has been installed
 * 17/07/23 CB display walk details using com_ra_wf/reports.showWalks, not com_ra_tools/reports.showWalks
 * 24/07/23 CB correct link to external website, add display of walks
 * 21/08/23 CB use JsonHelper
 * 09/10/24 CB delete code for import/export of walks
 */
//
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

echo "<!-- Code from ' . __FILE__ . ' -->" . PHP_EOL;
$JsonHelper = new JsonHelper;
$objHelper = new ToolsHelper;
$objTable = new ToolsTable();

$objApp = Factory::getApplication();
// callback will be set if invoked from WalksFollow
$callback = $objApp->input->getCmd('callback', '');

$sql = "SELECT a.id, a.name AS area_name, n.name as nation_name ";
$sql .= "FROM #__ra_areas AS a ";
$sql .= "LEFT JOIN #__ra_nations AS n on n.id = a.nation_id ";
$sql .= "WHERE a.code='" . $this->area . "' ";
$item = $objHelper->getItem($sql);
if (is_null($item)) {
    echo $objHelper->message;
    $id = 0;
    $name = "?";
    $nation = '';
} else {
    $id = $item->id;
    $name = $item->area_name;
    $nation = $item->nation_name;
}

// See if Walks Follow has been installed
$com_ra_walks = ComponentHelper::isEnabled('com_ra_walks', true);
if ($com_ra_walks) {
    $target_walks = "index.php?option=com_ra_walks&task=reports.showWalks&code=";
} else {
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // temp fix to support walksmanager.staffs
    $target_walks = "index.php?option=com_tool&view=reports.showProgramme&code=";
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
$target_reports = 'index.php?option=com_ra_walks&callback=area&view=reports_group&group_code=';
$target_reports .= '&callback=' . Toolshelper::convert_to_ASCII('index.php?option=com_ra_tools&view=area&code=' . $this->area);
$target_programme = 'index.php?option=com_ra_tools&view=programme&group=';
echo "<h2>Ramblers Groups for " . $nation . ' ' . $name . "(" . $this->area . ")</h2>";

$sql = "SELECT `groups`.code AS code, `groups`.name AS 'Group', ";
$sql .= "`groups`.website, `groups`.latitude, `groups`.longitude,`groups`.id ";
$sql .= "FROM  #__ra_areas AS a  ";
$sql .= "INNER JOIN #__ra_groups AS `groups` on `groups`.area_id = a.id ";
$sql .= "WHERE a.id=" . $id;
$sql .= " order by `groups`.code, `groups`.name";
//echo $sql;
$rows = $objHelper->getRows($sql);
$record_count = $objHelper->rows;
if ($record_count == 0) {
    echo "No data found for " . $sql . "<br>";
} else {
    $heading = 'Code,Name,,Website,';
    if ($com_ra_walks) {
        $heading .= 'All walks,Future walks,';
        if ($callback == "areas") {
            $heading .= 'Followings';
        } else {
            $heading .= 'Action';
        }
    } else {
        $heading .= 'Walks';
    }
    $objTable->add_header($heading);
    /*
      $objTable->add_column("code", "L");
      $objTable->add_column("Name", "L");
      $objTable->add_column('');
      $objTable->add_column("Website", "L");
      if ($com_ra_walks) {
      $objTable->add_column("All walks", "C");
      $objTable->add_column("Future walks", "C");
      if ($callback == "areas") {
      $objTable->add_column("Followings", "C");
      } else {
      $objTable->add_column("Action", "C");
      //            $objTable->add_column("Events", "C");
      }
      }
      $objTable->generate_header();
     */
//        echo "cols=" . $objTable->get_Columns();
//        while ($row = mysqli_fetch_array($rs, MYSQLI_BOTH)) {
    foreach ($rows as $row) {
        $objTable->add_item($row->code);
        $objTable->add_item($row->Group);
        $objTable->add_item($objHelper->showLocation($row->latitude, $row->longitude, 'O'));
        if ($row->website == "") {
            $objTable->add_item("");
        } else {
            $link = $objHelper->buildLink($row->website, $row->website, True, "");
            $objTable->add_item($link);
        }

        if ($com_ra_walks) {
            $sql = "SELECT COUNT(id) FROM #__ra_walks  ";
            $sql .= "WHERE group_code='" . $row->code . "'";
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks == 0) {
                //$objTable->add_item($sql);
                $objTable->add_item("");
            } else {
                $link = $objHelper->imageButton("I", $target_reports . "&scope=A&group_code=" . $row->code, true);
                $objTable->add_item($count_walks . $link);
            }
            $sql = "Select count(walks.id) from #__ra_walks as walks ";
            $sql .= "Where walks.group_code='" . $row->code . "'";
            $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
            $sql .= "AND (state=1) ";
            $count_walks = $objHelper->getValue($sql);
            $link = '';
            if ($count_walks == 0) {
                $objTable->add_item("");
            } else {
                $link = $objHelper->imageButton("I", $target_reports . "&scope=F&group_code=" . $row->code, true);
                $objTable->add_item($count_walks . $link);
//                $target = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=group-walk&api-key=742d93e8f409bf2b5aec6f64cf6f405e&groups=' . $row->code;
                $target = $JsonHelper->groupFeed($row->code);
                $link = $objHelper->buildLink($target, 'Show feed', True);
            }
            $link .= $objHelper->buildLink("index.php?option=com_ra_tools&task=area.refreshWalks&code=" . $row->code, 'Refresh');
            $objTable->add_item($link);
//            $objTable->add_item($this->getCountEvents());
            if ($callback == "areas") {
                $record_count2 = $objHelper->countGroupFollowers($row->code);
                if ($record_count2 == 0) {
                    $objTable->add_item("");
                } else {
                    $objTable->add_item($record_count2);
                }
            }
        } else {
            $objTable->add_item($objHelper->buildLink($target_programme . $row->code, 'Show'));
        }
        $objTable->generate_line();
    }
    $objTable->generate_table();
}
if ($record_count > 0) {
    echo "Count=" . $record_count;
}
if ($callback == "areas") {
    $target = "index.php?option=com_ra_tools&view=areas";
} else {
    $target = "index.php?option=com_ra_tools&view=area_list";
}
echo $objHelper->backButton($target);

echo "<!-- End of code from ' . __FILE__ . ' -->" . PHP_EOL;
?>
