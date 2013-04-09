<?php

/**
 * Formatage des dates et heures
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Format;

/**
 * Formatage des dates et heures
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class DateTime {

    /**
     *  Renvoi un temps relatif entre maintenant et la date en paramètre
     * 
     * @param int $timestampOrDate
     * @param bool $modeDate 
     * @return string 
     */
    static function RelativeTime($timestampOrDate, $modeDate = false) {
        if ($modeDate) {
            $timestamp = strtotime($timestampOrDate);
            if ($timestamp == "") {
                return;
            }
        } else {
            $timestamp = $timestampOrDate;
        }
        $difference = time() - $timestamp;
        $periods = array("seconde", "minute", "heure", "jour", "semaine",
            "mois", "année", "décennie");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        if ($difference > 0) { // this was in the past
            $ending = "il y a";
        } else { // this was in the future
            $difference = -$difference;
            $ending = "dans";
        }
        for ($j = 0; $difference >= $lengths[$j]; $j++)
            $difference /= $lengths[$j];
        $difference = round($difference);
        if ($difference != 1 && $periods[$j] != "mois")
            $periods[$j].= "s";
        $text = "$ending $difference $periods[$j]";
        return $text;
    }

    /**
     *
     * @param string $date
     * @param bool $moiscomplet
     * @return string
     */
    static function toText($date, $moiscomplet = FALSE) {
        if ($moiscomplet)
            $lesmois = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
        else
            $lesmois = array("", "janv.", "fév.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc.");

        $date = explode("-", $date);
        $d = strpos($date[2], " ");
        if ($d) {
            $heure = substr($date[2], $d + 1, strrpos($date[2], ":") - ($d + 1));
            $ladate = (int) $date[2] . " " . $lesmois[(int) $date[1]] . " " . $date[0] . " à " . $heure;
        }
        else
            $ladate = (int) $date[2] . " " . $lesmois[(int) $date[1]] . " " . $date[0];

        return ($ladate);
    }

    /**
     *
     * @param string $datetime
     * @param bool $moiscomplet
     * @return string
     */
    static function toShortText($datetime) {
        $lesmois = array("", "janv.", "fév.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc.");
        $datePart = substr($datetime, 0, 10);
        $timePart = substr($datetime, 11, 5);
        if ($datePart != date("Y-m-d")) {
            $date = explode("-", $datePart);
            $ladate = (int) $date[2] . " " . $lesmois[(int) $date[1]];
            if ($date[0] != date("Y")) {
                $ladate .=  " " . $date[0];
            }
            
        } else {
            $ladate = $timePart;
        }
        

        return ($ladate);
    }

}

