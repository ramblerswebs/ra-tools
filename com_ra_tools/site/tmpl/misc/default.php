<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * @version     1.0.0
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 03/01/22 use prefix ra, not cb
 */
// No direct access
//use Joomla\CMS\Factory;

\defined('_JEXEC') or die;
echo "start of code from " . __FILE__ . PHP_EOL;
echo '<h2>THIS IS DEFAULT VIEW</h2>';
echo "constant JPATH BASE= " . JPATH_BASE . "<br>";

echo "<h4>" . JPATH_COMPONENT_SITE . "</h4>";
$helper = JPATH_SITE . '/components/com_ra_tools/src/Helpers/Tools.php';
if (file_exists($helper)) {
    require_once $helper;
//    echo "Found " . $helper . '<br>';
} else {
    echo "Cant find " . $helper . '<br>';
}
$table = JPATH_SITE . '/components/com_ra_tools/src/Helpers/Table.php';
if (file_exists($table)) {
    require_once $table;
    echo "Found " . $table . '<br>';
} else {
    echo "Cant find " . $table . '<br>';
}
//JLoader::register('ToolsHelper', $helper);

$objHelper = new ToolsHelper;
echo $objHelper->showQuery('Select * from #__mywalks');

use Ramblers\Component\Ra_tools\Site\Helpers;

//$objTable = new Table;

echo '<h2>Areas</h2>';
$sql = "SELECT code, name,website,co_url ";
$sql .= "FROM #__ra_areas ";
$sql .= "ORDER BY code LIMIT 20";
echo $sql;
$rows = $objHelper->getRows($sql);
//$objTable = new Table();
//$objTable->add_header('Code,Name,website,CO site');
foreach ($rows as $row) {
//    $objTable->add_item($row->code);
//    $objTable->add_item($row->name);
//    $objTable->add_item($row->website);
//    $objTable->add_item($row->co_website);
//    $objTable->generate_line();
    echo $row->code . '<br>';
}
//$objTable->generate_table();
$back = "index.php?option=com_ra_tools&task=reports.showUserGroups";
//echo $objHelper->backButton($back);

//echo $objHelper->showQuery('Select * from #__mywalks');

