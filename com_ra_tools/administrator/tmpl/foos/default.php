
<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
// use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$canChange = true;
//$assoc = Associations::isEnabled();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_ra_tools&task=ra_tools.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
}
?>
Hello Foos
<form action = "<?php echo Route::_('index.php?option=com_ra_tools'); ?>" method = "post" name = "adminForm" id = "adminForm">
    <div class = "row">
        <div class = "col-md-12">
            <div id = "j-main-container" class = "j-main-container">
                <?php if (empty($this->items)) :
                    ?>
                    <div class="alert alert-warning">
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="fooList">
                        <thead>
                            <tr>
                                <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
                                    <?php echo Text::_('COM_FOOS_TABLE_TABLEHEAD_NAME'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('COM_FOOS_TABLE_TABLEHEAD_ID'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $n = count($this->items);
                            foreach ($this->items as $i => $item) :
                                ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <th scope="row" class="has-context">
                                        <div>
                                            <?php echo $this->escape($item->name); ?>
                                        </div>
                                        <?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
                                        <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_ra_tools&task=foo.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->name)); ?>">
                                            <?php echo $editIcon; ?><?php echo $this->escape($item->name); ?></a>

                                    </th>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>


