<?php

/*

 * 15/03/23 CB converted to Joomla 4
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

echo '<h2>Edit Profile</h2>';
echo '<form action="'
 . Route::_('index.php?option=com_ra_tools&view=area&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id)
 . '" method="post" name="adminForm" id="foo-form" class="form-validate">' . PHP_EOL;
//echo $this->getForm()->renderField('nation_id');
//echo $this->getForm()->renderField('ra_home_group');
//echo $this->getForm()->renderField('name');
//echo $this->getForm()->renderField('details');
//echo $this->getForm()->renderField('website');
//echo $this->getForm()->renderField('co_url') . PHP_EOL;
echo '<input type="hidden" name="task" value="">' . PHP_EOL;
echo HTMLHelper::_('form.token');
echo '</form>' . PHP_EOL;

