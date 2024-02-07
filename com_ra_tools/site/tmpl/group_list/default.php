<?php

/**
 * @version     4.0.10
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 23/08/23 CB don't show group count
 * 03/09/23 CB show location
 * 08/01/24 CB table responsive
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$objHelper = new ToolsHelper;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$target_reports = 'index.php?option=com_ra_walks&view=reports_group&group_code=';
$target_reports .= '&callback=' . Toolshelper::convert_to_ASCII('index.php?option=com_ra_tools&view=group_list');
$target_radius = 'index.php?option=com_ra_tools&view=programme&layout=radius&group=';

// See if Walks Follow has been installed
$com_ra_walks = ComponentHelper::isEnabled('com_ra_walks', true);

echo '<form action="';
echo Route::_('index.php?option=com_ra_tools&view=group_list');
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
    echo '<table class="table table-striped" id="ra_areasList">';
    // Start actual table of contents
    echo '<thead>';

    echo '<tr>';
    echo '<th scope="col" style="width:1%; min-width:85px" class="text-center">';
    echo HTMLHelper::_('searchtools.sort', 'Code', 'a.code', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Area', 'n.areas.name', $listDirn, $listOrder) . '</th>';
    echo '<th></th>';
    echo '<th scope="col" style="width:10%">';
    echo HTMLHelper::_('searchtools.sort', 'Website', 'a.website', $listDirn, $listOrder) . '</th>';

    echo '<th scope="col" style="width:20%" class="d-none d-md-table-cell">';
    echo HTMLHelper::_('searchtools.sort', 'CO link', 'a.co_url', $listDirn, $listOrder) . '</th>';
//    echo '<th></th>';
    if ($com_ra_walks) {
        echo '<th>All Walks</th>';
        echo '<th>Future Walks</th>';
    }
    echo '</th>' . PHP_EOL;
    echo '</tr>';
    echo '</thead>' . PHP_EOL;

    $n = count($this->items);
    foreach ($this->items as $i => $item) {
        $group_count = $objHelper->getValue('SELECT COUNT(id) FROM #__ra_groups WHERE code LIKE "' . $item->code . '%"');
        echo "<tr>";
        echo "<td>" . $item->code . "</td>";
        echo "<td>" . $item->name . "</td>";
        echo "<td>" . $item->area . "</td>";
        echo '<td>' . $objHelper->showLocation($item->latitude, $item->longitude, 'O') . '</td>';
        echo '<td>';
        if ($item->website == "") {
            echo '<br>';
        } else {
            echo $objHelper->buildLink($item->website, $item->website, True, "");
        }
        echo '</td>';

        echo '<td>';
        if ($item->co_url == "") {
            echo '<br>';
        } else {
            echo $objHelper->buildLink($item->co_url, $item->co_url, True, "");
        }
        echo '</td>';

//        echo '<td>' . $objHelper->buildLink($target_radius . $item->code, 'Walks', true) . '</td>';
        if ($com_ra_walks) {
            echo "<td>";
            $sql = "Select count(walks.id) from #__ra_walks as walks ";
            $sql .= "Where walks.group_code='" . $item->code . "'";
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks > 0) {
                echo $count_walks . $objHelper->imageButton("I", $target_reports . "&scope=A&group_code=" . $item->code);
            }
            echo "</td>";

            echo "<td>";
            $sql = "Select count(walks.id) from #__ra_walks as walks ";
            $sql .= "Where walks.group_code='" . $item->code . "'";
            $sql .= "AND (datediff(walk_date, CURRENT_DATE) >= 0) ";
            $sql .= "AND (state=1) ";
            $count_walks = $objHelper->getValue($sql);
            if ($count_walks > 0) {
                echo $count_walks . $objHelper->imageButton("I", $target_reports . "&scope=F&group_code=" . $item->code);
                //               echo$objHelper->buildLink("index.php?option=com_ra_walks&view=reports_group&group_code=" . $item->code . '&scope=F', $count_walks);
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
// load the pagination.
    echo $this->pagination->getListFooter();
}
echo '<input type="hidden" name="task" value="">';
//echo '<input type="hidden" name="boxchecked" value="0">';
echo HTMLHelper::_('form.token');
echo '</div><!-- row -->' . PHP_EOL;
echo '</div><!-- col-md-12 -->' . PHP_EOL;
echo '</div><!-- j-main-container -->' . PHP_EOL;
echo '</form>';

