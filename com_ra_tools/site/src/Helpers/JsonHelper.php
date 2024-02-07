<?php

/**
 * Various common functions used to access Json feeds
 *
 * @author charlie

 * 16/06/23 CB Ctreated
 */

namespace Ramblers\Component\Ra_tools\Site\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class JsonHelper {

    private $url = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=';
    public $feedType = 'walksevents';          // This can be over-written
    private $key = '&api-key=742d93e8f409bf2b5aec6f64cf6f405e';

    public function getCountEvents($code) {
        return $this->getJson('group-event', 'groups=' . $code, 'Y');
    }

    public function getJson($type, $param, $count = 'N') {
        // See https://app.swaggerhub.com/apis-docs/abateman/Ramblers-third-parties/1.0.0#/default/get_api_volunteers_walksevents
        $url = $this->url . $type . $this->key . '&' . $param;
//        $url .= '&limit=3';
//        $url .= '&dow=7';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // allow xx seconds for timeout
//			curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_wf"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . "\n");
            print('Http return: ' . $httpCode . "\n");
            echo 'Access failed : ' . $url, $httpCode;
            return;
        }

        $temp = json_decode($data);
        //       if (substr($param, 0, 3) == 'ids') {
        //           var_dump($temp);
        //           echo '<br>Data<br>';
        //           var_dump($temp->data);
//        }

        if ($count == 'Y') {
            return $temp->summary->count;
        } else {
            return $temp->data;
        }
    }

    public function getUrl($type) {
//        if ($type in($valid_types) ) {
//            $type = $param;
//        }
        return $this->url . $type . $this->key;
    }

    public function showWalk($id) {
        // Parameter may be a comma delimited array of ids
        // Returns a button with a link to show the JSON feed for the given walk
        $target = $this->url . 'group-walk&ids=' . $id . $this->key;
        $objHelper = new ToolsHelper;

        return $objHelper->imageButton('I', $target, true);
    }

    public function groupFeed($group_code) {
        // Returns link to enable display of all walks for given Group
        return $this->url . 'group-walk&groups=' . $group_code . $this->key;
    }

}
