<?php

/*
  // class file to extend the Base class produced by program generator
 * 06/12/22 CB created from com_ramblers
 * 07/12/22 CB use $this->buildLink
 * 19/12/22 CB use Ra_toolsProfile
 * 19/12/22 Cb getContactDetails
 * 13/01/23 CB always check contactviaemail before sending a message
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Site\Helpers;

use Joomla\CMS\Factory;

class Walk extends Walkbase {

    private $db;
    public $count_followers;
    public $error;
    public $message;

    function __construct() {
        parent::__construct();
        $this->id = 0;
        $this->published = 1;
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

// overrides to the base class
    function add() {
        $response = parent::add();

        if ($this->distance_miles > 0) {
            $user_id = $this->checkLeader();
//        echo "Checking leadership for " . $this->contact_display_name . " = " . $user_id;
            if (($user_id > 0) AND ($this->getDaystoGo() > 0)) {
// Invite leader to upload a gpx file
                $this->sendMessageSingle('G', $user_id);
                $this->notify();
            }
        }
        return $response;
    }

    function addFeedback($user_id, $feedback) {
//        echo "Walk creating feedback record: user=$user_id<br>";
//        $ip_address = Factory::getApplication()->input->server->get('REMOTE_ADDR', '');
//      Will be invoked from Feedback, so getApplication is not possible
        $ip_address = $this->db->quote($_SERVER['REMOTE_ADDR']);
        $sql = 'SELECT id FROM #__ra_walks_feedback WHERE record_type=1 ';
        $sql .= 'AND user_id=' . $user_id . ' AND walk_id=' . $this->id;
//            echo $sql . "<br>";
        $count = $this->objHelper->getValue($sql);

        if ($count > 0) {
            $sql = "UPDATE #__ra_walks_feedback ";
            $sql .= "SET comment=" . $this->db->quote($feedback) . ",";
            $sql .= "tcp_ip_address=" . $this->db->quote($ip_address) . " ";
            $sql .= 'WHERE walk_id=' . $this->id . ' ';
            $sql .= 'AND record_type=1 ';
            $sql .= 'AND user_id=' . $user_id;
//            echo $sql;
        } else {
            $sql = 'INSERT INTO #__ra_walks_feedback (walk_id,record_type,user_id,tcp_ip_address,comment) VALUES(';
            $sql .= $this->id . ",";
            $sql .= '1,';
            $sql .= (int) $user_id . ',';
            $sql .= $ip_address . ',';
            $sql .= $this->db->quote($feedback) . ')';
//            echo $sql;
        }
        $this->objHelper->executeCommand($sql);
    }

    function addPhoto($user_id, $height, $width, $source, $filename, $caption) {
//        echo "Walk creating feedback record: user=$user_id<br>";
//        $ip_address = Factory::getApplication()->input->server->get('REMOTE_ADDR', '');
//      Will be invoked from Feedback, so getApplication is not possible
        $ip_address = $_SERVER['REMOTE_ADDR'];

        $sql = "INSERT INTO #__ra_walks_feedback (walk_id,record_type,user_id,tcp_ip_address,comment,source,photo,height,width) VALUES(";
        $sql .= $this->id . ',';
        $sql .= "2,";
        $sql .= (int) $user_id . ",";
        $sql .= $this->db->quote($ip_address) . ',';
        $sql .= $this->db->quote($caption) . ',';
        $sql .= $this->db->quote($source) . ',';
        $sql .= $this->db->quote($filename) . ',';
        $sql .= $this->db->quote($height) . ',';
        $sql .= $this->db->quote($width) . ')';
//       echo $sql;
        $this->objHelper->executeCommand($sql);
    }

    function countEmails() {
        $sql = "SELECT COUNT(id) ";
        $sql .= "from #__ra_wf_emails ";
        $sql .= "where walk_id=" . $this->id;
//        echo $sql;
        return $this->objHelper->getValue($sql);
    }

    function countFeedback() {// Total number of records (text plus photos)
        $sql = "SELECT COUNT(id) ";
        $sql .= "from #__ra_walks_feedback ";
// $sql .= "INNER JOIN #__ra_walks AS walks on feedback.walk_id = walks.id ";
        $sql .= "where walk_id=" . $this->id;
//        echo $sql;
        return $this->objHelper->getValue($sql);
    }

    function countFollowers() {
        $sql = "SELECT count(id) as followers ";
        $sql .= "from #__ra_walks_follow as walk_follow ";
        $sql .= "WHERE walk_follow.walk_id=" . $this->id;
        $this->count_followers = $this->objHelper->getValue($sql);
        return $this->count_followers;
    }

    public function countPhotos($id = 0) {
        /*
         * This counts actual number of photos that are present
          $folder = JPATH_BASE . '/' . rtrim(JComponentHelper::getParams('com_ra_wf')->get('walks_folder'), '/') . '/';
          // Get array of image files for this walk
          if ($id == 0) {
          $photos = glob($folder . $this->id . '-' . "*.{jpg,jpeg,png,tif}", GLOB_BRACE);
          } else {
          $photos = glob($folder . $id . '-' . "*.{jpg,jpeg,png,tif}", GLOB_BRACE);
          }
          if ($photos) {
          return count($photos);
          } else {
          return 0;
          }
         */
        $sql = "SELECT COUNT(id) FROM #__ra_walks_feedback  ";
        $sql .= "WHERE record_type =2 AND walk_id=" . $this->id;
