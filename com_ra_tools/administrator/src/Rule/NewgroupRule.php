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

class NewgroupRule extends FormRule implements DatabaseAwareInterface {

    use DatabaseAwareTrait;

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null) {
        if (strlen($value) != 4) {
            $element->addAttribute('message', 'Enter 4 character Group code XXnn');
            return false;
        }

        $char_0_1 = substr($value, 0, 2);
        $char_2_3 = substr($value, 2, 2);
        if (!ctype_alpha($char_0_1)) {
            $element->addAttribute('message', 'First two characters must be alpha ' . $char_0_1 . '/' . $char_2_3);
            return false;
        }
        if (!ctype_digit($char_2_3)) {
            $element->addAttribute('message', 'Last two characters must be numeric ', $char_2_3);
            return false;
        }

        return true;
    }

}
