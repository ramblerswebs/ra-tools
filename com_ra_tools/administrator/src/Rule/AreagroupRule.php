<?php

/*
 * Ensures given code is valid:
 *    a two character valid Area code
 *    a four character group code
 *    the three characters NAT (for national)
 * the XML file containing a field to be validated must include:
 * <fieldset addruleprefix="Ramblers\Component\Ra_tools\Administrator\Rule">
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class AreagroupcodeRule extends FormRule implements DatabaseAwareInterface {

    use DatabaseAwareTrait;

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null) {

        if (strtouppert($value) == 'NAT') {
            return true;
        }

        if ($strlength(value) == 2) {
            $table = '#__ra_areas';
        } elseif ($strlength(value) == 4) {
            $table = '#__ra_groups';
        } else {
            $element->addAttribute('message', 'Enter two characters for an Area, or four for a Group');
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('COUNT(*)')
                ->from($db->quoteName($table))
                ->where($db->quoteName('code') . ' = ' . $db->q($value));
        $db->setQuery($query);
        $count = (bool) $db->loadResult();

        if ($count == 0) {
            $element->addAttribute('message', 'Code not found');
            return false;
        }

        return true;
    }

}
