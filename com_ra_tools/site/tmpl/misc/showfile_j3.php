<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Joomla\CMS\Factory;

//  Find any introduction for the page
$intro = $this->menu_params->get('intro');
//  Find any page footer
$page_footer = $this->menu_params->get('page_footer', '1');

$objTable = new Table;
$error = '';

$document_library = $params->get("document_library");

// remove any leading slash
if (substr($document_library, 0, 1) == '/') {
    echo 'library was ' . $document_library . '<br>';
    $document_library = substr($document_library, 1);
    echo 'library is ' . $document_library . '<br>';
}


$folder = JPATH_ROOT . '/';

// remove any trailing slash
if (substr($this->params, -1) == '/') {
    $folder .= substr($document_library, 0, strlen($this->params) - 1);
} else {
    $folder .= $document_library;
}

if (!is_dir($folder)) {
    $error .= $folder . ' is not a valid folder ';
}
//echo $this->params . ' / ' . $document_library . '<br>';
$file = $this->menu_params->get('file');
if ($file == '') {
    $error .= 'Filename is blank ';
}

// Find the highest version of this file
$filename = pathinfo($file, PATHINFO_FILENAME);
$ext = pathinfo($file, PATHINFO_EXTENSION);

$versions = glob($folder . '/' . $filename . '-' . "*." . $ext);
if ($versions) {
    sort($versions);
    $count = count($versions);
// get the file with the highest number (some numbers may be missing)
    $target = end($versions);
    $found = pathinfo(basename($target), PATHINFO_FILENAME);
    $download = $found . '.' . pathinfo(basename($target), PATHINFO_EXTENSION);
//    echo "download=" . $download . '<br>';
    // Copy the latest version, so it can be downloaded as basename
//    echo 'copying ' . $target . ' to ' . $folder . '/' . $filename . '.' . $ext . '<br>';
    copy($target, $folder . '/' . $filename . '.' . $ext);
//    echo 'Last=' . $found . ', ' . substr($found, -2) . '<br>';
    $version = substr(pathinfo(end($versions), PATHINFO_FILENAME), -2);
}
echo '<h2>Displaying file ' . $file . ', version=' . $version . '</h2>';

echo "<h4>File was uploaded: " . date("D j M Y, H:i:s", filemtime($target)) . '</h4>';

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

    if ($page_footer != '') {
        echo $page_footer . "<br>";
    }
    $download_target = Juri::base() . $document_library . '/' . $file;
    echo $this->objHelper->buildLink($download_target, 'Download', true, 'link-button button-p0186');
    if (Factory::getUser()->id > 0) {
        $upload = Juri::base() . 'components/com_ra_tools/upload_file.php';
        echo $this->objHelper->buildLink($upload, 'Upload new version', true, 'link-button button-p0583');
    }
} else {
    echo $error . '<br>';
}

