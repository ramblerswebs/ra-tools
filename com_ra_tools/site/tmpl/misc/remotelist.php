<?php

/**
 * @version     4.0.11
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 04/02/24 CB created
 */
// No direct access
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

defined('_JEXEC') or die;
$objHelper = new ToolsHelper;
echo '<h2>' . $this->params->get('page_title') . '</h2>';

$app = JFactory::getApplication();
$menu_params = $app->getMenu()->getActive()->getParams(); #
$intro = $menu_params->get('page_intro', '');
$website = $menu_params->get('website', '');
$token = $menu_params->get('token', '');
$category_id = $menu_params->get('category_id', '');
$show_details = $menu_params->get('show_details', '');

if (!$intro == '') {
    echo $intro . '<br>';
}

// code fom https://slides.woluweb.be/api/api.html
$curl = curl_init();
$url = $website . '/api/index.php/v1/content/articles?filter[state]=1&filter[category]=';

// HTTP request headers
$headers = [
    'Accept: application/vnd.api+json',
    'Content-Type: application/json',
    sprintf('X-Joomla-Token: %s', trim($token)),
];

curl_setopt_array($curl, [
    CURLOPT_URL => $url . $category_id,
    CURLOPT_HEADER => false, // do not include header in output
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => 'utf-8',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => $headers,
        //        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // do not output result
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);    // allow xx seconds for timeout
//	curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed
//        curl_setopt($ch, CURLOPT_REFERER, "com_ra_tools"); // say who wants the feed
        ]
);
$response = curl_exec($curl);
if (curl_errno($curl)) {
    echo curl_error($curl);
}
curl_close($curl);

$details = json_decode($response, true);
//echo '<b>Start of details</b><br>';
//var_dump($details);
//echo '<br><b>End of details</b><br>';
//echo $response->body;
//echo '<br>';


$articles = $details["data"];
$count = count($articles);
$objTable = new ToolsTable();
$objTable->add_header('Article,Short text,Created,Modified');
$target = $website . '/index.php?option=com_content&view=article&id=';

for ($i = 0; $i < count($articles); $i++) {
    $id = $articles[$i]['id'];

    $attributes = $articles[$i]['attributes'];
    $created = $attributes['created'];
    $modified = $attributes['modified'];
    $title = $attributes['title'];
    $text = $attributes['text'];
    $link = $objHelper->buildLink($target . $id, $title, true);
    $objTable->add_item($link);
    $details = strip_tags($text);
    $objTable->add_item(substr($details, 0, 100) . '...');
    $objTable->add_item($created);
    $objTable->add_item($modified);
    $objTable->add_item($id);
    $objTable->generate_line();
}
$objTable->generate_table();

if ($show_details == 'Y') {
    echo "Showing $count published articles where category = $category_id from $website<br>";
}
return;                 //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
// Following sample code could not be made to work 06/02/24
//
// Code from https://github.com/alexandreelise/Manual/blob/patch-1/docs/general-concept/webservices.md
// Before passing the HTTP METHOD to CURL

use Joomla\Http\HttpFactory;
use Joomla\Uri\Uri;

$http = (new HttpFactory())->getAvailableDriver();

$url = $website . '/api/index.php/v1';
//$url = 'https://example.org/api/index.php/v1';
$uri = new Uri($url);

// Don't send payload to server
$dataString = null;

// HTTP request headers
$headers = [
    'Accept: application/vnd.api+json',
    'Content-Type: application/json',
    sprintf('X-Joomla-Token: %s', trim($token)),
];

// Timeout in seconds
$timeout = 30;

// Set path for getting all articles it will set the current uri path part
//$uri->setPath('content/articles');
$uri->setPath('content/articles?filter[state]=1&filter[category]=' . $category_id);
// Will be a PSR-7 compatible Response
$response = $http->request('GET', $uri, $dataString, $headers, $timeout);

// The response body is now a stream, so you need to do
//echo $response->body;
echo $response;
return;                      //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<



