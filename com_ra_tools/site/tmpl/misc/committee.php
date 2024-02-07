<?php

/**
 * @version     4.0.11
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 19/06/21 only seek active contacts
 * 02/12/21 correction for email_to
 * 17/12/12 email using contacts form
 * 02/01/23 CB take email from user record if necessary
 * 25/01/23 CB sort by name within role
 * 24/09/23 CB remove duplicate c.user_id from sql lookup
 * 07/10/23 CB correct display if sort=name
 * 20/11/23 CB don't list in table if con_position is Null
 * 10/01/24 CB don't list in table if con_position is blank
 * 16/01/24 CB only show if in category committee
 */
// No direct access
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

defined('_JEXEC') or die;
$objHelper = new ToolsHelper;
echo '<h2>' . $this->params->get('page_title') . '</h2>';

$app = JFactory::getApplication();
$menu_params = $app->getMenu()->getActive()->getParams(); #
$sort = $menu_params->get('sort', 0);
$intro = $menu_params->get('page_intro', '');
$show_phone = $menu_params->get('show_phone', 1);
$show_email = $menu_params->get('show_email', 1);
$show_images = $menu_params->get('show_images', 0);

if (!$intro == '') {
    echo $intro;
}
$target = 'index.php?option=com_contact&view=contact&id=';
$objTable = new ToolsTable;
$sql = 'SELECT c.id, c.email_to, c.user_id, u.id AS UserId, u.email,';
if ($sort == 'name') {
    $objTable->add_column("Name", "L");
    $objTable->add_column("Role", "L");
    $sql .= 'c.name, c.con_position ';
    $order = 'c.name';
} else {
    $sql .= 'c.con_position, c.name ';
    $objTable->add_column("Role", "L");
    $objTable->add_column("Name", "L");
    if ($sort == 'role') {
        $order = 'c.con_position, c.name';
    } else {
        $order = 'c.ordering';
    }
}
if ($show_phone == 1) {
    $objTable->add_column("Phone", "L");
    $sql .= ', c.telephone, c.mobile ';
}

$objTable->add_column("email", "L");

$objTable->generate_header();
$sql .= 'FROM #__contact_details AS c ';
$sql .= 'LEFT JOIN #__users AS u ON u.id =  c.user_id ';
$sql .= 'INNER JOIN #__categories AS cat ON cat.id =  c.catid ';
$sql .= "WHERE c.con_position IS NOT NULL ";
$sql .= 'AND c.published=1 ';
$sql .= 'AND cat.extension="com_contact" ';
$sql .= 'AND cat.title="committee" ';
$sql .= 'ORDER BY ' . $order;
if (JDEBUG) {
    JFactory::getApplication()->enqueueMessage('sql=' . $sql, 'message');
}

$rows = $objHelper->getRows($sql);
foreach ($rows as $row) {
    if ($row->con_position != '') {
        if ($sort == 'name') {
            $objTable->add_item($row->name);
            $objTable->add_item($row->con_position); // . ' ' . $row->user_id);
        } else {
            $objTable->add_item($row->con_position);
            $objTable->add_item($row->name);
        }
        if ($show_phone == 1) {
            $phone = $row->telephone;
            if ($phone == '') {
                $phone = $row->mobile;
            } else {
                if (!$row->mobile == '') {
                    $phone .= '<br>' . $row->mobile;
                }
            }
            $objTable->add_item($phone);
        }
        if ($row->user_id == 0) {
            if ($row->email_to == '') {
                $objTable->add_item('');
            } else {
                $objTable->add_item(JHtml::_('email.cloak', $row->email_to, 1, 'Send email', 0));
            }
        } else {
            if ($row->email_to == '') {
                $sql = 'UPDATE #__contact_details SET email_to="' . $row->email . '" WHERE id=' . $row->UserId;
                $objHelper = new ToolsHelper;
                $objHelper->executeCommand($sql);
            }
            $objTable->add_item($objHelper->buildLink($target . $row->id, "Email"));
        }
        if ($row->con_position != '') {
            $objTable->generate_line();
        }
    }
}

$objTable->generate_table();
