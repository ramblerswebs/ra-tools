<?php

/**
 * @version     4.0.10
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 06/06/23 CB correct links to reports
 * 17/07/23 CB only count walks if Walks Follow has been installed
 * 03/08/23 CB remove diagnostics
 * 03/09/23 CB show location
 * 16/10/23 CB Clusters, Chair email
 * 08/01/24 CB table responsive
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers.css', 'com_ta_tools/css/ramblers.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$objHelper = new ToolsHelper;

// See if RA Walks has been installed
$com_ra_walks = ComponentHelper::isEnabled('com_ra_walks', true);
$target_reports = 'index.php?option=com_ra_walks&view=reports_area&callback=area_list&area=';

// get the current invokation parameters so that after drilldown, the
// subordinate programs can return to the same state
$current_uri = Uri::getInstance()->toString();
$callback_key = 'com_ra_walks.callback_matrix';
Factory::getApplication()->setUserState($callback_key, $current_uri);

echo '<form action="';
echo Route::_('index.php?option=com_ra_tools&view=area_list');
echo '" method="post" name="adminForm" id="adminForm">';
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div id="j-main-container" class="j-main-container">';
echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
if (empty($this->items)) {
    echo '<div class="alert alert-info">';
    echo '<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only">' . Text::_('INFO') . '</span>';
    echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
    echo '</div>';
} else {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped" id="mail_lstList">';
// Start actual table of contents
    echo '<thead>';

    echo '<tr>';
    echo '<th scope="col" style="width:1%; min-width:85px" class="text-center">';
    echo HTMLHelper::_('searchtools.sort', 'Code', 'a.code', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Nation', 'n.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%" class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'Cluster', 'a.cluster', $listDirn, $listOrder) . '</th>';

    echo '<th></th>';
    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Website', 'a.website', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%" class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'Chair', 'c.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:5%" class="text-center">';
    echo 'Groups' . '</th>';
    if ($com_ra_walks) {
        echo '<th>All Walks</th>';
        echo '<th>Future Walks</th>';
        echo '<th>All Events</th>';
    } else {
        echo '<th>Location</th>';
    }
    echo '</th>' . PHP_EOL;
    echo '</tr>';
    echo '</thead>' . PHP_EOL;

    foreach ($this->items as $i => $item) {
        $group_count = $objHelper->getValue('SELECT COUNT(id) FROM #__ra_groups WHERE code LIKE "' . $item->code . '%"');
        echo "<tr>";
        echo "<td>" . $item->code . "</td>";
        echo "<td>" . $item->name . "</td>";
        echo "<td>" . $item->nation . "</td>";
        echo "<td>" . $item->cluster . "</td>";

        echo '<td>' . $objHelper->showLocation($item->latitude, $item->longitude, 'O') . '</td>';
        echo '<td>';
        if ($item->website == "") {
            echo $objHelper->buildLink($item->co_url, $item->co_url, True, "");
        } else {
            echo $objHelper->buildLink($item->website, $item->website, True, "");
        }
        echo '</td>';

        echo '<td>';
        echo $this->generateEmail($item->chair_id, $item->chair, $item->website);
        echo '</td>';

        echo '<td class="">';
        echo '<a href="index.php?option=com_ra_tools&view=area&code=' . $item->code . '">';
        echo $group_count . '</a>';
        echo '</td>';

        if ($com_ra_walks) {
            echo "<td>";

            $sql = "SELECT COUNT(id) FROM #__ra_walks  ";
            $sql .= "WHERE group_code LIKE '" . $item->code . "%'";
//            echo $sql;
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks > 0) {
                // If number of walk greater then 50, allow further analyses, else show them as a list
                if ($count_walks > 50) {
                    echo $count_walks . $objHelper->imageButton("I", $target_reports . $item->code . '&scope=A');
                } else {
                    $target = 'http://localhost/index.php?option=com_ra_walks&view=reports_matrix&report_type=L';
                    $target .= '&mode=A&scope=A&opt=' . $item->code;
                    $target .= '&row=A&row_value=' . ToolsHelper::convert_to_ASCII($item->name);
                    echo $objHelper->buildLink($target, $count_walks, True);
                }
            }
            echo "</td>";

            echo "<td>";
            $sql = "SELECT count(walks.id) from #__ra_walks as walks ";
            $sql .= "WHERE walks.group_code LIKE '" . $item->code . "%'";
            $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
            $sql .= "AND (state=1) ";
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks > 0) {
                // If number of walk greater then 50, allow further analyses, else show them as a list
                if ($count_walks > 50) {
                    echo $count_walks . $objHelper->imageButton("I", $target_reports . $item->code . '&scope=A');
                } else {
                    $target = 'http://localhost/index.php?option=com_ra_walks&view=reports_matrix&report_type=L';
                    $target .= '&mode=A&scope=F&opt=' . $item->code;
                    $target .= '&row=A&row_value=' . ToolsHelper::convert_to_ASCII($item->name);
                    echo $objHelper->buildLink($target, $count_walks, True);
                }
            }
            echo '</td>';
            echo '<td>';
            echo $objHelper->buildLink("index.php?option=com_ra_tools&task=reports.showEventsArea&callback=area_list&code=" . $item->code, 'Show feed');
            echo '</td>';
        } else {
            echo '<td>' . $objHelper->showLocation($item->latitude, $item->longitude, 'O') . '</td>';
//echo '<td>lat ' . $row->latitude . ', long ' . $row->latitude . '</td>';
        }
        echo "</tr>";
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td>";
    $target = $current_uri . '&layout=print&tmpl=component';
    echo $objHelper->buildLink($target, 'Print');
    echo "</td>";
    echo "<td>";
    // load the pagination.
    echo $this->pagination->getListFooter();
    echo "</td>";

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
echo '<input type="hidden" name="task" value="">';
//echo '<input type="hidden" name="boxchecked" value="0">';
echo HTMLHelper::_('form.token');
echo '</div><!-- row -->' . PHP_EOL;
echo '</div><!-- col-md-12 -->' . PHP_EOL;
echo '</div><!-- j-main-container -->' . PHP_EOL;
echo '</form>';

//
if ($this->nation != '') {
    $nation_id = Factory::getApplication()->input->getCmd('nation', '0');
    $sql = 'SELECT COUNT(*) FROM #__ra_areas WHERE nation_id=' . $nation_id;
    $count = $objHelper->getValue($sql);
    echo 'Number of Areas for ' . $this->nation . '=' . $count;
} else {
    if ($this->cluster != '') {
        $sql = 'SELECT COUNT(*) FROM #__ra_areas WHERE cluster="' . $this->cluster . '"';
        $count = $objHelper->getValue($sql);
        echo 'Number of Areas for Cluster ' . $this->cluster . '=' . $count;
    }
}