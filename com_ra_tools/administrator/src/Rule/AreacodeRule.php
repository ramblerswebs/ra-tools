<?php

/*
 * Ensures given group code is valid
 * the XML file containing a field to be validated must include:
 * <fieldset addruleprefix="Ramblers\Component\Ra_tools\Administrator\Rule">
 */

namespace Ramblers\Component\Ra_tools\Administrator\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class AreacodeRule extends FormRule implements DatabaseAwareInterface {

    use DatabaseAwareTrait;

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null) {

        if (strlen($value) != 2) {
            $element->addAttribute('message', 'Enter 2 character Area code');
            return false;
        }

// Validate for valid group
        if (!ctype_alpha($value)) {
            $element->addAttribute('message', 'Characters must be alpha');
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
                ->from($db->quoteName('#__ra_areas'))
                ->where($db->quoteName('code') . ' = ' . $db->q($value));
        $db->setQuery($query);
        $count = (bool) $db->loadResult();

        if ($count == 0) {
            $element->addAttribute('message', 'Area code not found');
            return false;
        }

        return true;
    }

}
