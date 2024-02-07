<?php

/*
 * Ensures given time code is valid
 * the XML file containing the field to be validated must include:
 * <fieldset addruleprefix="Ramblers\Component\Ra_tools\Administrator\Rule">
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class TimefieldRule extends FormRule implements DatabaseAwareInterface {

    use DatabaseAwareTrait;

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null) {

        if (strlen($value) != 5) {
            $element->addAttribute('message', 'Enter 2 character Hours, a full stop, then 2 character minutes');
            return false;
        }

        $hh = substr($value, 0, 2);
        if (!ctype_digit($hh)) {
            $element->addAttribute('message', 'First 2 characters must be numeric');
            return false;
        }
        if ($hh > 23) {
            $element->addAttribute('message', 'First two characters must be valid hour');
            return false;
        }

        $mm = substr($value, 3, 2);
        if (!ctype_digit($mm)) {
            $element->addAttribute('message', 'Last two characters must be numeric');
            return false;
        }
        if ($mm > 59) {
            $element->addAttribute('message', 'Last two characters must be valid minutes');
            return false;
        }

        $deliminator = substr($value, 2, 1);
        if (($deliminator == '.') OR ($deliminator == ':')) {
            return true;
        }
        $element->addAttribute('message', 'Please enter third characters as . or : (not ' . $deliminator . ')');
        return false;
    }

}