//        echo $sql;
        return $this->objHelper->getValue($sql);
    }

    function decode($token, &$walk_id, &$user_id, &$id, &$days, &$mode, $debug = False) {
        /*
         * Takes the string that has been obfuscated by function "encode",
         *  and splits it into constituents
         */
        if ($debug) {
            echo "Walk/decode: " . $token . "<br>";
        }
        $temp = $token;
        $temp = strrev(substr($temp, 0, strlen($temp) - 1));

        $plain_text = "";
        for ($i = 0; $i < strlen($temp); $i++) {
            $char = substr($temp, $i, 1);
            $plain_text .= (hexdec($char) - 6);
        }
        if ($debug) {
            echo "After decoding: " . $plain_text . "<br>";
        }
//      Split the string into two parts
        $length_part1 = substr($plain_text, 0, 2);
        $part1 = substr($plain_text, 2, $length_part1);
        $part2 = substr($plain_text, 2 + $length_part1);
        if ($debug) {
            echo "part1:" . $part1 . ', len ' . strlen($part1) . "<br>part2:" . $part2 . ', len ' . strlen($part2) . "<br>";
        }
        $walk_id = substr($part1, 0, 8);
//        echo $part1 . '<br>' . '0123456789 123456789 123456<br>';
        $encode_date = substr($part1, 12, 4) . '-' . substr($part1, 10, 2) . '-' . substr($part1, 8, 2);
        $date_created = date_create($encode_date);

        $now = date_create(date('Y-m-d'));
        $interval = date_diff($now, $date_created);
        $days = $interval->format('%R%a');
        if ($debug) {
            echo "Encode date= $encode_date, days=$days<br>"; //date_created=$date_created,
        }

        $mode = substr($part1, 16, 2);
        $id = substr($part1, 18);
        $user_id = substr($part2, 8);
        if ($debug) {
            echo "id=" . $id . ", walk_id=" . $walk_id . ", User=" . $user_id . ", id=" . $id . ", Mode=" . $mode . "<br>";
        }
        $this->id = $id;
        if ($this->getData()) {
            return 1;
        } else {
            if ($debug) {
                echo $this->message . "<br>";
            }
            return 0;
        }
    }

    function deleteOld($walk_id) {
// The parameter passed must be the walk_id, not the internal id
        $id = $this->checkExists($walk_id, "N");
        parent::delete($id);
    }

    function delete($id) {
        $sql = "DELETE FROM #__ra_walks_feedback WHERE walk_id=$id";
//        echo $sql . '<br>';
        $this->objHelper->executeCommand($sql);

        $sql = "DELETE FROM #__ra_walks_follow WHERE walk_id=$id";
//        echo $sql . '<br>';
        $this->objHelper->executeCommand($sql);
        parent::delete($id);
    }

    function deleteById($id) {
// This is not usually used, since it takes as parameter the internal id
        parent::delete($id);
    }

    function set_contact_display_name($newValue) {
// don't allow an existing contact name to be deleted
        if (!trim($newValue) == "") {
            $this->contact_display_name = trim(substr($newValue, 0, 50));
        }
    }

    public function update() {
//       echo "Number of fields updated " . $this->fields_updated . "<br>";
        if (parent::update()) {
//            echo "Walk class Number of fields updated " . $this->fields_updated .
            " for " . $this->walk_id . "<br>";
            if (($this->fields_updated > 0) AND ($this->getDaystoGo() > 0)) {
                $this->sendMessageMultiple('U');
            }
            return 1;
        }
    }

// these should include validation/reformatting when accepting data
    function set_id($newValue) {
        $this->id = $newValue;
    }

    function get_id() {
        return $this->id;
    }

