<?php

/**
 * Shows the contents of a file with details of geographical points
 *
 *
 * @version     4.0.12
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 13/12/23 CB created
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

// No direct access
defined('_JEXEC') or die;

$objHelper = new ToolsHelper;
echo '<h2>' . $this->params->get('page_title') . '</h2>';

//  Find any introduction for the page
$intro = $this->menu_params->get('page_intro');
//  Find any page footer
$page_footer = $this->menu_params->get('page_footer', '1');

$file = $this->menu_params->get('file', '');
$sub_folder = $this->menu_params->get('sub_folder', '');
$show_file = $this->menu_params->get('show_file', 'N');
$download = $this->menu_params->get('download', 'N');

if (!$intro == '') {
    echo $intro . '<br>';
}

$folder = 'images/com_ra_tools/' . $sub_folder;

if (!file_exists(JPATH_SITE . '/' . $folder)) {
    $text = "Folder does not exist: " . JPATH_SITE . $folder . ". Unable to list contents";

    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

$target = $folder . '/' . $file;
if (!file_exists($target)) {
    $text = 'File ' . $target . ' does not exist';

    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

$list = new RLeafletCsvList($target);
$list->display();

if ($show_file == 'Y') {
    echo 'File is ' . $target;
}
if ($download == 'Y') {
    echo $this->objHelper->buildLink($target, 'Download', False, 'link-button button-p0186');
}
//echo 'user ' . $this->user->id;
//if ($this->user->id > 0) {
//    if ($this->canDo->get('core.edit' == true)) {
//      echo $this->objHelper->buildLink($target, 'Download', False, 'link-button button-p0186');
//    }
//}
echo '<br>';
if ($page_footer != '') {
    echo $page_footer . "<br>";
}

