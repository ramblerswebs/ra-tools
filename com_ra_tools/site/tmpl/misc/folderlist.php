<?php

/*
 *
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

// No direct access
defined('_JEXEC') or die;

$objHelper = new ToolsHelper;
echo '<h2>' . $this->params->get('page_title') . '</h2>';

$app = JFactory::getApplication();
$menu_params = $app->getMenu()->getActive()->getParams(); #
$sort = $menu_params->get('sort', 'ASC');
$intro = $menu_params->get('page_intro', '');
$sub_folder = $menu_params->get('sub_folder', '');
$show_folder = $menu_params->get('show_folder', 'N');

if (!$intro == '') {
    echo $intro . '<br>';
}

$folder = '/images/com_ra_tools/' . $sub_folder;
$fileTypes = array(".pdf", ".doc", ".docx", ".odt", ".zip", "png");
$this->names = array();
if (!file_exists(JPATH_SITE . $folder)) {
    $text = "Folder does not exist: " . JPATH_SITE . $folder . ". Unable to list contents";

    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
//    echo "<b>Not able to list contents of folder $folder<b>";
    return;
}

if ($handle = opendir(JPATH_SITE . $folder)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (is_dir($entry)) {

            } else {
                $names[] = $entry;
            }
        }
    }
    closedir($handle);
}

if ($names) {
//            echo count($names) . ' files in ' . $folder . '<br>';
    // Remove trailing slash
    $base = substr(uri::base(), 0, -1) . $folder;
//            echo 'Base = ' . $base . '<br>';
} else {
    echo 'No files in ' . $folder;
    return 0;
}
//        var_dump($names);
natcasesort($names);

if ($sort == 'DESC') {
    $names = array_reverse($names);
}
echo "<ul>";
foreach ($names as $value) {
//            echo 'value ' . $value . '<br>';
//            echo 'file ' . $base . '/' . $value . '<br>';
    echo '<li>' . $objHelper->buildLink($base . '/' . $value, $value, true) . "</li>\n";
}
echo "</ul>";
echo count($names) . ' files';
if ($show_folder == 'Y') {
    echo ' in ' . $folder;
}
echo '<br>';
