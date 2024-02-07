<?php

/**
 * @version     1.0.8
 * @package     com_ra_tools
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Charlie <webmaster@bigley.me.uk> - https://www.stokeandnewcastleramblers.org.uk
 * 05/06/22 CB remove diagnostic display
 * 01/07/22 CB changes for new version of Ramblers Library
 * 18/02/23 CB printed programmes
 * 27/03/23 CB updated for Joomla 4
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

echo 'Code from ' . __FILE__ . '<br>';
echo 'area=' . $this->area . 'type=' . $this->display_type . '<br>';
echo 'intro=' . $this->intro . 'cancelled=' . $this->cancelled . '<br>';
echo '<!-- Code from ' . __FILE__ . '-->' . PHP_EOL;
