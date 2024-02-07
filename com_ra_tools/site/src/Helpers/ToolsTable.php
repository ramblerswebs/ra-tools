<?php

/*
 * 11/11/23 CB table-responsive
 */

namespace Ramblers\Component\Ra_tools\Site\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/*
  <style type=@text/css@>
  TH {color: white; background-color: gray}
  TD {background-color: silver}
  </style>
 */

class ToolsTable {

    public $bgcolour;
    public $border;
    private $buttonClass = "button-p1815";
    public $class;
    private $csv;
    private $filename;
    public $font_name;
    public $font_size;
    private $handle;
    public $header;
    public $num_columns;
    public $num_rows;
    public $percentage;
    public $value;
    public $width;

    function __construct() {
        $this->column_pointer = 0;
        $this->num_columns = 0;
        $this->num_rows = 0;
        $this->font_name = "Arial";
        $this->fonts_size = 10;
        $this->border = 1;
        $this->width = "95";
    }

    function define_fonts($font_name = "Arial", $fonts_size = 2) {
        $this->$font_name = $font_name;
        $this->$fonts_size = $fonts_size;
    }

    function set_csv($csv) {
        if (strlen($csv) > 0) {
            $this->filename = "/tmp/download_" . $csv . '_' . (new DateTime())->format('YmdHis') . ".csv";
            $this->handle = fopen($this->filename, 'w'); //open file for writing
            If ($this->handle === false) {
                echo 'Cannot open file:  ' . $this->filename . '<br>';
                die(print_r(error_get_last(), true));
            } else {
                $this->csv = 'Y';
            }
        }
    }

    function set_width($width) {
        $this->width = $width;
    }

    function add_column($header, $percentage = "") {
        $this->header[$this->num_columns] = $header;
        $this->percentage[$this->num_columns] = $percentage;
        $this->num_columns++;
    }

    function add_count() {

    }

    function add_header($header, $colour = 'grey') {

        $class = ToolsHelper::lookupColourCode($colour, 'T');
//        echo $colour . ' ' . $class . '<br>';
        ;        // assumes parameter is a comma delimited list of column headings
        $this->header = explode(",", $header);
        $this->num_columns = count($this->header);
        for ($i = 0; $i < $this->num_columns; $i++) {
            $this->percentage[$i] = '';
//            echo $i . ' ' . $this->header[$i] . '<br>';
        }
        $this->generate_header($class);
    }

    function add_item($value) {
        $this->value[$this->column_pointer] = $value;
//echo "<br>add_item $this->num_columns " . $this->action[$this->num_columns] . $this->text[$this->num_columns];
        $this->column_pointer++;
    }

    function generate_header($colour = "grey") {
        if ($this->csv === 'Y') {
//            for ($i = 0; $i < $this->num_columns; $i++) {
            fputcsv($this->handle, $this->header);
//            }
        } else {
            $class = ToolsHelper::lookupColourCode($colour, 'T');
            echo '<div class="table-reponsive">' . PHP_EOL;
            echo '<table ';
            echo "class=\"" . $class . "\" ";
            echo "width=\"$this->width%\" border=\"$this->border\">" . PHP_EOL;
            echo '  <thead>';
            for ($i = 0; $i < $this->num_columns; $i++) {
                echo '<th ';
                echo " width=\"" . $this->percentage[$i] . "%\">";
                echo $this->header[$i];
                echo '</th>' . PHP_EOL;
            }
            echo '</thead>' . PHP_EOL;
        }
        $this->column_pointer = 0;
        $this->num_rows = 1;
    }

    function generate_line($row_colour = "", $max_columns = "0") {
        if ($this->csv === 'Y') {
            fputcsv($this->handle, $this->value);
        } else {
            if ($row_colour == "") {
                echo "<TR>";
            } else {
                echo "<TR  bgcolor='" . $row_colour . "'>";
            }

// 04/01/21 should add 'colspan="x"' on the last cell
            /*
              if ($max_columns == "0") {
              $max = $this->num_columns;
              $colspan = 0;
              } else {
              if ($max_columns < $this->num_columns) {
              // less columns than usual
              $colspan = $this->numcolumns - $max_columns;
              } else {
              $max = $this->num_columns;
              }
              }
             */

//        if ($max_columns == "0") {
            $max = $this->num_columns;
//        } else {
//            $max = $max_columns;
//        }
//        echo "<!--max " . $max . "-->";
            for ($i = 0; $i < $max; $i++) {
                echo "<TD ";

                echo " width=\"" . $this->percentage[$i] . "%\"";
                if (!$row_colour == "") {
//                echo " bgcolor=" . $this->bgcolour[$i];
//            } else {
                    echo " bgcolor=" . $row_colour;
                }
                echo ">";
                if ($this->value[$i] == "") {
                    echo "&nbsp";
                } else {
                    echo $this->value[$i] . "</TD>";
                }
                $this->value[$i] = "";
            }
            echo "</TR>" . PHP_EOL;
        }

        $this->column_pointer = 0;
        $this->num_rows++;
    }

    function generate_table() {
        if ($this->csv === 'Y') {
            fclose($this->handle);
            echo $this->num_rows . ' rows written to ' . $this->filename . ', click to download<br>';
            echo '<a href="' . $this->filename . '" class="link-button ' . $this->buttonClass . '">Download report as CSV</a>';
        } else {
            echo '</table>' . PHP_EOL;
            echo '</div>' . PHP_EOL;    // table-reponsive
        }
    }

    function get_Columns() {
        return $this->num_columns;
    }

    function get_rows() {
        return $this->num_rows - 1;
    }

    function show_csv($file) {
//        echo $file . '<br>';
        $handle = fopen($file, "rb");
        if (FALSE === $handle) {
            fclose($dest);
            echo "Failed to open source $file<br>";
            return 0;
        }
        $record_count = 0;
        while (($fields = fgetcsv($handle, $BUFFER_SIZE, ",")) !== FALSE) {
            $record_count++;
            if ($record_count == 1) {
                for ($i = 0; $i < count($fields); $i++) {
                    $this->add_column($fields[$i], 'L');
                }
                $this->generate_header();
            } else {
                for ($i = 0; $i < count($fields); $i++) {
                    $this->add_item($fields[$i]);
                }
                $this->generate_line();
            }
        }
        $this->generate_table();
        return 1;
    }

}