// Added manually to the generated base class
    function buildLink($url, $text, $newWindow = 0, $class = "") {
        // N.B. ToolsHelper cannot be used from batch programs, because Juri::base() is not available
        $q = chr(34);
        $out = PHP_EOL . "<a ";
//        echo "BuildLink: url = $url, " . substr($url, 0, 4) . ", text=$text<br>";
        if (!$class == "") {
            $out .= "class=" . $q . $class . $q;
        }
        $out .= " href=" . $q;
        if (substr($url, 0, 4) == "http") {

        } else {
            // get website base from component parameters
            $website_base = rtrim(JComponentHelper::getParams('com_ra_wf')->get('website'), '/') . '/';
            $out .= $website_base;
            echo 'base ' . $out . '<br>';
        }
        $out .= $url . $q;
        if ($newWindow) {
            $out .= " target =" . $q . "_blank" . $q;
        } else {
            $out .= " target =" . $q . "_self" . $q;
        }
        $out .= ">";
        if ($text == "")
            $out .= $url;
        else
            $out .= $text;
        $out .= "</a>" . PHP_EOL;
//        echo "BuildLink: output= $out";
        return $out;
    }

    function checkExists($walk_id) {
// $walk_id refers to the reference from the Central Office database
// returns the internal id of the walk record
        $this->walk_id = $walk_id;
        $sql = "SELECT id FROM #__ra_walks WHERE walk_id=" . $walk_id;
//        echo $sql;
        $this->id = $this->objHelper->getValue($sql);
        if ($this->id == 0) {
            $this->walk_id = $walk_id;
            $this->error = $this->objHelper->error;
            return 0;
        }
//        echo "checkExists: id=" . $this->id . "<br>";
        if ($this->getData()) {
            return $this->id;
        } else {
            return 0;
        }
    }

    function checkLeader() {
        if (trim($this->contact_display_name) == "") {
            return 0;
        }
        $sql = "SELECT id FROM #__ra_profiles WHERE preferred_name ='" . $this->contact_display_name . "'";
        $id = $this->objHelper->getValue($sql);
        if ($id == 0) {
            return 0;
        } else {
//            echo $this->contact_display_name . " gives user_id " . $id;
            return $id;
        }
    }

    public function encode($user_id, $mode) {
        /*
         * $user-id is the identifier for the current logged in user, mode = (N)ew walks or (F)eedback
          This creates a token to identify a walk in such a manner it can be sent in an email and subsequently used to update the record without the need to log in.

          It is built up in several parts:
          Six digits of the "walk id"
          Eight digits from the current date
          A two digit flag (for feedback, this signifies whether the user id given as parameter is the leader of the walk)
          The id of the database table

          The string thus generated is obfuscated in two stages by processing each digit in turn,
          firstly by adding 6, then changing its representation to Hexadecimal Thus 0123789 would become 678def

          The length of this part is not predictable, so its length is prepended

          This is followed by a further 8 digits from the timestamp Plus the user-id, also obfuscated
          Finally the whole string is reversed
         */
//        echo "<br>date=" . $this->walk_date . ",id=" . $this->walk_id . ",mode=$mode<br>";
        if (strlen($this->walk_id) < 8) {
// add any required leading zeroes to give 8 characters in length
            $part1 = str_pad($this->walk_id, 8, "0", STR_PAD_LEFT);
        } else {
            $part1 = substr($this->walk_id, 0, 8);
        }

        $part1 .= date_create()->format('dmY');
        if ($mode == "F") {                          // Feedback
            if ($user_id == $this->leader_user_id) { // this is the leader of the walk
                $part1 .= "04";
//                echo "encoding, Leader, part1=$part1" . "<br>";
            } else {
                $part1 .= "03";
//                echo "encoding, Not leader, part1=$part1" . "<br>";
            }
        } elseif ($mode == "G") {                     // GPX file
            $part1 .= "06";
        } else {
            $part1 .= $mode;
        }
        $part1 .= $this->id;
        $part1 = strlen($part1) . $part1;

        $part2 = substr(time(), 0, 8);  // Pseudo random numbers
        $part2 .= $user_id;

//        echo "Walk before encoding:" . $part1 . "-" . $part2 . ", id=" . $this->id . ", user=" . $user_id . ", mode=$mode<br>";
        $token = "";
        for ($i = 0; $i < strlen($part1); $i++) {
//            echo $i . substr($token, $i, 1) . " " . dechex(substr($token, $i, 1) + 6) . "<br>";
            $token .= dechex(substr($part1, $i, 1) + 6);
        }
        for ($i = 0; $i < strlen($part2); $i++) {
            $token .= dechex(substr($part2, $i, 1) + 6);
        }
//        echo "Walk:" . $token . "<br>";
        return strrev($token) . "Z";
    }

    function feedbackButton($encoded) {
        return $this->objHelper->buildLink("components/com_ra-wf/feedback.php?ref=" . $encoded, "Feedback", false, "link-button button-p0555");
    }

    function followWalk($id, $user_id) {
//        echo "followWalk: $id, $user_id";
//        $this->id = $id;
//        $this->getData();
        $sql = "SELECT id FROM #__ra_walks_follow WHERE user_id=$user_id AND walk_id=$id";
//echo $sql . "<br>";
        $follow_id = $this->objHelper->getValue($sql);
        if ($follow_id > 0) {
            $this->message = 'Already following this walk led by ' . $this->contact_display_name . ' on ' . $this->get_walk_date();
            return 0;
        }

// Get a db connection.
        $db = Factory::getContainer()->get(DatabaseInterface::class); //use Joomla\Database\DatabaseInterface;
// Create a new query object.
        $query = $db->getQuery(true);
// Insert columns.
        $columns = array('walk_id', 'user_id');
// Insert values.
        $values = array($id, Factory::getApplication()->loadIdentity()->id);
// Prepare the insert query.
        $query
                ->insert($db->quoteName('#__ra_walks_follow'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
// Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        $db->execute();

//        $sql = "INSERT INTO #__ra_walks_follow (walk_id, user_id) values ($id, $user_id)";
//        echo $sql;
//        if (!$this->objHelper->executeCommand($sql)) {
//            return 0;
//        }
        $this->message = 'You are now following the walk led by ' . $this->contact_display_name . ' on ' . $this->get_walk_date();
// Send confirmation message to leader, if required
        return $this->sendMessageSingle("F");
    }

    function generateLink($walk_id, $button = "N") {
// generates a link to Walkfinder for the specified Walk
        $q = chr(34);
        $out = "<a ";
        if ($button == "Y") {
            $out .= "class=" . $q . "link-button button-p0186" . $q;
        }
        $out .= " href=" . $q . "https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/walk-detail.aspx?walkID=" . $walk_id . $q;
        $out .= " target=" . $q . "_blank" . $q . ">";
        $out .= "Walksfinder</a>";
        return $out;
    }

    function getStreetmapLink($gridref, $latitude, $longitude) {
        if ($gridref == '') {
            return '';
        }
        $target = 'https://streetmap.co.uk/loc/' . $latitude . ',';
        if ($longitude > 0) {
            $target .= 'E' . $longitude;
        } else {
            $target .= 'W' . abs($longitude);
        }
        return $this->objHelper->imageButton("I", $target, True);
    }

    function getWalkDetails() {
        $message = $this->description . '<br>';
        $message .= $this->notes . '<br>';
        $message .= "Group: " . $this->group_code . ' ' . $this->getGroupname();
        if ($this->organising_group <> $this->group_code) {
            $message .= " (organised by $organising_group " . $this->objHelper->lookupGroup($this->organising_group) . ')';
        }
        $message .= '<br>';
        if ($this->get_meeting_description($details)) {
            $message .= "Meet: " . $details . '<br>';
            if (($this->meeting_latitude == 0) or ($this->meeting_latitude == 0)) {

            } else {
                $target = "https://www.google.com/maps?q=";
                $target .= $this->meeting_latitude;
                $target .= "," . $this->meeting_longitude;
                $message .= $this->buildLink($target, 'Google maps', True) . '<br>';
            }
        }
        if ($this->get_start_description($details)) {
            $message .= "Start: " . $details . '<br>';
            if (($this->start_latitude == 0) or ($this->start_longitude == 0)) {

            } else {
                $target = "https://www.google.com/maps?q=";
                $target .= $this->start_latitude;
                $target .= "," . $this->start_longitude;
                $message .= $this->buildLink($target, 'Google maps', True) . '<br>';
            }
        }

        $details = $this->difficulty;
        if (!$details == "") {
            $message .= "Difficulty: " . $details . '<br>';
        }

        $details = $this->grade_local;
        if (!$details == "") {
            $message .= "Local Grade: " . $details . '<br>';
        }

        $details = $this->pace;
        if (!$details == "") {
            $message .= "Pace: " . $details . '<br>';
        }

        $details = $this->distance_miles . " miles, ";
        $details .= $this->distance_km . " km";
        $message .= "Distance: " . $details . '<br>';

        $details = $this->ascent_feet;
        if (!$details == "") {
            $message .= "Ascent: " . $details . " feet, ";
            $message .= $this->ascent_metres . " metres" . '<br>';
        }
        if ($this->walk_id > 0) {
            $target = "https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/walk-detail.aspx?walkID=" . $this->walk_id;
            $message .= $this->buildLink($target, 'Walksfinder', true) . '<br>';
        }
        $message .= '<p>&nbsp</p>';
        return $message;
    }

    function get_contact_details(&$message) {
        $message = $this->contact_tel1;
//        if ($message == "") {
//            return 0;
//        }
        echo 'get_contact_details ' . Factory::getApplication()->loadIdentity()->id;
        if (!$this->contact_tel2 == "") {
            if ($message == "") {
                $message = $this->contact_tel2;
            } else {
                $message .= "," . $this->contact_tel2;
            }
        }
        if ($this->walk_id > 0) {
            if (Factory::getApplication()->loadIdentity()()->id == 0) {
                $target = 'https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=' . $this->walk_id;
                $new_window = true;
            } else {
                $target = 'index.php?option=com_ra_wf&view=chat&layout=edit&mode=SE&id=' . $this->id;
                $new_window = false;
            }
//        echo ' ' . $target . '<br>';
            $message .= $this->objHelper->imageButton("E", $target, $new_window);
        }
        return 1;
    }

    function getContactDetails() {
        $message = $this->contact_tel1;
        if (!$this->contact_tel2 == "") {
            if ($message == "") {
                $message = $this->contact_tel2;
            } else {
                $message .= "," . $this->contact_tel2;
            }
        }
        if ($this->walk_id > 0) {
            if (Factory::getApplication()->loadIdentity()->id == 0) {
                $target = 'https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=' . $this->walk_id;
                $new_window = true;
            } else {
                $target = 'index.php?option=com_ra_wf&view=chat&layout=edit&mode=SE&id=' . $this->id;
                $new_window = false;
            }
//        echo ' ' . $target . '<br>';
            $message .= $this->objHelper->imageButton("E", $target, $new_window);
        }
        return $message;
    }

    function getDaystoGo() {
//        echo $this->walk_date;
//        echo ', ' . date('Y-m-d H:i:s') . '<br>';
        $walk_date = date_create($this->walk_date);
        $now = date_create(date('Y-m-d'));
        $interval = date_diff($now, $walk_date);
//        echo $interval->format('%R%a');
        return $interval->format('%R%a');
    }

    function getDate($option = 0) {
        /*
          Changes walk to to a pretty format depending on option:
          Default = dd/mm/yy
          mode = 1, dd mmm yy
          mode = 2, include day www dd/mm/yy
          mode = 3, www dd mmm yy
         */

        $date = date_create($this->walk_date);
        $pretty = '';
        if ($option >= 2) {
            $pretty .= date_format($date, 'D') . ' ';
        }
        $pretty .= date_format($date, 'd');
        if (($option == 1) or ($option == 3)) {
            $pretty .= ' ' . date_format($date, 'M') . ' ';
        } else {
            $pretty .= '/' . date_format($date, 'm') . '/';
        }
        $pretty .= date_format($date, 'y');
        return $pretty;
    }

    function getDescription($walk_id) {
        $sql = "SELECT date_format(walk_date,'%a %e') as Date, title ";
        $sql .= "FROM #__ra_walks where walk_id = " . $walk_id;
        $item = $this->objHelper->getItem($sql);
        if (empty($item)) {
            $this->error = $this->objHelper->error;
            return 0;
        }
        return $item->Date . ': ' . $item->title;
    }

    function getFeedback($user_id) {
        if ($this->id == 0) {
            $this->message = 'Trying to get feedback, but not initialised';
            return 0;
        } else {
            $sql = 'SELECT comment FROM #__ra_walks_feedback WHERE walk_id=' . $this->id;
            $sql .= ' AND record_type = 1 AND user_id=' . $user_id;
            return $this->objHelper->getValue($sql);
        }
    }

    function getFollowerList($mode, $followers) {
// builds the list of Follower's names for inclusion in an email
// Depending on the setting of Privacy level on their Profile records, some names may be hidden
// However, the walk leader will always see the names in full
        $this->count_followers = count($followers);
        $list = 'Members who ';
        if ($this->getDaystoGo() > 0) {
            $list .= 'are Following';
        } else {
            $list .= 'Followed';
        }
        $list .= ' this walk<br><ul>';
        if ($this->count_followers == 0) {
            $list .= '<li>(none)</li>';
        } else {
            foreach ($followers as $follower) {
                $list .= '<li>';
                if ($mode == 'L') {
                    $list .= $follower->member;
//                if ($follower->user_id == $user_id) {
//                    $list .= " (New)";
//                }
                } else {
                    if ($follower->privacy_level == 3) {
                        $list .= "anonymous";
                    } else {
                        $list .= $follower->member;
                    }
                }

                $list .= '</li>';
            }
            $list .= '</ul>';
            return $list;
        }
    }

    function getFollowers() {
        if ($this->countFollowers() > 0) {
            $sql = "SELECT profile.preferred_name AS 'member', ";
            $sql .= "profile.preferred_name, ";
            $sql .= "profile.ra_group_code, ";
            $sql .= "profile.ra_home_group, ";
            $sql .= "users.email AS 'email', ";
            $sql .= "profile.ra_acknowledge_follow AS 'acknowledge_follow', ";
            $sql .= "profile.ra_privacy_level AS 'privacy_level', ";
            $sql .= "profile.ra_contactviaemail AS 'contactviaemail', "; //
            $sql .= "profile.ra_mobile AS 'mobile', ";
            $sql .= "profile.ra_contactviatextmessage AS 'contactviatextmessage', ";
            $sql .= "walk_follow.id, ";
            $sql .= "walk_follow.user_id, walk_follow.id ";
            $sql .= "FROM #__ra_walks_follow as walk_follow  ";
//       $sql .= "INNER JOIN #__ra_walks AS walks on walks.id = walk_follow.walk_id ";
            $sql .= "INNER JOIN #__ra_profiles AS profile on profile.id = walk_follow.user_id ";
            $sql .= "INNER JOIN #__users AS users on users.id = profile.id ";
//            $sql .= "WHERE users.email > '' ";
            $sql .= "WHERE walk_follow.walk_id=" . $this->id;
            $sql .= " ORDER by profile.preferred_name";
//       echo $sql;
            return $this->objHelper->getRows($sql);
        } else {
            return array();
        }
    }

    function getGroupname() {
        $sql = "SELECT name FROM  #__ra_groups ";
        $sql .= "where code='" . $this->group_code . "'";
        return $this->objHelper->getValue($sql);
    }

    function getLeaderEmail() {
// Find user from the contact details on the walk
        if ($this->leader_user_id == 0) {
            return $this->contact_email;
        } else {
            $objProfile = new Ra_toolsProfile;
            $objProfile->id = $this->leader_user_id;
            if ($objProfile->getData()) {
                return $objProfile->get_email();
            } else {
                $this->message = $this->objHelper->message;
                return '';
            }
        }
    }

    function getTitle() {
        $message = "Walk led by " . $this->contact_display_name;
        $message .= ", " . $this->getDate(3);
        $message .= ", " . $this->title;
        return $message;
    }

    function get_meeting_description(&$message) {
        $message = $this->meeting_time;
        if ($message == "") {
            return 0;
        }
        $message .= ", " . $this->meeting_details;
        $message .= ", " . $this->meeting_postcode . ',';
        $message .= ", " . $this->meeting_gridref;
        $message .= $this->getStreetmapLink($this->meeting_gridref, $this->meeting_latitude, $this->meeting_longitude);
        if (!$this->meeting_latitude == 0) {
            $message .= "Lat: " . $this->meeting_latitude;
        }
        if (!$this->meeting_longitude == 0) {
            $message .= ", Lon: " . $this->meeting_longitude;
        }
        return 1;
    }

    function get_meeting_location(&$message) {
// Returns an image button with a link to Google maps (should be to OSM)
        if (($this->meeting_latitude == 0) or ($this->meeting_longitude == 0)) {
            return 0;
        } else {
            $target = "https://www.google.com/maps?q=";
            $target .= $this->meeting_latitude;
            $target .= "," . $this->meeting_longitude;
            $message = $this->objHelper->imageButton("GO", $target, True);
            return 1;
        }
    }

    function get_start_description(&$message) {
        $message = $this->start_time;
        if ($message == "") {
            return 0;
        }
        $message .= ", " . $this->start_details;
        $message .= ", " . $this->start_postcode . ',';
        $message .= ", " . $this->start_gridref;
        $message .= $this->getStreetmapLink($this->start_gridref, $this->start_latitude, $this->start_longitude);
        if (!$this->start_latitude == 0) {
            $message .= "Lat: " . $this->start_latitude;
        }
        if (!$this->start_longitude == 0) {
            $message .= ", Lon: " . $this->start_longitude;
        }
        return 1;
    }

    function get_start_location(&$message) {
// should return an image button with a link to OSM
        if (($this->start_latitude == 0) or ($this->start_latitude == 0)) {
            $message = "";
            return 0;
        } else {
// OSM - only works intermittently
//            $target = "https://www.openstreetmap.org?mlat=";
//            $target .= $this->start_latitude;
//            $target .= "&mlon" . $this->start_longitude . "&zoom=20";
//            echo "lat=" . $this->start_latitude . ", target=$target";
            $target = "https://www.google.com/maps?q=";
            $target .= $this->start_latitude;
            $target .= "," . $this->start_longitude;
            $message = $this->objHelper->imageButton("GO", $target, True);
            return 1;
        }
    }

    function get_walk_date() {
// date is in format YYYY-MM-DD
        return substr($this->walk_date, 8, 2) . "/" . substr($this->walk_date, 5, 2) . "/" . substr($this->walk_date, 2, 2);
    }

    function get_walk_day() {
// Returns day of the week
        return substr($this->walk_date, 8, 2);
    }

    public function logEmail($record_type, $user_id) {
        $sql = 'INSERT INTO #__ra_wf_emails (walk_id, record_type, user_id) VALUES(';
        $sql .= $this->id . ",'" . $record_type . "'," . $user_id . ')';
//        echo "$sql<br>";
        $this->objHelper->executeCommand($sql);
    }

    /*
      function logEmail($record_type, $message) {
      $this->logMessage($record_type, $this->walk_id, $message);
      }
     */

    function logMessage($record_type, $ref, $message) {
        $q = chr(34);
//        define Q = chr(34);
        $sql = "INSERT INTO #__ra_logfile (`log_date`, `record_type`, `ref`, `message`) VALUES ";
        $sql .= "(CURRENT_TIMESTAMP," . $q . $record_type . $q . ",";
        $sql .= $q . (int) $ref . $q;
        $sql .= "," . $q . $message . $q . ")";
//        echo $sql;
        $this->objHelper->executeCommand($sql);
    }

    function notify() {
//        echo "Target = " . $this->distance_miles . "<br>";
        $sql = "SELECT profiles.id,";
        $sql .= "profiles.ra_min_miles, profiles.ra_max_miles, ";
        $sql .= "profiles.ra_group_code ";
        $sql .= "FROM #__ra_profiles AS profiles ";
        $sql .= "WHERE ra_group_code > '' ";
        $sql .= "AND ra_min_miles <=" . $this->distance_miles . " AND ra_max_miles >=" . $this->distance_miles;
//        echo $sql . "<br>";
//        $this->objHelper->showQuery($sql);
        $rows = $this->objHelper->getRows($sql);
//        if (empty($this->rows)) {
//            return 0;
//        }

        foreach ($rows as $row) {
// This will find all users: check they are interested in the group
// check the walk's group code is not present, rather than found in position 0
//            echo $row->ra_group_code . ' ' . strpos($row->ra_group_code, $this->group_code) . '<br>';
            $result = strpos($row->ra_group_code, $this->group_code);
            if ($result === false) {
//                echo $row->ra_group_code . " / " . $this->group_code . " / " . $result . "<br>";
            } else {
                echo "Send message N " . $row->id . " : " . "<br)";
                $this->sendMessageSingle("N", $row->id);
            }
        }
    }

    function sendEmail($to_email, $subject, $email_body) {
//       (N.B. dev machine not configured )
        if (trim($to_email) == '') {
            $this->message .= 'Blank email address given for message ' . $subject;
            return 0;
        }
//      Look up the profile record to ensure member has opted in
        $sql = 'SELECT ra_contactviaemail from #__ra_profiles AS p ';
        $sql .= 'INNER JOIN #__users AS u ON u.id = p.id ';
        $sql .= 'WHERE u.email="' . $to_email . '"';

        if ($this->objHelper->getValue($sql) == false) {
            Factory::getApplication()->enqueueMessage($to_email . ' has opted out of emails', 'notice');
            return 0;
        }
// Email header could (should?) come from configuration
        $params = JComponentHelper::getParams('com_ra_wf');
//        $header = '<i>' . $params->get('email_header') . '</i>';
        $header = '<i>This email was sent from the Ramblers WalksFollow component</i><br>';

        $footer = '<br>' . $params->get('email_footer');

        $final_message = $header . $email_body . $footer;

        $objJMail = Factory::getMailer();
        $config = Factory::getConfig();
        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );
        $objJMail->isHtml(true);
        $objJMail->Encoding = 'base64';
        $objJMail->setSender($sender);        // This could/should be an array
        $objJMail->addRecipient($to_email);
        $objJMail->setSubject($subject);
        /*
          // Optionally add embedded image
          $objJMail->AddEmbeddedImage( JPATH_COMPONENT.'/assets/logo128.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );
          // Optional file attached
          // $objJMail->addAttachment(JPATH_COMPONENT.'/assets/document.pdf');
         */
        $objJMail->setBody($final_message);
        $test = substr(JPATH_ROOT, 14, 4);
        if (($test == 'joom') OR ($test == 'MAMP')) { // Development
            echo 'To: ' . $to_email . '<br>';
            echo 'Subject: ' . $subject . '<br>';
            echo $final_message . '<br><br>';
            return true;
        } else {
            $send = $objJMail->Send();
            if ($send !== true) {
//                $this->logEmail("ME", 'Error sending email: ' . $to_email);
                $this->logMessage("ME", $this->walk_id, 'Error sending email: ' . $to_email);
            }
        }
    }

    function sendMessageMultiple($mode, $message = '') {
        /*
         * This sends an email to everyone registered as a Follower to the walk

          B Advise Followers the Blog has been updated
          L leader sending message to all Followers (online, from screen input)
          M member sending message to all co-Followers (online, from screen input)
          P post walk feedback (batch, from cli/feedback.php)
          R pre walk reminder (batch, from cli/reminder.php)
          U walk has been updated (called internally, from update)
         */
        $response = 0;
// Log the email
//       $this->logEmail('M' . $mode, 'Multiple ' . $message);
// Find the base address of the website, to use when generating links in the mail body
// (Juri::base() is not available in batch mode
// Delete any trailing slash
        $website_base = rtrim(JComponentHelper::getParams('com_ra_wf')->get('website'), '/') . '/';

        $followers = $this->getFollowers();

//        foreach ($followers as $follower) {
//            echo "Hi " . $follower->user_id . $follower->preferred_name . $follower->member . $follower->email . ",<br>";
//        }
//        die('xx');

        if (count($followers) == 0) {
            echo "no followers for $this->id<br>";
            return;
        }
        $list = $this->getFollowerList($mode, $followers);
        $email_type = "M" . $mode;
        switch ($mode) {
            case 'B':    //
                $this->message = "Message sent to ";
                $subject = 'Message to walk Followers';

                foreach ($followers as $follower) {
                    $this->logEmail($email_type, $follower->user_id);
                    $body = "Hi " . $follower->preferred_name . ",<br>";
                    $body .= '<b>' . $this->getTitle() . '</b><br>';
                    $body .= "The blog for this walk has been updated, if you want to see the latest photos simply follow this link:<br>";
                    $token = $this->encode($follower->user_id, "15");
                    $target = $website_base . "components/com_ra_wf/process_email.php?ref=";
                    $body .= $this->buildLink($target . $token, 'View Blog', true) . '<br>';
                    $to_email = $follower->email;
                    if ($this->objHelper->isSuperuser()) {
                        $this->message .= $to_email . ", ";
                    }
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message .= $this->count_followers . " Followers";
                break;
            case 'L':    //
                $this->message = "Message sent to ";
                $subject = 'Message to walk Followers';
                $body = '<b>' . $this->getTitle() . '</b><br>';
                $body .= "The leader of this walk is sending this message to all Followers:<br>";
                $body .= $message;  // as provided as input
//                $body .= '<i>You can reply to the leader ';
//                $body .= 'leader from our walking programme"</i>';
                $target = 'https://www.ramblers.org.uk/go-walking/find-a-walk-or-route/contact-walk-organiser.aspx?walkId=' . $this->walk_id;
                $body .= $this->buildLink($target, 'Reply', true) . '<br>';

                foreach ($followers as $follower) {
                    $to_email = $follower->email;
                    $this->logEmail($email_type, $follower->user_id);
                    if ($this->objHelper->isSuperuser()) {
                        $this->message .= $to_email . ", ";
                    }
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message .= $this->count_followers . " Followers";
                break;
            case 'M':
                $this->message = "Message sent to ";
                $subject = 'Message from one Followers to all others';
                $body = '<b>' . $this->getTitle() . '</b><br>';
                $body .= Factory::getApplication()->loadIdentity()->name . ' is sending this message to all other Followers:<br>';
                $body .= $message . '<br>';  // as provided as input
                $body .= '<i>You cannot reply directly, but can sent a message to all other Followers ';
                $body .= 'by logging on and viewing "My Diary"</i>';
                foreach ($followers as $follower) {
                    $to_email = $follower->email;
                    $this->logEmail($email_type, $follower->user_id);
                    if ($this->objHelper->isSuperuser()) {
                        $this->message .= $to_email . ", ";
                    }
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message .= $this->count_followers . " Followers";
// Send a copy to the walk leader
                $to_email = $this->getLeaderEmail();
                if ($to_email != '') {
                    $leader_email = $this->getLeaderEmail();
                    $this->logEmail($email_type, $leader_email);
                    $response = $this->sendEmail($leader_email, $subject, $body);
                }
                break;
            case 'P':                            // Post walk feedback - request from all Followers
                foreach ($followers as $follower) {
                    $this->logEmail($email_type, $follower->user_id);
                    $to_email = $follower->email;
                    $subject = 'Request for feedback about walk ' . $this->getDate(3);
                    $body = "Hi " . $follower->preferred_name . ",<br>";
                    $body .= '<b>' . $this->getTitle() . '</b><br>';
                    $body .= "You are registered as having Followed this walk. If you would like to give feedback, simply follow this link:<br>";
                    $token = $this->encode($follower->user_id, "13");
                    $target = $website_base . "components/com_ra_wf/feedback.php?ref=";
                    $body .= $this->buildLink($target . $token, 'Give feedback', true) . '<br>';

                    $body .= "If you did not actually go on the walk, could you please follow this link:<br>";
                    $token = $this->encode($follower->user_id, '02');
                    $target = $website_base . "components/com_ra_wf/process_email.php?token=" . $token;
                    $body .= $this->buildLink($target, 'Unfollow', true) . '<br>';
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message = "Message sent to " . $this->count_followers . ' Followers';
                break;
            case 'R':                            // Pre walk reminder to all Followers
                foreach ($followers as $follower) {
                    $this->logEmail($email_type, $follower->user_id);
                    $to_email = $follower->email;
                    $subject = 'Reminder about walk ' . $this->getDate(3);
                    $body = "Hi " . $follower->preferred_name . ",<br>";
                    $body .= '<b>' . $this->getTitle() . '</b><br>';
                    $body .= "You are registered as Following this walk - don't forget it!<br>";
                    $body .= $this->getWalkDetails();
                    $body .= "If you no longer expect to actually go on the walk, could you please follow this link:<br>";
                    $token = $this->encode($follower->user_id, "02");
                    $target = $website_base . "components/com_ra_wf/process_email.php?token=" . $token;
                    $body .= $this->buildLink($target, 'Unfollow', true) . '<br>';
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message = "Message sent to " . $this->count_followers . ' Followers';
                break;
            case 'U':
                foreach ($followers as $follower) {
                    $this->logEmail($email_type, $follower->user_id);
                    $to_email = $follower->email;
                    $subject = 'Walk ' . $this->getDate(3) . ' has been updated';
                    $body = "Hi " . $follower->preferred_name . ",<br>";
                    $body .= '<b>' . $this->getTitle() . '</b><br>';
                    $body .= "You are registered as Following this walk - Walk details have been updated:<br>";
                    $body .= $this->getWalkDetails();
                    $body .= "If you no longer expect to actually go on the walk, could you please follow this link:<br>";
                    $token = $this->encode($follower->user_id, "02");
                    $target = $website_base . "components/com_ra_wf/process_email.php?token=" . $token;
                    $body .= $this->buildLink($target, 'Unfollow', true) . '<br>';
                    $response = $this->sendEmail($to_email, $subject, $body);
                }
                $this->message = "Message sent to " . $this->count_followers . ' Followers';
// Send a copy to the walk leader
                $subject = 'Walk ' . $this->getDate(3) . ' has been updated';
                $body = "Hi,<br>";
                $body .= '<b>' . $this->getTitle() . '</b><br>';
                $body .= "You are registered as leading this walk - Walk details have been updated:<br>";
                $body .= $this->getWalkDetails();
                $response = $this->sendEmail($this->getLeaderEmail(), $subject, $body);
                break;
        }
        return $response;
    }

    function sendMessageSingle($mode, $to_user = 0, $message = '', $to_email = '') {

//        echo $this->id . ', mode=' . $mode . ', to_user=' . $to_user . ', to_email=' . $to_email . '<br>';


        /*
          This sends an email to a single member, and sometimes to the walk leader
          E Enquiry to walk leader (on-line)                                          walk leader
          F member is Following the walk, optionally email to the Leader              current user
          L to the walk leader (on-line)                                              as specified by parameter
          M message to the walk leader (on-line)                                      leader of the walk
          X walks has been un-followed, optionally email to the Leader (on-line)      current user
         *
          G Invite leader to upload GPX file                                          walk leader
          L to the walk leader (batch, from feedback.php)                             walk leader
          N Newly added walk, called repeatedly (batch, from data load)               walk leader
          P Post walk, request feedback (batch, from feedback.php)                    walk leader
          R Pre walk reminder to leader (batch, from reminders.php)                   walk leader

          Must check for permission before G, L, N, P, R
         * if ($mode in g,k,n,p,r({
         *  $sql = 'SELECT ra_contact_by_email FROM #__ra_profiles where user_id=' . $user->id;
         * }
         */

        $response = 0;
// Find the base address of the website, to use when generating link in the mail body,
// and ensure it has a trailing slash
// (Juri::base() is not available in batch mode)

        $website_base = rtrim(JComponentHelper::getParams('com_ra_wf')->get('website'), '/') . '/';

        $user = Factory::getApplication()->loadIdentity();
        $leader_email = $this->getLeaderEmail();
        $email_type = "S" . $mode;

//        echo $this->id . ', leader_email=' . $leader_email . '<br>';

        switch ($mode) {
            case 'F':    // member has registered to Follow a walk
                $this->logEmail($email_type, $user->id);
                $subject = 'You are Following a walk ' . $this->getDate(3);
                $body = 'You have registered to Follow this walk:' . '<br>';
                $body .= '<b>' . $this->getTitle() . '</b><br>';
                $body .= $this->getWalkDetails();
                $body .= 'If you change your mind about attending the walk, please cancel by following this link: ';
                $token = $this->encode($user->id, '02');
                $target = $website_base . "components/com_ra_wf/process_email.php?token=" . $token;
                $body .= $this->buildLink($target, 'UnFollow', true) . '<br>';
                $response = $this->sendEmail($user->email, $subject, $body);
// See if leader wants to be notified
                if (($response) AND ($leader_email != '')) {
                    $this->logEmail($email_type, $this->leader_user_id);
                    $body = 'A new member ' . $user->name . " has registered to Follow this walk" . '<br>';
                    $followers = $this->getFollowers();
                    $body .= $this->getFollowerList('L', $followers);
                    $response = $this->sendEmail($leader_email, $subject, $body);
                }
                break;
            case 'G':    // Invite leader to upload a gpx file
                $this->logEmail($email_type, $this->leader_user_id);
                $subject = 'New walk created ' . $this->getDate(3);
                $body = 'This walk has been created:' . '<br>';
                $body .= '<b>' . $this->getTitle() . '</b><br>';
                $body .= $this->getWalkDetails();
                $body .= 'If you would like to upload a gpx file of the route, just follow this link: ';
                $token = $this->encode($user->id, '15');
                $target = $website_base . "components/com_ra_wf/upload_gpx.php?token=" . $token;
                $body .= $this->buildLink($target, 'Upload', true) . '<br>';
                $response = $this->sendEmail($user->email, $subject, $body);
                break;
            case 'N':    // newly added walk, sending message to any member with range specified
// Don't send email to the leader
                if ($this->leader_user_id == $to_user) {
                    break;
                }
                $objProfile = new Ra_toolsProfile;
                $objProfile->id = $to_user;
                if ($objProfile->getData()) {
                    $this->logEmail($email_type, $to_user);
                    $subject = 'Notification of new ' . $this->distance_miles . ' miles walk on ' . $this->getDate(3);
                    $body = "Hi " . $objProfile->get_username() . ",<br> ";
                    $body = "This walk has just been added, and at " . $this->distance_miles;
                    $body .= " miles is within your selected range of " . $objProfile->ra_min_miles;
                    $body .= " to " . $objProfile->ra_max_miles . " miles.<br> ";
                    $body .= '<b>' . $this->getTitle() . '</b><br>';
                    $body .= $this->getWalkDetails();
                    $body .= "If you choose to Follow this walk, you will be notified of any changes, and ";
                    $body .= "the walk leader would be able to get in touch with you by email if necessary. Furthermore, ";
                    if ($objProfile->ra_privacy_level > 1) {
                        $body .= 'you would be able to send emails to anyone else who is following this walk, and';
                    } else {
                        $body .= 'you would be invited to';
                    }
                    $body .= ' give feedback afterwards. ';
                    $body .= "Simply follow this link: ";
                    $token = $this->encode($to_user, "01");
                    $target = $website_base . "components/com_ra_wf/process_email.php?token=" . $token;
                    $body .= $this->buildLink($target, 'Follow', true) . '<br>';
                    $response = $this->sendEmail($objProfile->get_email(), $subject, $body);
                } else {
                    echo 'Unable to find profile record for ' . $to_user;
                }
                break;

            case 'P':          // Request post walk feedback from the walk leader
                $this->logEmail($email_type, $leader_email);
                $subject = 'Request for feedback about walk ' . $this->getDate(3);
                $body = "Hi " . $this->contact_display_name . ",<br> ";
                $body .= '<b>' . $this->getTitle() . '</b><br>';
                $body .= "Thanks for leading this walk - it would be helpful if you could spare a moment to provide feedback ";
                $body .= "about attendance and the finish time, so please simply follow this link;<br>";
                $token = $this->encode($this->leader_user_id, "14");
                $target = $website_base . "components/com_ra_wf/feedback.php?ref=" . $token;
                $body .= $this->buildLink($target, 'Give feedback', true) . '<br>';
                $response = $this->sendEmail($leader_email, $subject, $body);
                break;

            case 'R':         // Pre walk reminder to the walk leader
                $this->logEmail($email_type, $leader_email);
                $subject = 'Pre walk reminder for walk ' . $this->getDate(3);
                $body = "Hi " . $this->contact_display_name . ",<br>";
                $body .= "You are registered as Leading this walk - the current details are as follows:<br>";
                $body .= '<b>' . $this->getTitle() . '</b><br>';
                $body .= $this->getWalkDetails();
//                $body .= "This is the current list of Followers:<br>";
                $body .= $this->getFollowerList("L", $followers);
                $response = $this->sendEmail($leader_email, $subject, $body);
                break;

            case 'X':               // member has cancelled Follow
                $this->logEmail($email_type, $user->id);
                $subject = 'You have opted out of Following a walk ' . $this->getDate(3);
                $body = 'You are no longer Following this walk:' . '<br>';
                $body .= $this->getWalkDetails();
                $response = $this->sendEmail($user->email, $subject, $body);
// See if leader wants to be notified
                if ($leader_email != '') {
                    $this->logEmail($email_type, $leader_email);
                    $body = $user->name . " has cancelled from Following this walk,<br>";
                    $response = $this->sendEmail($leader_email, $subject, $body);
                }
                break;

// This block of code must be last, beacause of the drop-through tests above

            case 'E':    // enquiry to the walk leader
                $recipient_email = $leader_email;
// drop through until we hit break
            case 'L':    // from the walk leader
// drop through until we hit break
            case 'M';    // from Member to the walk leader
                if ($mode == 'E') {
                    $subject = 'Enquiry to the walk leader ';
                    $recipient_email = $leader_email;
                } else if ($mode == 'L') {
                    // recipient has been selected from layout=myfollowers
                    // May not have a profile record
                    $subject = 'Message from a walk leader ';
                    $recipient_email = $to_email;
                    // could write a message to ra_wf_email_enquiries
                    // $this->logEnquiry($to_email);
                } else {  // M
                    $subject = 'Message to a walk leader ';
                    $recipient_email = $leader_email;
                }
                $body = $message . '<br>';
                $body .= 'Sent by ' . $user->name . '<br>';
                $response = $this->sendEmail($recipient_email, $subject, $body);
                if ($response) {
                    if ($mode == 'E') {
                        $this->logEmail($email_type, $this->leader_user_id);
                    } else if ($mode == 'M') {
                        $this->logEmail($email_type, $this->leader_user_id);
                    }
                }
                break;

            default:
                $this->message = 'Invalid mode ' . $mode . ' when sending email';
                return 0;
        }
        return $response;
    }

    function set_end_time($newValue) {
        if (strlen($newValue) == 7) {   // no leading zero
            $this->end_time = "0" . substr($newValue, 0, 4);
        } else {
            $this->end_time = substr($newValue, 0, 5);
        }
    }

    function set_walk_date_json($newValue) {
// assumes date in format YYYY-MM-DD
        $this->walk_date = substr($newValue, 0, 10);
//        $this->walk_date = substr($newValue, 0, 4) . "-" . substr($newValue, 3, 2) . "-" . substr($newValue, 0, 2);
    }

    function unfollowWalk($id, $user_id) {
        $this->id = $id;
        $sql = "SELECT COUNT(id) FROM #__ra_walks_follow WHERE user_id=$user_id AND walk_id=$id";

        $num_rows = $this->objHelper->getValue($sql);
//        echo $sql . "<br>rows=$num_rows<br>";
        if ($num_rows == 0) {
            $this->message = 'You are not following the walk led by ' . $this->contact_display_name . ' on ' . $this->getDate();
            if (JDEBUG) {
                $this->message .= '<br>' . $sql;
            }
            return 0;
        }
        $sql = "DELETE FROM #__ra_walks_follow WHERE user_id=$user_id AND walk_id=$id";
        $this->objHelper->executeCommand($sql);
        $this->message = 'You are no longer following the walk led by ' . $this->contact_display_name . ' on ' . $this->getDate();

// Notify the leader
        return $this->sendMessageSingle("X");
//        echo "Walk: unfollowWalk: Message sent<br>";
//        return 1;
    }

}
