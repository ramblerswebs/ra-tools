<?php

/**
 * @package     Ra_tools.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 02/06/23 CB JoomlaUsersByGroup - LEFT JOIN
 * 18/07/23 CB delete unused reports
 * 20/08/23 CB Show Admin'Site in menu report
 * 18/08/23 CB areasLatitude
 * 30/11/23 Cb use Factory::getContainer()->get('DatabaseDriver');
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHtml;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class DebugController extends FormController {

    protected $criteria_sql;
    protected $back;
    protected $db;
    protected $objApp;
    protected $objHelper;
    protected $prefix;
    protected $query;
    protected $scope;

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getContainer()->get('DatabaseDriver');
        $this->objHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        $this->prefix = 'Reports: ';
        $this->back = 'administrator/index.php?option=com_ra_tools&view=reports';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    function checkTable($table, $details, $details2 = '') {

        $config = Factory::getConfig();
        $database = $config->get('db');
        $this->dbPrefix = $config->get('dbprefix');

        $table_name = $this->dbPrefix . $table;
        $sql = 'SELECT COUNT(COLUMN_NAME) ';
        $sql .= "FROM information_schema.COLUMNS ";
        $sql .= "WHERE TABLE_SCHEMA='" . $database . "' AND TABLE_NAME ='" . $table_name . "' ";
        echo "$sql<br>";

        $count = $this->getValue($sql);
        echo 'Seeking ' . $table_name . ', count=' . $count . "<br>";
        if ($count > 0) {
            return $count;
        }

        $response = $this->executeCommand($details);
        if ($response) {
            echo 'Table created OK';
        } else {
            echo 'Failure';
            return false;
        }
        if ($details2 != '') {
            $sql = 'ALTER TABLE ' . $table_name . ' ' . $details2;
            $response = $this->executeCommand($sql);
            if ($response) {
                echo 'Table altered OK';
            } else {
                echo 'Failure';
                return false;
            }
        }
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

    public function ra_events() {

        $details = 'CREATE TABLE IF NOT EXISTS `#__ra_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT NULL ,
    `event_date` DATETIME NULL ,
    `event_date_end` DATETIME NULL ,
    `event_time` VARCHAR(5)  NOT NULL DEFAULT "19:00",
    `event_type_id` INT  NOT NULL,
    `description` VARCHAR(255)  NULL DEFAULT "",
    `group_code` VARCHAR(4)  NOT NULL ,
    `location` TEXT  NULL,
    `w3w` VARCHAR(255) NULL ,
    `postcode` VARCHAR(10) NULL ,
    `gridref` VARCHAR(12) NULL ,
    `contact_id` INT NULL,
    `details` TEXT NULL ,
    `reports` TEXT NULL ,
    `minutes` TEXT NULL ,
    `url` VARCHAR(255)  NULL  DEFAULT "",
    `url_description` VARCHAR(255)  NULL  DEFAULT "",
    `attachment` VARCHAR(255)  NULL  DEFAULT "",
    `attachment_description` VARCHAR(255)  NULL  DEFAULT "",
    `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT NULL DEFAULT "0",
    `modified` DATETIME NULL DEFAULT NULL,
    `modified_by` INT NULL DEFAULT "0",
    `checked_out_time` DATETIME NULL  DEFAULT NULL ,
    `checked_out` INT NULL,
    `state` TINYINT(1)  NULL  DEFAULT 1,

    PRIMARY KEY (`id`),
    INDEX idx_event_type_id(event_type_id)
) DEFAULT COLLATE=utf8mb4_unicode_ci;';
        $this->checkTable('ra_events', $details);

        $back = "administrator/index.php?option=com_ra_tools&view=dashboard";
        echo $this->objHelper->backButton($back);
    }

    public function ra_event_type() {
        $details = 'CREATE TABLE IF NOT EXISTS `#__ra_event_type` (
    `id` INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `description` varchar(20) NOT NULL,
    `ordering` INT NOT NULL DEFAULT 0,
    `state` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
        $this->checkTable('ra_event_type', $details);

        $details2 = "
INSERT INTO `#__ra_event_type` (`description`,`ordering`) VALUES
    ('Committee meeting',10),
    ('Social event',20),
    ('Training',30),
    ('Holiday/weekend',40),
    ('WalksManager',50);";
        $this->executeCommand($details2);

        $back = "administrator/index.php?option=com_ra_tools&view=dashboard";
        echo $this->objHelper->backButton($back);
    }

    public function test() {

        $back = "administrator/index.php?option=com_ra_tools&view=dashboard";
        echo $this->objHelper->backButton($back);
    }

}
