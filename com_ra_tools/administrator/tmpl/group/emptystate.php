<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_FOOS',
    'formURL' => 'index.php?option=com_ra_tools',
    'helpURL' => 'https://docs.stokeandnewcastleramblers.org.uk',
    'icon' => 'icon-copy',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_ra_tools')) {
    $displayData['createURL'] = 'index.php?option=com_ra_tools&task=group.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);

