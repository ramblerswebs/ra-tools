<?php

/**
 * Shows the contents of a given file
 *
 *
 * @version     4.0.10
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 30/11/22 CB Created from com ramblers
 * 13/12/23 CB Use subfolder within com_ra_tools
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$objTable = new ToolsTable;
$error = '';

//  Find any introduction for the page
$intro = $this->menu_params->get('page_intro');
//  Find any page footer
$page_footer = $this->menu_params->get('page_footer', '1');

$file = $this->menu_params->get('file', '');
$folder = 'images/com_ra_tools';
$sub_folder = $this->menu_params->get('sub_folder', '');
$show_file = $this->menu_params->get('show_file', 'N');
$download = $this->menu_params->get('download', 'N');

if ($file == '') {
    Factory::getApplication()->enqueueMessage('Filename is blank ', 'error');
}
if ($sub_folder != '') {
    $folder .= '/' . $folder;
    if (!file_exists(JPATH_SITE . '/' . $folder)) {
        $text = "Folder does not exist: " . JPATH_SITE . $folder . ". Unable to list contents";
        // Add a message to the message queue
        Factory::getApplication()->enqueueMessage($text, 'error');
        return;
    }
}

$target = $folder . '/' . $file;
if (!file_exists($target)) {
    $text = 'File ' . $file_name . ' does not exist';
    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}

echo '<h2>Displaying file ' . $file . '</h2>';
if ($intro != '') {
    echo $intro . "<br>";
}

$file_extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));
$allowed_extensions = array('csv', 'txt', 'html', 'htm');
if (!in_array($file_extension, $allowed_extensions)) {
    $error .= 'Extension of ' . $file_extension . ' not permitted (must be csv,txt, htm or html) ';
}
if ($error == '') {
    if ($file_extension == 'csv') {
        $objTable->show_csv($target);
    } elseif ($file_extension == 'txt') {
        echo file_get_contents($target) . '<br>';
    } else {
        $data_file = new SplFileObject($target);

//        Loop until we reach the end of the file.
        while (!$data_file->eof()) {
            // Echo one line from the file.
            echo $data_file->fgets();
        }
// Unset the file to call __destruct(), closing the file handle.
        $data_file = null;
    }
    if ($show_file == 'Y') {
        echo 'File is ' . $target;
    }

    if ($download == 'Y') {
        echo $this->objHelper->buildLink($folder . '/' . $file, 'Download', False, 'link-button button-p0186');
    }
    echo '<br>';
} else {
    echo $error . '<br>';
}
if ($page_footer != '') {
    echo $page_footer . "<br>";
}
