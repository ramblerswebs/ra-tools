<?php
/**
 * @version     1.0.6
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie Bigley <webmaster@bigley.me.uk> - https://www.developer-url.com
 * 17/12/20 CB Attempt to allow update of notify_joiners (does not work, reversed)
 * 19/04/21 CB reinstate notify joiners
 * 20/01/22 CB show all fields if group code present for walks follow
 */
// No direct access
\defined('_JEXEC') or die;

echo "<!-- Code from com_ra_tools/views/profile/tmpl/default.php -->" . PHP_EOL;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

//echo __FILE__ . '<br>';
$me = Factory::getApplication()->loadIdentity();
$email = $me->email;
echo "<h2>Profile</h2>";
?>

<div class="btn-toolbar">
    <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('profile.save')">
            <span class="icon-ok"></span><?php echo Text::_('JSAVE') ?>
        </button>
    </div>
    <div class="btn-group">
        <button type="button" class="btn" onclick="Joomla.submitbutton('profile.cancel')">
            <span class="icon-cancel"></span><?php echo Text::_('JCANCEL') ?>
        </button>
    </div>
    <div class="btn-group">
        <button type="button" class="btn" onclick="Joomla.submitbutton('profile.audit')">
            <span></span><?php echo 'Audit' ?>
        </button>
    </div>
</div>
die('template');
<form action="<?php echo JRoute::_('index.php?option=com_ra_tools&view=profile&id=' . $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
    <?php $this->form->getInput('id'); ?>
    <?php
//echo '<form action="';
//echo JRoute::_('index.php?option=com_ra_tools&layout=edit&id=' . $this->item->id);
//echo '" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">';


    $objTable = new Table;
    $objHelper = new ToolsHelper;
    //$objTable->num_columns = 3;

    $objTable->add_column("User", "R");
    $objTable->add_column($me->name, "L");
    $objTable->add_column($this->form->getInput('id'), "L");
    $objTable->generate_header();

    $objTable->add_item("Email address");
    $objTable->add_item($me->email);
    $objTable->add_item("Your email address");
    $objTable->generate_line();

    $objTable->add_item("Home group");
    $objTable->add_item($this->form->getInput('ra_home_group'));
    $objTable->add_item("Group to which you are registered by Central Office");
    $objTable->generate_line();

    $walks_profile_group = $this->form->getInput('ra_group_code');
    if ($this->walks_follow) {
        $objTable->add_item("Group(s) ");
        $objTable->add_item($this->form->getInput('ra_group_code'));
        $objTable->add_item("Group or Groups whose walks you are following (if more than one, they must be separated with commas)");
        $objTable->generate_line();

        $objTable->add_item("Display Name ");
        $objTable->add_item($this->form->getInput('preferred_name'));
        $objTable->add_item("As visible to others (and as used in GWEM, if you are a leader)");
        $objTable->generate_line();

        $objTable->add_item("Contact by Email ");
        $objTable->add_item($this->form->getInput('ra_contactviaemail'));
        $objTable->add_item("Do you want messages from this website by email");
        $objTable->generate_line();

        $objTable->add_item("Mobile number ");
        $objTable->add_item($this->form->getInput('ra_mobile'));
        $objTable->add_item("As held in this website");
        $objTable->generate_line();

        $objTable->add_item("Contact via text ");
        $objTable->add_item($this->form->getInput('ra_contactviatextmessage'));
        $objTable->add_item("Do you want messages from this website by text (if this feature is implemented)");
        $objTable->generate_line();

        $objTable->add_item("Acknowledge Follow ");
        $objTable->add_item($this->form->getInput('ra_acknowledge_follow'));
        $objTable->add_item("Whether or not acknowlegement will be sent when choosing a walk to Follow");
        $objTable->generate_line();

        $privacy = "3. You will receive automated messages from the system and possibly from the Leader but your Following of the walk will be hidden from other members<br>";
        $privacy .= "2. You will receive automated messages from the system and possibly from the Leader, and ";
        $privacy .= "your Following of the walk will be visible to other members who are following that walk. Others will be able to see your “Display name” as chosen by you when registering, but none of the other information you provide<br>";
        $privacy .= "1. Additionally, you will be able to send message to, and receive messages from, any other member with a privacy level of 1 who is Following the walk";
        $objTable->add_item("Privacy level ");
        $objTable->add_item($this->form->getInput('ra_privacy_level'));
        $objTable->add_item($privacy);
        $objTable->generate_line();

        $objTable->add_item("Notification range ");
        $objTable->add_item($this->form->getInput('ra_min_miles') . " to " . $this->form->getInput('ra_max_miles') . " miles");
        $objTable->add_item("Unless set to zero, you will be notified of new walks within this range."); // . $where);
        $objTable->generate_line();

        $objTable->add_item("Notify joiners ");
        $objTable->add_item($this->form->getInput('ra_notify_joiners'));
        $objTable->add_item("Whether or not you will be notified of new Followers (Leaders only)");
        $objTable->generate_line();
    }
    $objTable->generate_table();
    //echo ' <div id="validation-form-failed" data-backend-detail="User" data-message="';
    //echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '">';
    //echo '</div>';
    //echo '</form>';
    //  <?php echo $this->form->getInput('preferred_name');
    ?>

    <div class="control-group">

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
    <div id="validation-form-failed" data-backend-detail="user" data-message="<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>">
    </div>
</form>
<!-- End of code from com_ra_tools/views/profile/tmpl/default.php -->