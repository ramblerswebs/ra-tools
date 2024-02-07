<?php

namespace Ramblers\Component\Ra_tools\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Firm\Field\FormField;
use Joomla\Database\DatabaseInterface;

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * nation Form Field class for the Ramblers component
 */
class NationField extends JFormFieldList {

    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Nation';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     */
    public function getOptions() {
        $db = Factory::getContainer()->get(DatabaseInterface::class);//use Joomla\Database\DatabaseInterface;

$db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('id, name');
        $query->from('#__ra_nations');
        $db->setQuery((string) $query);
        $messages = $db->loadObjectList();
        $options = array();

        if ($messages) {
            foreach ($messages as $message) {
                $options[] = JHtml::_('select.option', $message->id, $message->description);
            }
        }

        // Now this array of values is merged with the rest of the data items
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
