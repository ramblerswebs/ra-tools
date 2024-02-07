<?php

/**
 * @module     RA Blogs
 * @author     Charlie Bigley
 * @website    https://demo.stokeandnewcastleramblers.org.uk
 * @copyleft   Copyleft 2022 Charlie Bigley webmaster@stokeandnewcastleramblers.org.uk All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 * 23/1012/22 CB created from mod_ra_sidebar
  24/07/23 CB rewritten for Joomla 4
 */
// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
if (file_exists(JPATH_LIBRARIES . '/ramblers')) {
    JLoader::registerPrefix('R', JPATH_LIBRARIES . '/ramblers');
}
require(ModuleHelper::getLayoutPath('mod_ra_tools', $params->get('layout', 'default')));

