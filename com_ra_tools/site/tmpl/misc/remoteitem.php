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
$id = $menu_params->get('id', 0);
$show_details = $menu_params->get('show_details', '');

if ($id == 0) {

}
if (!$intro == '') {
    echo $intro . '<br>';
}

$id = 10;
// code fom https://slides.woluweb.be/api/api.html
$curl = curl_init();
//$url = $website . '/api/index.php/v1/content/articles?filter[category]=';
$url = $website . '/api/index.php/v1/content/articles/';
//
// HTTP request headers
$headers = [
    'Accept: application/vnd.api+json',
    'Content-Type: application/json',
    sprintf('X-Joomla-Token: %s', trim($token)),
];

curl_setopt_array($curl, [
    CURLOPT_URL => $url . $id,
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
//echo '<b>Start of response</b><br>';
////echo $response;
//echo '<br><b>End of response</b><br>';
//echo $response->body;
//echo '<br>';

$details = json_decode($response, true);

$data = $details["data"];
$attributes = $data["attributes"];

$id = $articles['id'];

$created = $attributes['created'];
$modified = $attributes['modified'];
$title = $attributes['title'];
$text = $attributes['text'];

echo '<h2>' . $title . '</h3>';
echo $text . '<br>';

echo '<b>Created</b> ' . $created . '<br>';
if (!$modified == '') {
    echo '<b>Modified</b> ' . $modified . '<br>';
}
if ($show_details == 'Y') {
    echo "Showing articles $id from $website<br>";
}
if (JDEBUG) {
    echo '<b>Start of data</b><br>';
    var_dump($data);
    echo '<br><b>End of data</b><br>';

    echo '<b>Start of $attributes</b><br>';
    var_dump($attributes);
    echo '<br><b>End of $attributes</b><br>';
}