<?php

/**
 * @version     4.1.4
 * @package     com_ra_tools
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 12/05/23 MK created
 * 23/05/23 CB table names changed
 * 26/05/23 CB loop through all Areas
 * 26/05/23 CB correct where statement on update
 * 27/05/23 CB clean phone number
 * 29/05/23 CB re-write writeWalk
 * 03/06/23 CB check for null description
 * 12/06/23 CB log record counter
 * 21/08/23 set state to 1
 * 28/12/23 CB copied from /cli, send email of errors
 * 30/12/23 CB only send email for errors in new records
 * 02/01/24 CB set state = 1
 * 14/01/24 CB send email of errors
 */
/**
 * Usage /usr/bin/php /path/to/site/cli/ra_read_feed.php
 */
// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER))
    die();

// Initialize Joomla framework
const _JEXEC = 1;

const JDEBUG = 0;

use \Joomla\CMS\Factory;

//use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php')) {
    require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(dirname(__FILE__)));
    require_once JPATH_BASE . '/includes/defines.php';

// Get the framework.
    if (file_exists(JPATH_LIBRARIES . '//bootstrap.php')) {
        require_once JPATH_LIBRARIES . '//bootstrap.php';
    } else {
        require_once JPATH_LIBRARIES . '/import.php';
// Import necessary classes not handled by the autoloaders
        jimport('joomla.application.component.helper');
// Force library to be in JError legacy mode
        JError::$legacy = true;
    }

// Bootstrap the CMS libraries.
//require_once JPATH_LIBRARIES.'//bootstrap.php';
}

class ra_read_feed extends JApplicationCli {

    private $area;
    private $counter = 0;
    private $error_count = 0;
    private $error_message = '';
    private $walksfound = 0;
    private $walksupdated = 0;
    private $walkscreated = 0;

    /**
     *   Run it all in sequence
     */
    public function doExecute() {
        if (date("w") == 0) {
            echo 'Today is Sunday';
        }

//       $objHelper = new Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
//      $sql = 'SELECT code FROM j4_ra_areas WHERE code = "NS" ORDER BY code';
        $sql = 'SELECT code FROM #__ra_areas ORDER BY code ';
//        $sql .= 'LIMIT 2';
        $rows = $this->getRows($sql);
//        $rows = $objHelper->getRows($sql);
        foreach ($rows as $row) {
            $this->area = $row->code;
            print ("Processing $this->area \n");
            $walks = $this->getWalksData($row->code);

            if (is_null($walks)) {
                $this->logMessage("Failed to get data for " . $this->area);
            } else {
                $this->processArea($walks);
            }
        }

        print ( "Walks created = $this->walkscreated \n");
        print ( "Walks updated = $this->walksupdated \n");
        if ($this->error_count > 0) {
            $to = array('webmaster@bigley.me.uk', 'gary.atkin@ramblers.org.uk', 'ciaran.evans@ramblers.org.uk');
            $reply_to = 'webmaster@bigley.me.uk';
            $subject = $this->error_count . ' Errors found';
            print ($subject . "\n");
            $this->sendEmail($to, $reply_to, $subject, $this->error_message);
        }
        $this->logMessage("Walks in feed = $this->counter , Walks created = $this->walkscreated , Walks updated = $this->walksupdated ");
        echo $this->message;
    }

    /**
     *   Does walk exist?
     */
    private function doesWalkExist($walkid) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

// Select everything from the table that matches the walk id.
        $query->select(' a.*')
                ->from('`#__ra_walks` AS a')
                ->where('a.walk_id = ' . (int) $walkid)
        ;

        $db->setQuery((string) $query);

        $results = $db->loadObjectList();

// See if we got anything back - ie does the walk exist
        if (count($results) == 0) {
            return false;
        } else {
            return true;
        }
    }

