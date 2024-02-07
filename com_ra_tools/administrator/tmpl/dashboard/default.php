<?php

/**
 *  @version     4.0.11
 * @package     com_ra_tools
 * @copyright   Copyleft (C) 2021
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk

 * 05/12/22 CB Created from com_ramblers
 * 10/12/22 CB correct view reports
 * 12/12/22 CB System tools configuration - use com_ra_wf
 * 26/01/23 CB component names for configuration
 * 26/05/23 CB refresh groups
 * 12/06/23 CB added Refresh Walks
 * 13/07/23 CB Refresh Areas
 * 21/06/23 CB revised menu options for MailMan
 * 10/07/23 Cb System reports
 * 18/07/23 CB show logo
 * 06/07/23 CB allow for com_ra_walks
 * 09/10/23 CB add Events / Reports
 * 04/12/23 CB add Walks Follow
 * 27/12/23 CB code for task=system
 * 30/12/23 CB Hide options is no access available
 * 04/01/24 CB move display of paths to Reports Controller
 * 06/01/24 Cb Mailman / checkRenewals
 */
// No direct access
\defined('_JEXEC') or die;

//use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

//use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
//JHtml::_('behavior.tooltip');
JToolBarHelper::title('Ramblers dashboard');
$objHelper = new ToolsHelper;

$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');

echo $objHelper->showLogo();
if (ComponentHelper::isEnabled('com_ra_mailman', true)) {
    $canDo = ContentHelper::getActions('com_ra_mailman');
    echo '<h3>Mail Manager</h3>';
    echo '<ul>';
    echo $this->createLink('com_ra_mailman', 'mail_lsts', 'Mailing lists');
    echo $this->createLink('com_ra_mailman', 'mailshots', 'Mailshots');
    if ($canDo->get('core.create')) {
        echo $this->createLink('com_ra_mailman', 'subscriptions', 'Subscriptions');
        echo $this->createLink('com_ra_mailman', 'profiles', 'MailMan Users');
        echo '<li><a href="index.php?option=com_ra_mailman&amp;view=dataload" target="_self">Import list of members</a></li>';
        echo '<li><a href="index.php?option=com_ra_mailman&amp;view=reports" target="_self">Mailman Reports</a></li>';
    }
    if ($canDo->get('core.admin')) {
        echo $this->showConfig('com_ra_mailman');
        /*
          echo '<li><a href="index.php?option=com_config&amp;view=component&amp;component=com_ra_mailman" target="_self">';
          echo 'Configure com_ra_mailman (';
          $sql = 'SELECT extension_id from #__extensions WHERE element="com_ra_mailman"';
          $extension_id = $objHelper->getValue($sql);
          $sql = 'SELECT version_id from #__schemas WHERE extension_id=' . $extension_id;
          //    echo 'Seeking  ' . $sql . '<br>';
          echo $objHelper->getValue($sql) . ')</a></li>' . PHP_EOL;
         */
    }
    echo '</ul>' . PHP_EOL;
}
echo '<h3>Organisation</h3>' . PHP_EOL;
echo '<ul>';
echo $this->createLink('com_ra_tools', 'area_list', 'List of Areas');
echo $this->createLink('com_ra_tools', 'group_list', 'List of Groups');
echo '</ul>' . PHP_EOL;

if (ComponentHelper::isEnabled('com_ra_events', true)) {
    $canDo = ContentHelper::getActions('com_ra_events');
    echo '<h3>Events</h3>' . PHP_EOL;
    echo '<ul>' . PHP_EOL;
    echo $this->createLink('com_ra_events', 'events', 'List of Events');
    echo '<li><a href="index.php?option=com_ra_events&amp;view=reports" target="_self">Event Reports</a></li>' . PHP_EOL;
    if ($canDo->get('core.admin')) {
        echo $this->showConfig('com_ra_events');
    }
    echo '</ul>' . PHP_EOL;
}

