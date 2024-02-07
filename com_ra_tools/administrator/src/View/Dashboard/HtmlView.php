<?php

/**
 *  @version     4.0.11
 * @package     com_ra_tools
 * @copyright   Copyleft (C) 2021
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk

 * 14/01/24 CB createLink and showConfig
 */
// No direct access

namespace Ramblers\Component\Ra_tools\Administrator\View\Dashboard;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class HtmlView extends BaseHtmlView {

    public function display($tpl = null): void {
        parent::display($tpl);
    }

    public function createLink($component, $view, $caption) {
        $objHelper = new ToolsHelper;
        $target = '<li><a href="index.php?option=' . $component . '&amp;view=' . $view . '"';
        $target .= '" target="_self">' . $caption . ' (';

        switch ($view) {
            case 'area_list';
                $table = 'areas';
                break;
            case 'group_list';
                $table = 'groups';
                break;
            case 'mail_lsts':
                $table = 'mail_lists';
                break;
            case 'mailshots':
                $table = 'mail_shots';
                break;
            case 'subscriptions':
                $table = 'mail_subscriptions';
                break;
            default:
                $table = $view;
        }
        $sql = 'SELECT COUNT(*) FROM #__ra_' . $table;
        $count = $objHelper->getValue($sql);
        $target .= number_format($count);
        $target .= ')</a></li>' . PHP_EOL;
        return $target;
    }

    public function showConfig($component) {
        $objHelper = new ToolsHelper;
        $target = '<li><a href="index.php?option=com_config&amp;view=component&amp;component=' . $component . '" target="_self">Configure ' . $component . ' (';
        $sql = 'SELECT s.version_id ';
        $sql .= 'FROM #__schemas AS s ';
        $sql .= 'INNER JOIN #__extensions as e ON e.extension_id = s.extension_id ';
        $sql .= 'WHERE e.element="' . $component . '"';
//        return $sql;
        $target .= $objHelper->getValue($sql);
        $target .= ')</a></li>' . PHP_EOL;
        return $target;
    }

}