// doesWalkExist ($walkid)
// This function is required by Joomla and must be present
    function getName() {

    }

    function getRows($sql) {
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $db->setQuery($sql);
            $db->execute();
            $this->rows = $db->getNumRows();
//            print_r($this->rows);
            $rows = $db->loadObjectList();
            return $rows;
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
//            if (JDEBUG) {
//                echo $this->error;
//            }
            return false;
        }
    }

    /**
     *   Get the walks data from the feed (either a test file or the ramblers JSON feed);
     */
    private function getWalksData($code) {
//        if ($code == 'ER') {
//            return;
//        }

        $url = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=group-walk';
        $url .= '&api-key=742d93e8f409bf2b5aec6f64cf6f405e';
        $url .= '&groups=' . $code;
//        $url .= '&limit=3';
//        $url .= '&dow=7';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);         // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);    // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);           // allow xx seconds for timeout
//	curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_walks"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . '\n');
            print('Http return: ' . $httpCode . '\n');
            echo 'Access failed' . '\n';

            $this->logMessage("Feed access failed for feed : " . $url, $httpCode);
            return;
        }

        $temp = json_decode($data);
        $summary = $temp->summary;
//        print('Limit=', $summary->limit . ', count=' . $summary->count. "\n");
        return $temp->data;
    }

// getWalksData()

    /**
     *   Store a log entry
     */
    private function logMessage($text, $record_type = 'WM') {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->insert('#__ra_logfile')
                ->set("record_type = " . $db->quote($record_type))
                ->set("message = " . $db->quote($text))
                ->set("ref = " . $db->quote($this->area))
        ;

        $result = $db->setQuery($query)->execute();
    }

// logMessage ($text , $code = 0)

    /**
     *   Process the walks data for an Area
     */
    private function processArea($walkslist) {
        $this->walksfound = count($walkslist);
        print ( "Walks in feed = $this->walksfound \n");
//        $this->logMessage("Walks in feed $this->walksfound");

        foreach ($walkslist as $walk) {
            $this->counter++;
            $this->writeWalk($walk);
        }
    }

