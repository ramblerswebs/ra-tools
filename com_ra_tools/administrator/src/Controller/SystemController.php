<?php

/**
 * @version     1.0.13
 * @package     com_ra_tools
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 23/12/23 CB Created
 * 30/12/23 CB show Groups the user belongs to
 * 04/01/24 CB show Authorship of lists
 */

namespace Ramblers\Component\Ra_tools\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHtml;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

class SystemController extends FormController {

    protected $access_groups;
    protected $back;
    protected $changes_required;
    protected $components;
    protected $missing_group;
    protected $no;
    protected $objApp;
    protected $objHelper;
    protected $wrong_parent;
    protected $yes;

    public function __construct() {
        parent::__construct();
        $this->objHelper = new ToolsHelper;
        $this->objApp = Factory::getApplication();
        $this->back = 'administrator/index.php?option=com_ra_tools&view=dashboard';

        $this->components[] = 'com_ra_tools';
        $this->components[] = 'com_ra_events';
        $this->components[] = 'com_ra_mailman';
        $this->components[] = 'com_ra_walks';
        $this->yes = '<img src="/media/com_ra_tools/tick.png" alt="OK" width="20" height="20">' . '<br>';
        $this->no = '<img src="/media/com_ra_tools/cross.png" alt="Fail" width="20" height="20">';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    public function AccessWizard() {
        ToolBarHelper::title('Access Configuration Wizard');
        if (!$this->objHelper->isSuperuser()) {
            echo 'Access only permitted for Superusers';
            return;
        }
        $this->yes = '<img src="/media/com_ra_tools/tick.png" alt="OK" width="20" height="20">' . '<br>';
        $this->no = '<img src="/media/com_ra_tools/cross.png" alt="Fail" width="20" height="20">';
        echo 'This checks the current configuration of your Access setting.';
        $this->changes_required = 0;
        $this->missing_groups = 0;
        $this->wrong_parent = 0;

        $sql = 'SELECT rules FROM #__viewlevels ';
        $sql .= 'WHERE title="Special"';

        $item = $this->objHelper->getItem($sql);
        if (is_null($item)) {
            echo $sql . '<br>';
            echo 'View level Special not found.<br>';
            return;
        }
        $this->access_groups = ',' . substr($item->rules, 1, -1) . ',';
//        echo $this->access_groups . '<br>';

        foreach ($this->components as $component) {
            $this->checkComponentAccess($component);
        }
        // Check if error detected
        if ($this->changes_required == 0) {
            echo 'All settings have been set up as recommended.';
            echo $this->objHelper->backButton($this->back);
            return;
        }

        if ($this->missing_group > 0) {
            echo '<table><td>';
            echo 'To control update access to each component in the back end, it is recommended that you add ';
            if ($this->missing_groups == 1) {
                echo 'a group for this component.';
            } else {
                echo 'groups for these components.';
            }
            echo ' Then add the Users who need to be able to update the component.';
            echo '</td><td>';
            $target = 'administrator/index.php?option=com_installer&view=manage';
            echo $this->objHelper->standardButton('Go', $target);
            echo '</td></table>';
            echo '<br>';
        }

        if ($this->wrong_parent > 0) {
            echo '<table><td>';
            echo 'The ability to log on to the website back end is determined by membership of the access level "Special", It is recommended that you add ';
            if ($this->changes_required == 1) {
                echo 'this component';
            } else {
                echo 'these components';
            }
            echo ' to that access level.';
            echo 'Members in those groups will then be granted access to logon to the Administrative functions in the website back end.';
            echo '</td><td>';
            $target = 'administrator/index.php?option=com_installer&view=manage';
            echo $this->objHelper->standardButton('Go', $target);
            echo '</td></table>';
            echo '<br>';
        }
        echo 'It is recommended you make the changes suggested above.';
        echo '<br>';
        echo $this->objHelper->backButton($this->back);
    }

    private function checkComponentAccess($component) {
        echo '<h3>' . $component . '</h3>';

        $sql = 'SELECT extension_id FROM #__extensions ';
        $sql .= 'WHERE element="' . $component . '"';
        $item = $this->objHelper->getItem($sql);
        if (is_null($item)) {
            echo 'Component ' . $component . ' is not installed.<br>';
            return;
        }
        echo 'Is component ' . $component . ' installed and published?  ';
        if (ComponentHelper::isEnabled($component, true)) {
            echo $this->yes;
        } else {
            echo $this->no;
            $this->changes_required++;
            $target = 'administrator/index.php?option=com_installer&view=manage';
            echo $this->objHelper->buildButton($target, 'Publish it now', false, 'red');
            echo '<br>';
            return;
        }

        echo 'Does Usergroup ' . $component . '  exist?  ';
        $sql = 'SELECT g.id, p.title ';
        $sql .= 'FROM #__usergroups as g ';
        $sql .= 'INNER JOIN #__usergroups as p on p.id = g.parent_id ';
        $sql .= 'WHERE g.title="' . $component . '"';
        $item = $this->objHelper->getItem($sql);
        if (is_null($item)) {
            $this->changes_required++;
            $this->missing_group++;
            echo $this->no;
            $target = 'administrator/index.php?option=com_users&view=group&layout=edit';
            echo $this->objHelper->buildButton($target, 'Create it now', false, 'red');
            $group_id = 0;
            return;
        } else {
            echo $this->yes;
            $group_id = $item->id;
        }

        echo 'Is Usergroup ' . $component . ' a child of Usergroup Public? ';
        if ($item->title == 'Public') {
            echo $this->yes;
        } else {
            echo $this->no . ' (' . $item->title . ')';
            $this->changes_required++;
            $this->wrong_parent++;
            $target = 'administrator/index.php?option=com_users&view=group&layout=edit&id=' . $group_id;
            echo $this->objHelper->buildButton($target, 'Fix it now', false, 'red');
            echo '<br>';
        }

        echo 'Is Access-level Special available to Usergroup ' . $component . '?';
        if (str_contains($this->access_groups, ',' . $group_id . ',')) {
//            echo $this->access_groups . ',' . $group_id . '<br>';
            echo $this->yes;
        } else {
            //           echo $this->access_groups . ',' . $group_id . '<br>';
            echo $this->no;
            $this->changes_required++;
            $target = 'administrator/index.php?option=com_users&view=level&layout=edit&id=3';
            echo $this->objHelper->buildButton($target, 'Fix it now', false, 'red');
            echo '<br>';
        }

// Find number of users in the user_group
        $sql = 'SELECT COUNT(*) ';
        $sql .= 'FROM #__user_usergroup_map WHERE group_id=' . $group_id;
//        echo $sql . '<br>';
        $count = $this->objHelper->getValue($sql);
        echo 'Number of users in user-group ' . $component . ' ' . $count;
        if ($count > 0) {
            $target = 'administrator/index.php?option=com_ra_tools&task=system.showUsers&group_id=' . $group_id;
            echo $this->objHelper->buildButton($target, 'Show', false, 'red');
        }
        echo '<br>';
    }

    public function createUsergroup($component) {
        $component = $this->objApp->input->getWord('component', '');
        $sql = 'INSERT INTO #__usergroup (name,parent_id) ';
        $sql .= '("' . $component . '","1")';
        $item = $this->objHelper->executeCommand($sql);
    }

    public function showAccess() {
// Invoked from dashboard
        ToolBarHelper::title('Show your access permissions');

        $user = Factory::getApplication()->getIdentity();
        echo 'You are logged in as ' . $user->name . ' (' . $user->id . ')';
        echo ', and are a member of the following Groups<br>';
        $sql = 'SELECT g.title  ';
        $sql .= 'FROM #__user_usergroup_map AS map ';
        $sql .= 'INNER JOIN #__usergroups as g on g.id = map.group_id ';
        $sql .= 'WHERE map.user_id=' . $user->id . ' ';
        $item = $this->objHelper->showQuery($sql);

        $this->showAccessComponent('com_ra_tools', 'RA Tools');

        $this->showAccessComponent('com_ra_mailman', 'RA MailMan');

        $this->showAccessComponent('com_ra_events', 'RA Events');

        $this->showAccessComponent('com_ra_walks', 'RA Walks');

        $this->showAccessComponent('com_ra_wf', 'RA Walks Follow');

//        echo '<h3>RA Guided Walks</h3>' . PHP_EOL;
//        $this->showAccessComponent('com_ra_sg');
        echo $this->objHelper->backButton($this->back);
    }

    private function showAccessComponent($component, $title) {
        echo '<h2>' . $title . ' (' . $component . ')</h2>';
        if (!ComponentHelper::isEnabled($component, true)) {
            echo '<h4>Not installed, or is disabled</h4>';
            return;
        }
        $canDo = ContentHelper::getActions($component);
        echo 'Access backend ' . $this->showLiteral($canDo->get('core.manage')) . '<br>';
        echo 'Create ' . $this->showLiteral($canDo->get('core.create')) . '<br>';
        echo 'Delete ' . $this->showLiteral($canDo->get('core.delete')) . '<br>';
        echo 'Edit ' . $this->showLiteral($canDo->get('core.edit')) . '<br>';
        echo 'Edit state ' . $this->showLiteral($canDo->get('core.edit.state')) . '<br>';
        echo 'Change config ' . $this->showLiteral($canDo->get('core.admin')) . '<br>';
        if ($component == 'com_ra_mailman') {
            // See if Author of any lists
            $this->showLists();
        }
    }

    private function showLists() {
        $user_id = Factory::getApplication()->getIdentity()->id;
        $sql = ' FROM #__ra_mail_lists AS l ';
        $sql .= 'INNER JOIN #__ra_mail_subscriptions AS s ON s.list_id = l.id ';
        $sql .= 'WHERE s.user_id=' . $user_id;
        $count = $this->objHelper->getValue('SELECT COUNT(*)' . $sql);
        if ($count > 0) {
            echo '<b>You are an Author for the following list';
            if ($count > 1) {
                echo 's';
            }
            echo ':</b><br>' . PHP_EOL;
            echo '<ul>' . PHP_EOL;
            $rows = $this->objHelper->getRows('SELECT l.name' . $sql);
            foreach ($rows as $row) {
                echo '<li>' . $row->name . '</li>' . PHP_EOL;
            }
            echo '</ul>' . PHP_EOL;
        }
    }

    private function showLiteral($value) {
        if ($value) {
            return '<img src="/media/com_ra_tools/tick.png" width="20" height="20">' . 'Permitted';
        } else {
            return '<img src="/media/com_ra_tools/cross.png" width="20" height="20">' . 'Denied';
        }
    }

    public function showUsers() {
        if (!$this->objHelper->isSuperuser()) {
            echo 'Access only permitted for Superusers';
            return;
        }
        $group_id = $this->objApp->input->getInt('group_id', '');
        $sql = 'SELECT title ';
        $sql .= 'FROM #__usergroups ';
        $sql .= 'WHERE id=' . $group_id;
        $title = $this->objHelper->getvalue($sql);
        ToolBarHelper::title('Users for group ' . $title);

        $sql = 'SELECT u.id AS UserId, u.name, u.username, u.email ';
        $sql .= 'FROM #__user_usergroup_map AS map ';
        $sql .= 'INNER JOIN #__users AS u ON u.id = map.user_id ';
        $sql .= 'WHERE map.group_id=' . $group_id;
        $sql .= ' ORDER BY u.name';
        $rows = $this->objHelper->getRows($sql);
//      Show link that allows page to be printed
        $target = 'administrators/index.php?option=com_ra_tools&task=system.showUsers&group_id=' . $group_id;
        echo $this->objHelper->showPrint($target) . '<br>' . PHP_EOL;
        $objTable = new ToolsTable;
        $objTable->add_header('id,Name,Username,Email');
        foreach ($rows as $row) {
            $objTable->add_item($row->UserId);
            $objTable->add_item($row->name);
            $objTable->add_item($row->username);
            $objTable->add_item($row->email);
            $objTable->generate_line();
        }
        $objTable->generate_table();
        echo $this->objHelper->backButton('administrator/index.php?option=com_ra_tools&task=system.AccessWizard');
    }

}
