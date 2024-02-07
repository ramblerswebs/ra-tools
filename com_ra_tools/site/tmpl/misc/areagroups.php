<?php

/**
 * Shows the names and description of Groups in the Area, using information from
 * table #__ra_groups. Optionally includes a link to their walking programme
 *
 *
 * @version     4.0.6
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 30/11/22 CB Created from com ramblers
 * 31/08/23 CB use view programme to show walks
 * 21/11/23 CN indent text for each Group
 */
// No direct access
defined('_JEXEC') or die;

// use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

$objHelper = new ToolsHelper;
$app = JFactory::getApplication();

//$menu_params = $app->getMenu()->getActive()->getParams();
//var_dump($this->menu_params) . "<br>";

$area = substr($this->menu_params->get('area', 'NS'), 0, 2);

$page_intro = $this->menu_params->get('page_intro');
$area_info = substr($this->menu_params->get('area_info', 'N'), 0, 1);
$programme = $this->menu_params->get('programme', 0);

$target_radius = 'index.php?option=com_ra_tools&view=programme&layout=radius&group=';

// If display of the walking programme is required, it would be possible to
// alow choice of the format required. However, in the initial versions, the
// display_type alwasys defaults to 'simple'
if ($programme !== '0') {
    $target_walks = 'index.php?option=com_ra_tools&view=programme&group=';
}

$sql = "SELECT * FROM #__ra_areas WHERE code='" . $area . "'";
$item = $objHelper->getItem($sql);
echo "<h2>" . $item->name . " of the Ramblers</h2>";
if (!$page_intro == '') {
    echo $page_intro;
}
if ($area_info == 1) {
    if ($item->website == '') {
        echo $objHelper->buildLink($item->co_url, $item->co_url) . '<br>';
    } else {
        echo $objHelper->buildLink($item->website, $item->website) . '<br>';
    }
    echo $item->details;
}
//echo "<h3>Groups in " . $item->name . "</h3>";
$sql = "SELECT * FROM #__ra_groups WHERE code like '" . $area . "%' ORDER BY name";
$rows = $objHelper->getRows($sql);
if ($rows === False) {
    echo 'No data found for ' . $sql . '<br>';
}
foreach ($rows as $row) {
    $heading = '<h4>' . $row->name . ' ' . $row->code;
    if ($programme !== '0') {
        $heading .= $objHelper->buildLink($target_walks . $row->code, ' Show walks');
    }
    $heading .= '</h4>';
    echo $heading;

    echo '<p style="padding-left: 30px;">' . $row->details . '<br>';
    if ($row->website == '') {
        echo $objHelper->buildLink($row->co_url, $row->co_url, True) . '<br>';
    } else {
        echo $objHelper->buildLink($row->website, $row->website, True) . '</p>';
    }
}
echo "<!-- End of code from ' . __FILE__ . ' -->" . PHP_EOL;
?>

