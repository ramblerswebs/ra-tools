<?php

/**
 * @version    CVS: 4.1.0
 * @package    Com_Ra_wf
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Date\Date;

/**
 * Supports an HTML select list of categories
 *
 * @since  4.1.0
 */
class TimecreatedField extends FormField {

    /**
     * The form field type.
     *
     * @var    string
     * @since  4.1.0
     */
    protected $type = 'timecreated';

    /**
     * Method to get the field input markup.
     *
     * @return  string    The field input markup.
     *
     * @since   4.1.0
     */
    protected function getInput() {
        // Initialize variables.
        $html = array();

        $time_created = $this->value;

        if (!strtotime($time_created)) {
            $time_created = Factory::getDate('now', Factory::getConfig()->get('offset'))->toSql(true);
            $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $time_created . '" />';
        }

        $hidden = (boolean) $this->element['hidden'];

        if ($hidden == null || !$hidden) {
            $jdate = new Date($time_created);
            $pretty_date = $jdate->format(Text::_('DATE_FORMAT_LC2'));
            $html[] = "<div>" . $pretty_date . "</div>";
        }

        return implode($html);
    }

}