// processArea ( $walkslist)

    function sendEmail($to, $reply_to, $subject, $body, $attachment = '') {

        if ((substr(JPATH_ROOT, 14, 6) == 'joomla') OR (substr(JPATH_ROOT, 14, 6) == 'MAMP/h')) {  // Development
            $this->message .= $to . ' ';
            return true;
        } else {
            $objMail = \Joomla\CMS\Factory::getMailer();
            $config = \Joomla\CMS\Factory::getConfig();
            $sender = array(
//                $config->get('mailfrom'),
//                $config->get('fromname')
                'walks@stokenandnewcastleramblers.org.uk',
                'Walks Feed'
            );
            $objMail->setSender($sender);
            $objMail->addRecipient($to);
            $objMail->addReplyTo($reply_to);
            $objMail->isHtml(true);
            $objMail->Encoding = 'base64';
            $objMail->setSubject($subject);
            $objMail->setBody($body);

//          Add embedded image
//          This adds the logo as an attachment, which could then be referenced as cid:xxx)
//            $objMail->AddEmbeddedImage(JPATH_COMPONENT_SITE . '/media/com_ra_mailman/logo.png', 'logo', 'logo.jpg', 'base64', 'image/png');
//           Optional file attached
            if ($attachment != '') {
                $objMail->addAttachment($attachment);
            }
            $send = $objMail->Send();
        }
        return $send;
    }

    /**
     *   Create or Update as Walk
     */
    private function writeWalk($walk) {
        $found_error = false;
        $error1 = '';
        $error2 = '';
        $error3 = '';
        if ((int) $walk->id == 0) {
            $error = 'Walk $this->counter has blank id field ';
            print ("$error \n");
            $this->logMessage($error);
        } else {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $date = substr($walk->start_date_time, 0, 10);
            $start_time = substr($walk->start_date_time, 11, 5);
            $end_time = substr($walk->end_date_time, 11, 5);
            if (is_null($walk->description)) {
                $description = '(blank)';
                $found_error = true;
                $error1 = 'No description for ' . $walk->id . ',' . $walk->title;
                print ( "$this->area: $error1  \n");
                $this->logMessage(substr($error1, 0, 100));
            } else {
                $description = $walk->description;
            }
            $difficulty = substr($walk->difficulty->description, 0, 10);
            if ($walk->shape == 'linear') {
                $shape = 'L';
            } else {
                $shape = 'C';
            }

            if (is_null($walk->start_location)) {
                $start_details = '';
                $start_grid_ref = '';
                $start_latitude = 0;
                $start_longitude = 0;
                $start_postcode = '';
                $found_error = true;
                $error2 = 'No description for ' . $walk->id . ',' . $walk->title;
                print ( "$this->area: $error2  \n");
                $this->logMessage(substr($error2, 0, 100));
            } else {
                $start_details = $walk->start_location->description;
                $start_grid_ref = $walk->start_location->grid_reference_10;
                $start_latitude = (float) $walk->start_location->latitude;
                $start_longitude = (float) $walk->start_location->longitude;
                $start_postcode = $walk->start_location->postcode;
            }

//            $title = substr($walk->title, 0, 120);
//            $title = substr(iconv(($walk->title, mb_detect_order(), true), "UTF-8", $walk->title), 0, 120);

            $title = substr($walk->title, 0, 120);

            if ((is_null($walk->walk_leader)) or (is_array($walk->walk_leader))) {
                $found_error = true;
                $error3 = 'No walkleader for ' . $walk->id . ',' . $walk->title;
                print ( "$this->area: $error3  \n");
                $this->logMessage(substr($error3, 0, 100));
                $phone = '';
                $leader_name = '';
            } else {
                $phone = substr(preg_replace('/\D/', '', $walk->walk_leader->telephone), 0, 15);
                $leader_name = $walk->walk_leader->name;
            }

            $query->set("walk_id = " . $db->quote($walk->id))
                    ->set("walk_date = " . $db->quote($date))
                    ->set("group_code = " . $db->quote($walk->group_code))
                    ->set("organising_group = " . $db->quote($walk->group_code))
                    ->set("contact_display_name = " . $db->quote($leader_name))
// must increase size of database field
//                ->set('contact_email = ' . $db->quote($walk->walk_leader->email_form))
                    ->set('contact_tel1 = ' . $db->quote($phone))
                    ->set("title = " . $db->quote(substr($title, 0, 120)))
                    ->set("description = " . $db->quote($description))
                    ->set("start_time = " . $db->quote($start_time))
                    ->set("start_gridref = " . $db->quote($start_grid_ref))
                    ->set("start_latitude = " . $db->quote($start_latitude))
                    ->set("start_longitude = " . $db->quote($start_longitude))
                    ->set("start_postcode = " . $db->quote($start_postcode))
                    ->set("start_details = " . $db->quote($start_details))
                    ->set("distance_km = " . $db->quote((float) $walk->distance_km))
                    ->set("distance_miles = " . $db->quote((float) $walk->distance_miles))
                    ->set("difficulty = " . $db->quote($difficulty))
//                ->set("pace = " . $db->quote($walk->pace))
                    ->set("ascent_feet = " . $db->quote($walk->ascent_feet))
                    ->set("ascent_metres = " . $db->quote($walk->ascent_metres))
                    ->set("circular_or_linear = " . $db->quote($shape))
                    ->set("finish_time = " . $db->quote($end_time))
                    ->set("state = 1")
            ;

            if ($this->doesWalkExist($walk->id)) {
                $query->update('#__ra_walks')
                        ->where('walk_id=' . $walk->id);
                $result = $db->setQuery($query)->execute();
                $new_record = false;
                $this->walksupdated++;
//                echo "walk $walk->id for $walk->group_code updated<br>";
            } else {
                $query->insert('#__ra_walks');  // utf8mb4_unicode_ci
                $result = $db->setQuery($query)->execute();
                $this->walkscreated++;
                $new_record = true;
            }
        }
        if ($found_error == true) {
            if ((date("w") == 0) or ($new_record == true)) {
                // Add individual error to body of the email
                $this->error_count++;
                if ($error1 != '') {
                    $this->error_message .= $error1 . '<br>';
                }
                if ($error2 != '') {
                    $this->error_message .= $error2 . '<br>';
                }
                if ($error3 != '') {
                    $this->error_message .= $error3 . '<br>';
                }
            }
        }
    }

}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
$cli = JApplicationCli::getInstance('ra_read_feed');

JFactory::$application = $cli;

$cli->doExecute();

// The end!