if (ComponentHelper::isEnabled('com_ra_walks', true)) {
    $canDo = ContentHelper::getActions('com_ra_walks');
    echo '<h3>Walks</h3>' . PHP_EOL;
    echo '<ul>' . PHP_EOL;
    echo $this->createLink('com_ra_walks', 'walks', 'List of Walks');
    if ($canDo->get('core.admin')) {
        echo $this->showConfig('com_ra_walks');
    }
    echo '</ul>' . PHP_EOL;
}
if (ComponentHelper::isEnabled('com_ra_wf', true)) {
    echo '<h3>Walks Follow</h3>' . PHP_EOL;
    echo '<ul>' . PHP_EOL;
    echo '<li><a href="index.php?option=com_ra_wf&amp;view=walks" target="_self">List of Walks to Follow</a></li>' . PHP_EOL;

    echo '<li><a href="index.php?option=com_ra_wf&amp;view=profiles" target="_self">Walks Follow profiles</a></li>' . PHP_EOL;
    echo '<li><a href="index.php?option=com_ra_wf&amp;view=reports" target="_self">Walks Follow Reports</a></li>' . PHP_EOL;
    if ($canDo->get('core.admin')) {
        echo $this->showConfig('com_ra_wf');
    }
    echo '</ul>' . PHP_EOL;
}
if (ComponentHelper::isEnabled('com_ra_sg', true)) {   // Self Guided
    echo '<h3>Walks</h3>' . PHP_EOL;
    echo '<ul>' . PHP_EOL;
    echo '<li><a href="index.php?option=com_ra_tools&amp;view=sg_list" target="_self">Self Guided walks</a></li>' . PHP_EOL;
    echo '<li><a href="index.php?option=com_categories&amp;extension=com_ra_tools" target - "_self">Categories</a></li>' . PHP_EOL;
    if ($canDo->get('core.admin')) {
        echo $this->showConfig('com_ra_tools');
    }
    echo '</ul>' . PHP_EOL;
}
if (ComponentHelper::isEnabled('com_ra_tools', true)) {
    $component = ComponentHelper::getComponent('com_ra_tools');
    $canDo = ContentHelper::getActions('com_ra_tools');

    $extension = \JTable::getInstance('extension');
    $extension->load($component->id);
    $manifest = new \Joomla\Registry\Registry($extension->manifest_cache);

    echo '<h3>System tools</h3>';
    echo '<ul>';
    echo '<li><a href="index.php?option=com_ra_tools&task=system.showAccess" target="_self">Show your access permissions</a></li>' . PHP_EOL;
    if (ComponentHelper::isEnabled('com_ra_mailman', true)) {
        $canDo = ContentHelper::getActions('com_ra_events');
        if ($canDo->get('core.create')) {
            echo '<li><a href="index.php?option=com_ra_mailman&task=system.checkRenewals" target="_self">Check Renewals</a></li>' . PHP_EOL;
        }
    }
    if ($objHelper->isSuperuser()) {
        echo '<li><a href="index.php?option=com_ra_tools&task=system.AccessWizard" target="_self">Access Configuration Wizard</a></li>' . PHP_EOL;
        echo '<li><a href="index.php?option=com_ra_tools&view=reports" target="_self">System Reports</a></li>' . PHP_EOL;
        echo '<li><a href="index.php?option=com_ra_tools&amp;task=area_list.refreshAreas" target="_self">Refresh details of Areas</a></li>' . PHP_EOL;
        echo '<li><a href="index.php?option=com_ra_tools&amp;task=group_list.refreshGroups" target="_self">Refresh details of Groups</a></li>' . PHP_EOL;
        if (ComponentHelper::isEnabled('com_ra_walks', true)) {
            echo ' <li><a href="index.php?option=com_ra_wf&amp;task=walks.refreshWalksArea&code=NS" target="_self">Refresh details of Walks</a></li>' . PHP_EOL;
        }
        if (ComponentHelper::isEnabled('com_ra_mailman', true)) {
            echo '<li><a href="index.php?option=com_ra_mailman&task=profiles.purgeTestdata" target="_self">Purge test data</a></li>' . PHP_EOL;
        }
    }
    if ($canDo->get('core.admin')) {
        echo '<li><a href="index.php?option=com_config&view=component&component=com_ra_tools" target="_self">';
        echo "Configure com_ra_tools (version " . $manifest->get('version') . ")</a></li>" . PHP_EOL;
    }
    echo '</ul>';
}

