<?php

/**
 * @version     4.0.12
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 10/04/22 tweaked
 * 11/06/22 CB Show route library to logged in members
 * 20/11/23 CB strip leading slash from name of gpx file if necessary
 * 04/12/23 CB take lat/long from ra_groups for home group
 * 22/01/24 CB check folder exists
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

$home_group = $this->params->get('default_group');
$sql = 'SELECT latitude, longitude FROM #__ra_groups WHERE code="' . $home_group . '"';

$item = $this->objHelper->getItem($sql);
$latitude = $item->latitude;
$longitude = (float) $item->longitude;
if (($longitude > 1)
        or ($longitude < -5)) {
    JFactory::getApplication()->enqueueMessage($latitude . ' Longitude must be in range -5 - +1', 'error');
    $longitude = 1;
}
if (($latitude > 58)
        or ($latitude < 50)) {
    JFactory::getApplication()->enqueueMessage($latitude . ' Latitude must be in range 50-58', 'error');
    $latitude = 52;
}
//echo '<h4>' . $latitude . ',' . $longitude . '</h4>';
$app = JFactory::getApplication();
$menu_params = $app->getMenu()->getActive()->getParams();
//var_dump($menu_params) . "<br>";
//$title = $this->params->get('page_title', '');
if (is_null($menu_params)) {
    $parent_folder = "images";
    $sub_folder = "";
    $display_type = "RJsonwalksStdFulldetails";
    $title = "Settings not found";
    $download = "None";
} else {
    $display_type = $menu_params->get('display_type');
    $intro = $menu_params->get('page_intro');
    $download = $menu_params->get('download');
    $parent_folder = $menu_params->get('parent_folder');
    $sub_folder = $menu_params->get('sub_folder');
    $path = $this->objHelper->sanitisePath($parent_folder, $sub_folder);
    // if only showing a single file, $gpx will be the full name of the file to be displayed
    $gpx = $menu_params->get('gpx');
    if (substr($gpx, 0, 1) == '/') {
        $gpx = substr($gpx, 1);
    }
}

echo '<h2>' . $this->params->get('page_title') . '</h2>';
if (!file_exists($parent_folder)) {
    $text = 'File ' . $parent_folder . ' does not exist';
    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}
if (!file_exists($path)) {
    $text = 'File ' . $path . ' does not exist';
    // Add a message to the message queue
    Factory::getApplication()->enqueueMessage($text, 'error');
    return;
}
if (!$intro == "") {
    echo $intro . '<br>';
}
if (($display_type == "RLeafletMapdraw") or ($display_type == 'P')) {
    $object = new RLeafletMapdraw();
    $object->setCenter($latitude, $longitude, 10); // lat, long, zoom
    $object->display();
} else {
    if (($display_type == "RLeafletGpxMaplist") or ($display_type == 'L')) {
        $map = new RLeafletGpxMaplist();
        $map->addDownloadLink = $download; // "None" no download link, "Users" link if registered user; "Public" link for public
        $map->folder = $path;
        $map->displayTitle = False;
        $map->display();
    } else {  // display_type = S
        $target = JPATH_SITE . '/' . $gpx;
        // Check that the given file (still) exists
        if (file_exists($target)) {
            $path_parts = pathinfo($target);
            if (strtolower($path_parts['extension']) != "gpx") {
                $app = Factory::getApplication();
                $app->enqueueMessage('GPX: Route file is not a gpx file: ' . $target, 'error');
                echo "<p><b>UGPX: Route file is not a gpx file: $gpx</b></p>";
                return;
            }
        } else {
            $app = Factory::getApplication();
            $app->enqueueMessage('GPX: Route file not found: ' . $target, 'error');
            echo "<p><b>Unable to display gpx file $target</b></p>";
            return;
        }

        $map = new RLeafletGpxMap();  // standard software to read json feed and decode file
        $map->linecolour = '#782327'; // optionally set the route's line colour
        $map->addDownloadLink = $download; //  "None" no download link, "Users" link if registered user; "Public" link for public
//        $map->folder = $path;
        $map->displayPath($gpx);
        echo $gpx;
    }
    if ($this->user->id == 0) {
        if ($download == 'Users') {
            echo 'Login or Register to download gpx files<br>';
        }
    } else {
        if ($display_type == 'S') {
            echo 'Route is ' . $gpx;
        } else {
            echo 'Routes being displayed from ' . $path;
        }
    }
}
echo "<!-- End of code from com_ra_tools/views/routes/tmpl/default.php -->" . PHP_EOL;
?>
