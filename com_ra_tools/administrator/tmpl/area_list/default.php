<?php
/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

//echo __FILE__ . '<br>';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$editIcon = '<span class="fa fa-pen-square me-2" aria-hidden="true"></span>';
$objHelper = new ToolsHelper;
$self = 'index.php?option=com_ra_tools&view=area_list';
echo '<form action="' . Route::_($self) . '" method="post" name="adminForm" id="adminForm">' . PHP_EOL;
echo '<div class="row">' . PHP_EOL;
echo '<div class="col-md-12">' . PHP_EOL;
echo '<div id="j-main-container" class="j-main-container">' . PHP_EOL;
echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
if (empty($this->items)) {
    echo '<div class="alert alert-info">' . PHP_EOL;
    echo '<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only">';
    echo Text::_('INFO') . '</span>' . PHP_EOL;
    echo Text::_('JGLOBAL_NO_MATCHING_RESULTS') . PHP_EOL;
    echo '</div>' . PHP_EOL;
} else {
    echo '<table class="table" id="ra_areasList">' . PHP_EOL;
    echo '<thead>' . PHP_EOL;
    echo '<tr>' . PHP_EOL;

    echo '<td style="width:1%" class="text-center">' . PHP_EOL;
    echo HTMLHelper::_('grid.checkall') . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:1%; min-width:85px" class="text-center">' . PHP_EOL;
    echo HTMLHelper::_('searchtools.sort', 'Code', 'a.code', $listDirn, $listOrder) . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:10%">' . PHP_EOL;
    echo HTMLHelper::_('searchtools.sort', 'Nation', 'n.name', $listDirn, $listOrder) . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:10%">' . PHP_EOL;
    echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder) . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:10%">' . PHP_EOL;
    echo HTMLHelper::_('searchtools.sort', 'Website', 'a.website', $listDirn, $listOrder) . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:20%" class="d-none d-md-table-cell">' . PHP_EOL;
    echo HTMLHelper::_('searchtools.sort', 'CO link', 'a.co_url', $listDirn, $listOrder) . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:5%" class="d-none d-md-table-cell">' . PHP_EOL;
    echo 'Groups' . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '<th scope="col" style="width:5%" class="d-none d-md-table-cell">' . PHP_EOL;
    echo 'ID' . PHP_EOL;
    echo '</th>' . PHP_EOL;

    echo '</tr>' . PHP_EOL;
    echo '</thead>' . PHP_EOL;
    echo '<tbody>' . PHP_EOL;

    $n = count($this->items);
    foreach ($this->items as $i => $item) {
        $group_count = $objHelper->getValue('SELECT COUNT(id) FROM #__ra_groups WHERE code LIKE "' . $item->code . '%"');
        echo '<tr class="row' . $i % 2 . '">' . PHP_EOL;

        echo '<td class="text-center">' . PHP_EOL;
        echo HTMLHelper::_('grid.id', $i, $item->id) . PHP_EOL;
        echo '</td>' . PHP_EOL;

        echo '<td class="article-status">' . PHP_EOL;
        echo $item->code . PHP_EOL;
        echo '</td>' . PHP_EOL;

        echo '<td class="">' . PHP_EOL;
        echo $item->nation . PHP_EOL;
        echo '</td>' . PHP_EOL;

        echo '<td class="has-context">' . PHP_EOL;
        if ($this->canDo->get('core.edit')) {
            echo '<a class="hasTooltip" href="' . Route::_('index.php?option=com_ra_tools&task=area.edit&id=' . $item->id) . '">' . PHP_EOL;
            echo $editIcon . $this->escape($item->name) . '</a>' . PHP_EOL;
        } else {
            echo $this->escape($item->name) . PHP_EOL;
        }
        echo '</td>' . PHP_EOL;

        echo '<td class="">' . PHP_EOL;
        if ($item->website != '') {
            echo $objHelper->buildLink($item->website, '', true) . PHP_EOL;
        }
        echo '</td>' . PHP_EOL;

        echo '<td class="d-none d-md-table-cell">' . PHP_EOL;
        if ($item->co_url != '') {
            echo $objHelper->buildLink($item->co_url, '', true) . PHP_EOL;
        }
        echo '</td>' . PHP_EOL;

        echo '<td class="">' . PHP_EOL;
        if ($group_count > 0) {

            echo '<a href="' . Route::_('index.php?option=com_ra_tools&view=grouplist&area=' . $item->code) . '">' . PHP_EOL;
            echo $group_count . '</a>' . PHP_EOL;
        }
        echo '</td>' . PHP_EOL;

        echo '<td class="d-none d-md-table-cell">' . PHP_EOL;
        echo $item->id . PHP_EOL;
        echo '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
    }
    echo '</tbody>' . PHP_EOL;
    echo '</table>' . PHP_EOL;

// load the pagination.
    echo $this->pagination->getListFooter();
}
?>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</div>
</div>
</form>

