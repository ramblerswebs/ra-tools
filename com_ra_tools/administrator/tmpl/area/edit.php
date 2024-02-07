<?php
/*
 * @package Ramblers Calendar Download (com_ra_calendar_download) for Joomla! >=3.0
 * @author Keith Grimes
 * @copyright (C) 2018 Keith Grimes
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * 17/09/20 CB naming updated
 * 12/12/22 CB correct title
 * 15/03/23 CB converted to Joomla 4
 * 24/07/23 CB Lat & Long
 * 18/09/23 CB Nation
 * 16/10/23 CB Contact for chair
 * */
//
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$input = $app->input;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');

$layout = 'edit';
$tmpl = $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

/*
  <?php

  echo '<form action="';
  echo Route::_('index.php?option=com_ra_tools&view=area&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id);
  echo '" method="post" name="adminForm" id="foo-form" class="form-validate">' . PHP_EOL;
  echo $this->getForm()->renderField('nation_id');
  echo $this->getForm()->renderField('code');
  echo $this->getForm()->renderField('name');
  echo $this->getForm()->renderField('details');
  echo $this->getForm()->renderField('website');
  echo $this->getForm()->renderField('co_url') . PHP_EOL;
  echo '<input type="hidden" name="task" value="">' . PHP_EOL;
  echo HTMLHelper::_('form.token');
  echo '</form>' . PHP_EOL;
  ?>
 *  *  */
?>

<form action="<?php echo Route::_('index.php?option=com_ra_tools&view=area&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="foo-form" class="form-validate">
    <?php
    echo $this->getForm()->renderField('nation_id');
    echo $this->getForm()->renderField('code');
    echo $this->getForm()->renderField('name');
    echo $this->getForm()->renderField('details');
    echo $this->getForm()->renderField('website');
    echo $this->getForm()->renderField('co_url');
    echo $this->getForm()->renderField('chair_id');
    echo $this->getForm()->renderField('latitude');
    echo $this->getForm()->renderField('longitude');
    echo '<input type="hidden" name="task" value="">';
    echo HTMLHelper::_('form.token');
    ?>
</form>
