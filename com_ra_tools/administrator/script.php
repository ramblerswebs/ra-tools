<?php

/*
 * Installation script
 * 01/08/23 CB Create from MailMan script
 * 07/08/23 copy checkColumn and checkTable from version 3
 * 21/08/23 CB copy walksprinted.php to Ramblers/jsonwalks/std
 * 21/08/23 CB correct location of walksprinted
 * 09/10/23 CB update clusters
 * 16/10/23 CB Clusters
 * 13/11/23 CB don't use ToolsHelper->executeCommand
 * 20/11/23 CB start deletion of obsolete praogramme_area view
 * 20/11/23 CB actually tried to delete obsolete View
 * 04/12/23 CB replace getDbo()
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
// use Joomla\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

class Com_Ra_toolsInstallerScript {

    private $component;
    private $minimumJoomlaVersion = '4.0';
    private $minimumPHPVersion = JOOMLA_MINIMUM_PHP;

    function checkColumn($table, $column, $mode, $details = '') {
//  $mode = A: add the field
//  $mode = U: update the field (keeping name the same)
//  $mode = D: delete the field

        $count = $this->checkColumnExists($table, $column);
        $table_name = $this->dbPrefix . $table;
        echo 'mode=' . $mode . ': Seeking ' . $table_name . '/' . $column . ', count=' . $count . "<br>";
        if (($mode == 'A') AND ($count == 1)
                OR ($mode == 'D') AND ($count == 0)) {
            return true;
        }
        if (($mode == 'U') AND ($count == 0)) {
            echo 'Field ' . $column . ' not found in ' . $table_name . '<br>';
            return false;
        }

        $sql = 'ALTER TABLE ' . $table_name . ' ';
        if ($mode == 'A') {
            $sql .= 'ADD ' . $column . ' ';
            $sql .= $details;
        } elseif ($mode == 'D') {
            $sql .= 'DROP ' . $column;
        } elseif ($mode == 'U') {
            $sql .= 'CHANGE ' . $column . ' ' . $column . ' ';
            $sql .= $details;
        }
        echo "$sql<br>";
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Success';
        } else {
            echo 'Failure';
        }
        echo ' for ' . $table_name . '<br>';
        return $count;
    }

    private function checkColumnExists($table, $column) {
        $config = JFactory::getConfig();
        $database = $config->get('db');
        $this->dbPrefix = $config->get('dbprefix');

        $table_name = $this->dbPrefix . $table;
        $sql = 'SELECT COUNT(COLUMN_NAME) ';
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $this->dbPrefix . $table . "' ";
        $sql .= "AND COLUMN_NAME='" . $column . "'";
//    echo "$sql<br>";

        return $this->getValue($sql);
    }

    function checkTable($table, $details, $details2 = '') {

        $config = JFactory::getConfig();
        $database = $config->get('db');
        $this->dbPrefix = $config->get('dbprefix');

        $table_name = $this->dbPrefix . $table;
        $sql = 'SELECT COUNT(COLUMN_NAME) ';
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $table_name . "' ";
//        echo "$sql<br>";

        $count = $this->getValue($sql);
        echo 'Seeking ' . $table_name . ', count=' . $count . "<br>";
        if ($count > 0) {
            return $count;
        }
        $sql = 'CREATE TABLE ' . $table_name . ' ' . $details;
        echo "$sql<br>";
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Table created OK<br>';
        } else {
            echo 'Failure<br>';
            return false;
        }
        if ($details2 != '') {
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $details2;
            $response = $this->executeCommand($sql);
            if ($response) {
                echo 'Table altered OK<br>';
            } else {
                echo 'Failure<br>';
                return false;
            }
        }
    }

    function check403() {
        $details = '( `code` VARCHAR(3) NOT NULL,'
                . '`name` VARCHAR(20) NOT NULL,'
                . '`contact_id` INT NULL,'
                . '  PRIMARY KEY (`code`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $details2 = 'INSERT INTO  `#__ra_clusters` (code,name) values ';
        $details2 .= "('ME','Midlands and East'),";
        $details2 .= "('N','North and North West'),";
        $details2 .= "('SE','South East'),";
        $details2 .= "('SSW','South and South West')";
        $this->checkTable('ra_clusters', $details, '');
        $this->checkColumn('ra_areas', 'cluster', 'A', "ADD cluster VARCHAR(3) NOT NULL DEFAULT '' AFTER co_url; ");
        $this->checkColumn('ra_areas', 'chair_id', 'A', "ADD chair_id INT NOT NULL DEFAULT '0' AFTER cluster; ");
        $this->updateClusters();
    }

    private function executeCommand($sql) {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $db->setQuery($sql);
        return $db->execute();
    }

    private function getValue($sql) {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $db->setQuery($sql);
        return $db->loadResult();
    }

    public function getDatabaseVersion($component = 'com_ra_tools') {
// Get the extension ID
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $eid = $this->getExtensionId($component);

        if ($eid != null) {
// Get the schema version
            $query = $db->getQuery(true);
            $query->select('version_id')
                    ->from('#__schemas')
                    ->where('extension_id = ' . $db->quote($eid));
            $db->setQuery($query);
            $version = $db->loadResult();

            return $version;
        }

        return null;
    }

    /**
     * Loads the ID of the extension from the database
     *
     * @return mixed
     */
    public function getExtensionId($component = 'com_ra_tools') {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true);
        $query->select('extension_id')
                ->from('#__extensions')
                ->where($db->qn('element') . ' = ' . $db->q($component) . ' AND type=' . $db->q('component'));
        $db->setQuery($query);
        $eid = $db->loadResult();
        if (is_null($eid)) {
            echo 'Can\'t find Extension id for ' . $component . '<br>';
            echo $db->replacePrefix($query) . '<br>';
        }
        return $eid;
    }

    public function getVersion($component = 'com_ra_tools') {
        $version = '';
        $extension_id = $this->getExtensionId($component);
        if ($extension_id) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select('version_id')
                    ->from('#__schemas')
                    ->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
            $db->setQuery($query);

            $version = $db->loadResult();
            if ($version == false) {
                echo 'Can\'t find version for ' . $component . '<br>';
                echo $db->replacePrefix($query) . '<br>';
            }
        }
        return $version;
    }

    public function install($parent): bool {
        echo '<p>Installing RA Tools (com_ra_tools) ' . '</p>';
        if (ComponentHelper::isEnabled('com_ra_tools', true)) {
            echo 'com_ra_tools found, version=' . $this->getVersion('com_ra_tools');
        }
        return true;
    }

    private function ra_feedback_summary() {
        $details = 'CREATE TABLE ra_feedback_summary`(id` INT UNSIGNED NOT NULL AUTO_INCREMENT,'
                . '`walk_id` INT NOT NULL,'
                . '`date_created` VARCHAR(10) NOT NULL,'
                . '  PRIMARY KEY (`id`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $details2 = 'ADD KEY `idx_wfs_walk_id` (`walk_id`);';
        $this->checkTable('ra_feedback_summary', $details, $details2);
    }

    public function uninstall($parent): bool {
        echo '<p>Uninstalling RA Tools (com_ra_tools)</p>';

        return true;
    }

    public function update($parent): bool {
        echo '<p>Updating RA Tools (com_ra_tools)</p>';

// You can have the backend jump directly to the newly updated component configuration page
// $parent->getParent()->setRedirectURL('index.php?option=com_ra_tools');
        return true;
    }

    public function preflight($type, $parent): bool {
        echo '<p>Preflight RA Tools (type=' . $type . ')</p>';
        $version = $this->getVersion();
        echo '<p>com_ra_tools is currently at version ' . $version . '</p>';
        if ($type !== 'uninstall') {
            if (!empty($this->minimumPHPVersion) && version_compare(PHP_VERSION, $this->minimumPHPVersion, '<')) {
                Log::add(
                        Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPHPVersion),
                        Log::WARNING,
                        'jerror'
                );
                return false;
            }

            if (!empty($this->minimumJoomlaVersion) && version_compare(JVERSION, $this->minimumJoomlaVersion, '<')) {
                Log::add(
                        Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomlaVersion),
                        Log::WARNING,
                        'jerror'
                );
                return false;
            }
        }

        return true;
    }

    public function postflight($type, $parent) {
        '<p>Postflight RA Tools (com_ra_tools)</p>';

        if ($type == 'uninstall') {
            return 1;
        }
        $new_version = $this->getVersion();
        echo '<p>com_ra_tools is now at version ' . $new_version . '</p>';

        $new_script = JPATH_SITE . "/components/com_ra_tools/walksprinted.php";
        $target = JPATH_LIBRARIES . '/ramblers/jsonwalks/std/walksprinted.php';
        if (file_exists($new_script)) {
            echo 'Copying ' . $new_script . '<br> to ' . $target;
            copy($new_script, $target);
            if (file_exists($target)) {
                echo ' Success<br>';
            } else {
                echo ' Failed<br>';
            }
        } else {
            echo $new_script . ' not found<br>';
        }
        $v_403 = '4.0.3';
        if (version_compare($this->getVersion(), $v_403, '<')) {
            echo 'New version is less than ' . $v_403 . '<br>';
        }
        $target = JPATH_SITE . '/components/com_ra_tools/src/View/Programme_area';
        if (file_exists($target)) {
            echo "$target found<br>";
            File::delete($target);
        } else {
            echo "$target NOT found<br>";
        }
        $target = JPATH_SITE . '/components/com_ra_tools/tmpl/programme_area';
        if (file_exists($target)) {
            echo "$target found<br>";
//            File::delete($target);
        } else {
            echo "$target NOT found<br>";
        }
        if (1) {
//        $this->checkColumn('ra_walks', 'description', 'A', "TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''; ");
//        $this->checkColumn('ra_mail_shots', 'author_id', 'D');

            $this->check403();
        }

//        $sql = "INSERT INTO `dev_ra_mail_access` (`id`, `name`)";
//        $sql .= "VALUES ('1', 'Subscriber'), ('2', 'Author') ,('3', 'Owner') ";
        return true;
    }

    private function updateClusters() {
        $sql = 'UPDATE `#__ra_areas` SET cluster = "SC" WHERE nation_id=2';
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Updated Scotland';
        } else {
            echo 'Scotland Failure';
        }

        $sql = 'UPDATE `#__ra_areas` SET cluster = "WA" WHERE nation_id=3';
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Updated Wales';
        } else {
            echo 'Wales Failure';
        }

        $sql = 'UPDATE `#__ra_areas` SET cluster = "ME" WHERE code in ("BF","LI","NP","NR","NE","SS","NS","WO","CH","DE")';
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Updated ME';
        } else {
            echo 'ME Failure';
        }

        $sql = 'UPDATE `#__ra_areas` SET cluster = "SE" WHERE code in ("BU","CB","ES","WX","KT","IL","IW","NO","OX","SK","SR","SX")';
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Updated SE';
        } else {
            echo 'SE Failure';
        }

        $sql = 'UPDATE `#__ra_areas` SET cluster = "SSW" WHERE code in ("AV","BK","CL","DN","DT","GR","IW","OX","SO","WE")';
        $response = $this->executeCommand($sql);
        if ($response) {
            echo 'Updated SSW';
        } else {
            echo 'SSW Failure';
        }
    }

}
