<?php

/**
 * Description of WalksPrinted
 *    Create a printed program of the walks and display a button to allow user to download it as a doc file
 *
 * @author Charlie Bigley
 * 20/02/23 CB Created from RJsonwalksStdCsv
 * 28/02/23 CB no seconds in date; remove 'approx"
 *
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

class RJsonwalksStdWalksprinted extends RJsonwalksDisplaybase {

    private $current_month;
    private $filename;
    private $buttonClass = "button-p1815";
    private $handle;

//    public $removeHTML = false;
//    public $convertToASCII = false;

    public function __construct($filename = "tmp/walks-download") {
        parent::__construct();
        $this->filename = $filename . (new DateTime())->format('YmdHis') . ".doc";
    }

    private function beginFile() {
        $this->handle = fopen($this->filename, 'w'); //open file for writing
        if ($this->handle === false) {
            return $this->handle;
        }
        fwrite($this->handle, '<!DOCTYPE html>' . PHP_EOL);
        fwrite($this->handle, ' <html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL);
        fwrite($this->handle, '<head>' . PHP_EOL);
        fwrite($this->handle, ' <title></title>' . PHP_EOL);
        fwrite($this->handle, '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . PHP_EOL);
        fwrite($this->handle, '</head>' . PHP_EOL);
        fwrite($this->handle, '<body>' . PHP_EOL);
    }

    public function DisplayWalks($walks) {
        if ($this->beginFile() === false) {
            die('Cannot open file:  ' . $this->filename);
        } else {
            $walks->sort(RJsonwalksWalk::SORT_DATE, RJsonwalksWalk::SORT_TIME, RJsonwalksWalk::SORT_DISTANCE);
            $items = $walks->allWalks();

            $count = 0;
            foreach ($items as $walk) {
                $count++;
                $data = '<table>';
                $details = $this->displayWalkProgramme($walk);
            }
            $this->endFile();

            echo $count . ' walks extracted<br>';
            $this->createButton();
        }
    }

    private function displayWalkProgramme($walk) {
        $line = '<hr>';
        fwrite($this->handle, $line);

        $line = '<p>';
        $line .= $walk->walkDate->format('D j M y') . ': ';
        $month = $walk->walkDate->format('F');
        if ($month != $this->current_month) {
            $this->current_month = $month;
            fwrite($this->handle, '<u><b>' . strtoupper($month) . '</u></b>' . PHP_EOL);
//            echo $line;
        }

        $line .= $walk->distanceMiles . ' miles/';
        $line .= $walk->distanceKm . ' km - ';
        if ($walk->isLinear) {
            $line .= "Linear - ";
        } else {
            $line .= "Circular - ";
        }
        $line .= $walk->nationalGrade;
        $line .= '</p>' . PHP_EOL;
        fwrite($this->handle, $line);
//        echo $line . '<br>';

        $line = '<p>';
        $line .= '<b>' . $walk->title . '</b>';
        $line .= '</p>' . PHP_EOL;
        fwrite($this->handle, $line);
//        echo $line . '<br>';

        $start_location = $walk->startLocation;
        $line = '<p>';
        $line .= 'Starts at ' . substr($start_location->getTextTime(), 0, 5);
        $line .= ' ' . $start_location->description . ' ';
        $line .= $start_location->postcode . ' ';
        $line .= $start_location->gridref;
        $line .= '</p>' . PHP_EOL;
        fwrite($this->handle, $line);
        //       echo $line . '<br>';

        if ($walk->meetLocation != null) {
            $meet_location = $walk->meetLocation;
            $line = '<p>';
            $line .= 'Meet at ' . substr($meet_location->getTextTime(), 0, 5);
            $line .= ' ' . $meet_location->description . ' ';
            $line .= $meet_location->postcode . ' ';
            $line .= $meet_location->gridref;
            $line .= '</p>' . PHP_EOL;
            fwrite($this->handle, $line);
//        echo $line;
        }

        if ($walk->finishLocation != null) {
            $end_location = $walk->finishLocation;
            $line = '<p>';
            $line .= 'Finishes ' . substr($end_location->getTextTime(), 0, 5);
            $line .= ' ' . $end_location->description . ' ';
            $line .= $end_location->postcode . ' ';
            $line .= $end_location->gridref;
            $line .= '</p>' . PHP_EOL;
            fwrite($this->handle, $line);
//            echo $line . '<br>';
        }
        $line = '<p>';
        $line .= $walk->description;
        $line .= '</p>' . PHP_EOL;
//        echo $line . '<br>';
        fwrite($this->handle, $line);

        if ($walk->additionalNotes != '') {
            $line = '<p><i>';
            $line .= $walk->additionalNotes;
            $line = '</i></p>' . PHP_EOL;
//            echo $line . '<br>';
        }

        $line = '<p>';
        $line .= 'Contact ' . $walk->contactName;
        $line .= ' ' . $walk->telephone1;
        if ($walk->telephone2 != '') {
            $line .= ' ' . $walk->telephone2;
        }
        if ($walk->getEmail() != '') {
            $line .= ' ' . $walk->getEmail();
        }
        $line .= '</p>' . PHP_EOL;
//        echo $line . '<br>';
        fwrite($this->handle, $line);
        /*

          if ($walk->isLeader) {
          $array[] = "Yes";
          } else {
          $array[] = "No";
          }

          if ($this->removeHTML) {
          $array[] = html_entity_decode(strip_tags($walk->description));
          } else {
          $array[] = $walk->descriptionHtml;
          }
          $array[] = $walk->pace;
          $array[] = $walk->ascentFeet;
          $array[] = $walk->ascentMetres;
          $array[] = $walk->localGrade;

          return $array;
         */
    }

    private function createButton() {
        //       echo '<a href="' . $this->filename . '" class="link-button ' . $this->buttonClass . '">Download walks programme</a>';
        echo '<a href="' . $this->filename . '">Download walks programme</a><br>';
    }

    private function endFile() {
        /*
         * write the end of the HTML, close the output file
         */

        fwrite($this->handle, '</body>' . PHP_EOL);
        fwrite($this->handle, '</html>' . PHP_EOL);
        fclose($this->handle);
    }

}
