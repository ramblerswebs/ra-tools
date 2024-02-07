<?php

/*
 * 21/03/23 CB created from libraries/ramblers/html/html.php
 * 15/08/23 CB use URI:root when defining alt image
 */

namespace Ramblers\Component\Ra_tools\Site\Helpers;

class ToolsHtml {

    static function convertToText($html) {
        $text = str_replace("\r", " ", $html);
        $text = str_replace("\n", " ", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = strip_tags($text);
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        $text = trim($text);
        return $text;
    }

    static function removeNonBasicTags($html) {
        $text = str_replace("\r", " ", $html);
        $text = str_replace("\n", " ", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = RHtml::strip_tags_with_whitespace($text, '<b><strong>');
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        $text = trim($text);
        return $text;
    }

    static function strip_tags_with_whitespace($string, $allowable_tags = null) {
        $string = str_replace('<', ' <', $string);
        $string = strip_tags($string, $allowable_tags);
        $string = str_replace('  ', ' ', $string);
        $string = trim($string);

        return $string;
    }

    static function convert_mails($text) {
        $emails = RHtml::fetch_mails($text);
        foreach ($emails as $value) {
            // see https://docs.joomla.org/URLs_in_Joomla
            $image = Uri::root() . '/libraries/ramblers/images/symbol_at.png';
            $img = '<img src="' . $image . '" alt="@ sign" />';
            $email = str_replace("@", $img, $value);
            $text = str_replace($value, $email, $text);
        }
        return $text;
    }

    static function fetch_mails($text) {

        //String that recognizes an e-mail
        $str = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
        preg_match_all($str, $text, $out);
        //return a blank array if not true otherwise insert the email in $out and return
        return isset($out[0]) ? $out[0] : array();
    }

    static function addTableHeader($cols) {
        if (is_array($cols)) {
            $out = "<tr>";
            foreach ($cols as $value) {
                $out .= "<th>" . $value . "</th>";
            }
            $out .= "</tr>" . PHP_EOL;
            return $out;
        } else {
            return "<tr><td>invalid argument in html::addTableRows</td></tr>";
        }
    }

    static function addTableRow($cols, $class = "") {
        if (is_array($cols)) {
            if ($class == "") {
                $out = "<tr>";
            } else {
                $out = "<tr class='" . $class . "'>";
            }

            foreach ($cols as $value) {
                $out .= "<td>" . $value . "</td>";
            }
            $out .= "</tr>" . PHP_EOL;
            return $out;
        } else {
            return "<tr><td>invalid argument in html::addTableRows</td></tr>";
        }
    }

    static function withDiv($class, $text, $printOn) {
        $out = "";
        if ($printOn) {
            $out .= "&nbsp;&nbsp;&nbsp;" . $text;
        } else {
            $out .= "<div class='" . $class . "'>";
            $out .= $text;
            $out .= "</div>";
        }
        return $out;
    }

    static function displayTitle($title, $display) {
        If ($display == "Yes") {
            echo "<h2>" . $title . "</h2>";
        }
    }

}
