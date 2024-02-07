<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ra_events
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_events\Site\Controller;

\defined('_JEXEC') or die;

//use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
//use Joomla\CMS\Language\Multilanguage;
//use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

//use Joomla\CMS\Router\Route;
//use Joomla\CMS\Uri\Uri;
//use Joomla\Utilities\ArrayHelper;

/**
 * Events class.
 *
 * @since  1.0.0
 */
class Programme_dayController extends FormController {

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional
     * @param   array   $config  Configuration array for model. Optional
     *
     * @return  object	The model
     *
     * @since   1.0.0
     */
//    public function getModel($name = 'Events', $prefix = 'Site', $config = array()) {
//        return parent::getModel($name, $prefix, array('ignore_request' => true));
//    }
    public function showDay($day, $group, $intro, $limit) {
        die('Pragrame_dayController');
// This code is invoked from the controller and from the template file default.php
// $group and $day will have been defined
        $objHelper = new ToolsHelper;
        echo "<h2>" . $day . "</h2>";

        if (!$intro == "") {
            // spaces will have been replaced with underscores, so reinstate them
            echo '<p>' . str_replace("_", " ", $intro) . '</p>';
        }
        $target = 'index.php?option=com_ra_tools&task=programme.showDay';
        $target .= '&group=' . $group;
        $target .= '&title=' . $title;
        $target .= '&intro=' . str_replace(" ", "_", $intro);
        $target .= '&day=';

//
// Generate the seven entries at the top of the page, as a table with a single row
// The current day is shown in bold, others as buttons
        //echo '<table style="margin-right: auto; margin-left: auto;">';
        echo '<table class="table-responsive">';
        echo "<tr>";
        $week = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        for ($i = 0; $i < 7; $i++) {
            echo "<td>";
            $weekday = $week[$i];
            //$link = URI::base() . 'index.php?option=com_ra_tools&view=programme_day&day=' . $weekday;
            $link = URI::base() . $target . $weekday;
            if ($day == $weekday) {
                echo '<b>' . $day . '<b>';
            } else {
                if ($i < 5) {
                    $colour = 'p7474';
                } else {
                    $colour = 'p0555';
                }
                //echo $objHelper->buildLink($target . $weekday, $weekday, False, "link-button button-" . $colour);
                echo $objHelper->buildLink($link, $weekday, False, "link-button button-" . $colour);
            }
            echo "</td>";
        }
        echo "</tr>";
        echo "</table>";

        return;
        $options = new RJsonwalksFeedoptions($group);
        $objFeed = new RJsonwalksFeed($options);
        if ($this->show_cancelled == 'Y') {
            $objFeed->filterCancelled();
        }
        $objFeed->filterDayofweek(array($day));
        $count = $objFeed->numberWalks();

        if ($count == 0) {
            echo "no walks for " . $group;
        } else {
            if ($limit > 0) {
                $objFeed->noWalks($limit);
            }
            $display = new RJsonwalksStdDisplay(); // code to display the walks in tabbed format
            $display->displayGroup = true;
            $display->displayGradesIcon = false;
            $display->emailDisplayFormat = 2;      // don't show actual email addresses
            $objFeed->Display($display);           // display walks information
        }
    }

}
