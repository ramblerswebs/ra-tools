<?php

/*
 * Ensures given time code is valid
 * the XML file containing the field to be validated must include:
 * <fieldset addruleprefix="Ramblers\Component\Ra_tools\Administrator\Rule">
 */

namespace Ramblers\Component\Ra_tools\Administrator\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class AreagroupcodeRule extends FormRule implements DatabaseAwareInterface {

    use DatabaseAwareTrait;

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null) {
        if (strtoupper($value) == 'NAT') {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $char_0_1 = substr($value, 0, 2);
// Area code must be two alpha characters
        if (strlen($value) == 2) {
            if (!ctype_alpha($char_0_1)) {
                $element->addAttribute('message', 'Area code must be two alpha characters');
                return false;
            }
            $query->select('COUNT(*)')
                    ->from($db->quoteName('#__ra_areas'))
                    ->where($db->quoteName('code') . ' = ' . $db->q($value));
            $db->setQuery($query);
            $count = (bool) $db->loadResult();

            if ($count == 0) {
                $element->addAttribute('message', 'Area code not found');
                return false;
            } else {
                return true;
            }
        }
        if (strlen($value) != 4) {
            $element->addAttribute('message', 'Enter 2 character Area code, 4 character Group code or NAT');
            return false;
        }

// Validate for valid group
        if (!ctype_alpha($char_0_1)) {
            $element->addAttribute('message', 'First two characters must be alpha');
            return false;
        }
        $char_2_3 = substr($value, 2, 2);

        if (!ctype_digit($char_2_3)) {
            $element->addAttribute('message', 'Last two characters must be numeric');
            return false;
        }
        $query->select('COUNT(*)')
                ->from($db->quoteName('#__ra_groups'))
                ->where($db->quoteName('code') . ' = ' . $db->q($value));
        $db->setQuery($query);
        $count = (int) $db->loadResult();

        if ($count == 0) {
            $element->addAttribute('message', 'Group code not found');
            return false;
        }
        return true;
    }

}
