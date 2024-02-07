<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
$objTable = new ToolsTable;

$callback = 'area_list';

$target_walks = "index.php?option=com_ra_tools&task=reports.showGroupWalks&code=";

$sql = "SELECT nations.name as Nation, `groups`.code as Code, `groups`.name as 'Group', ";
$sql .= "`groups`.website, `groups`.co_url,  `groups`.id ";
$sql .= "FROM #__ra_nations AS nations ";
$sql .= "INNER JOIN #__ra_areas AS areas ON areas.nation_id = nations.id ";
$sql .= "INNER JOIN #__ra_groups AS `groups` ON `groups`.area_id = areas.id ";
$sql .= "WHERe areas.code='" . $this->area_code . "' ";
$sql .= "ORDER BY `groups`.code, `groups`.name";
//echo $sql;
$rows = $this->objHelper->getRows($sql);
$record_count = $this->objHelper->rows;
if ($record_count == 0) {
    echo "No data found for " . $sql . "<br>";
} else {
    $objTable->add_column("Nation", "L");
    $objTable->add_column("Code", "L");
    $objTable->add_column("Name", "L");
    $objTable->add_column("Website", "L");
    $objTable->add_column("CO link", "L");
    $objTable->generate_header();
//        echo "cols=" . $objTable->get_Columns();
//        while ($row = mysqli_fetch_array($rs, MYSQLI_BOTH)) {
    foreach ($rows as $row) {
        $objTable->add_item($row->Nation);
        $objTable->add_item($row->Code);
        $objTable->add_item($row->Group);
        if ($row->website == "") {
            $objTable->add_item("");
        } else {
            $details = $this->objHelper->buildLink($row->website, $row->website, True, "");
            $objTable->add_item($details);
        }
        if ($row->co_url == "") {
            $objTable->add_item("");
        } else {
            $details = $this->objHelper->buildLink($row->co_url, $row->co_url, True, "");
            $objTable->add_item($details);
        }

        $objTable->generate_line();
    }
    $objTable->generate_table();
}
if ($record_count > 0) {
    echo "Count=" . $record_count . '<br>';
}

$target = "administrator/index.php?option=com_ra_tools&view=";

echo $this->objHelper->backButton($target . $callback);

