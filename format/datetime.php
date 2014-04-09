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
class DateTime
{
    /**
     *  Renvoi un temps relatif entre maintenant et la date en paramètre
     *
     * @param string|int $timestampOrDate
     * @param bool       $modeDate
     *
     * @return string
     * @deprecated ????
     */
    static function relativeTime1($timestampOrDate, $modeDate = false)
    {
        $periods = array(
            'seconde',
            'minute',
            'heure',
            'jour',
            'semaine',
            'mois',
            'année',
        );
        $lengths = array(
            '60',
            '60',
            '24',
            '7',
            '4.35',
            '12',
        );
        $difference = time() - $timestamp;
        if ($difference >= 0) {
            /**
             * C'est dans le passé
             */
            $ending = 'il y a';
        } else {
            /**
             * C'est dans le futur
             */
            $difference = -$difference;
            $ending = 'dans';
        }

        /**
         * On recherche la plus grande période seconde, minute etc.
         */
        $j = 0;
        while (isset($lengths[$j]) && $difference >= $lengths[$j]) {
            $difference /= $lengths[$j];
            $j++;
        }
        $difference = round($difference);
        if ($difference != 1 && $periods[$j] != 'mois') {
            $periods[$j] .= 's';
        }
        $text = $ending . ' ' . $difference . ' ' . $periods[$j];
        return $text;
    }

    /**
     *  Renvoi un temps relatif entre maintenant et la date en paramètre
     *
     * @param string|int $timestampOrDate date au format mysql ou timestamp
     * @param bool       $modeDate        vrai si c'est une date mysql, faux
     * si c'est un timestamp
     *
     * @return string
     */
    static function relativeTime($timestampOrDate, $modeDate = false)
    {
        /**
         * Tableau des noms des périodes
         */
        $periods = array(
            'année',
            'mois',
            'jour',
            'heure',
            'minute',
            'seconde',
        );

        /**
         * Tableau des attributs de la classe DateInterval
         * http://www.php.net/manual/fr/class.dateinterval.php
         */
        $periodsMember = array(
            'y',
            'm',
            'd',
            'h',
            'i',
            's',
        );

        $max = count($periodsMember);
        if ($modeDate) {
            /**
             * La date est nulle
             * On retourne null
             */
            if($timestampOrDate == "") {
                return null;
            }

            $time = $timestampOrDate;
            if (strlen($timestampOrDate) == 10) {
                /**
                 * Si l'heure n'est pas précisé (H:i:s)
                 * on limite le résultat à un nombre de jours
                 */
                $max  = 3;
            }
        } else {
            $time = '@' . $timestampOrDate;
        }

        $d = new \DateTime($time);
        $n = new \DateTime();
        $difference = $n->diff($d);

        if ($difference->invert > 0) {
            /**
             * C'est dans le passé
             */
            $ending = 'il y a';
        } else {
            /**
             * C'est dans le futur
             */
            $ending = 'dans';
        }

        $ii = 0;
        do {
            /**
             * Nom de l'attribut de la classe DateInterval
             */
            $mb = $periodsMember[$ii];

            /**
             * Nombre d'occurence (nombre de jours, de mois etc.)
             */
            $nb = $difference->$mb;

            /**
             * Nom de la période en français
             */
            $pr = $periods[$ii];

            $ii++;
        } while ($nb == 0 && $ii < $max);

        /**
         * Si on obtient plus de 7 jours, on parle de semaines
         */
        if ($mb == 'd' && $nb > 7) {
            $pr = 'semaine';
            $nb = round($nb / 7);
        }

        if ($nb > 1 && $pr != 'mois') {
            $pr .= 's';
        }

        $text = $ending . ' ' . $nb . ' ' . $pr;
        return $text;
    }

    /**
     * Renvoi la date en français
     *
     * @param string $date        date au format mysql
     * @param bool   $moiscomplet vrai si on veut la version complète du mois
     * (janvier), faux si on veut seulement une abréviation (janv.)
     *
     * @return string
     */
    static function toText($date, $moiscomplet = false, $jour = false)
    {
        if (substr($date,0, 10) == '0000-00-00') {
            return '';
        }

        $ladate = '';

        if ($jour) {
            $timestamp = strtotime($date);
            $nbJour = date('w', $timestamp);

            $jours = array(
                'dimanche',
                'lundi',
                'mardi',
                'mercredi',
                'jeudi',
                'vendredi',
                'samedi',
            );

            $ladate .= $jours[$nbJour];
        }

        if ($moiscomplet) {
            $lesmois = array(
                '',
                'janvier',
                'février',
                'mars',
                'avril',
                'mai',
                'juin',
                'juillet',
                'août',
                'septembre',
                'octobre',
                'novembre',
                'décembre'
            );
        } else {
            $lesmois = array(
                '',
                'janv.',
                'fév.',
                'mars',
                'avril',
                'mai',
                'juin',
                'juil.',
                'août',
                'sept.',
                'oct.',
                'nov.',
                'déc.'
            );
        }

        $dateTab = explode("-", substr($date, 0, 10));
        $ladate .= (int) $dateTab[2] . ' '
                 . $lesmois[(int) $dateTab[1]] . ' '
                 . $dateTab[0];

        $d = strlen($date);
        if ($d > 10) {
            $heure   = substr($date, 11, 5);
            $ladate .= ' à ' . $heure;
        }

        return $ladate;
    }

    /**
     * Renvoi la date au format court
     *
     * @param string $date date au format mysql
     *
     * @return string
     */
    static function toShortText($datetime, $moiscomplet = false)
    {
        if ($moiscomplet) {
            $lesmois = array(
                '',
                'janvier',
                'février',
                'mars',
                'avril',
                'mai',
                'juin',
                'juillet',
                'août',
                'septembre',
                'octobre',
                'novembre',
                'décembre'
            );
        } else {
            $lesmois = array(
                '',
                'janv.',
                'fév.',
                'mars',
                'avril',
                'mai',
                'juin',
                'juil.',
                'août',
                'sept.',
                'oct.',
                'nov.',
                'déc.'
            );
        }

        /**
         * On prend la partie date (année, mois et jour)
         */
        $datePart = substr($datetime, 0, 10);

        /**
         * On prend les heures et minutes mais pas les secondes
         */
        $timePart = substr($datetime, 11, 5);

        if ($datePart != date('Y-m-d')) {
            /**
             * Si ce n'est pas aujourd'hui, on précise la date
             */
            $date = explode('-', $datePart);
            $ladate = (int) $date[2] . ' ' . $lesmois[(int) $date[1]];

            if ($date[0] != date('Y')) {
                /**
                 * Si ce n'est pas la même année, on précise l'année
                 */
                $ladate .=  ' ' . $date[0];
            }
        } else {
            /**
             * Si c'est aujourd'hui, on précise uniquement l'heure
             */
            $ladate = $timePart;
        }

        return $ladate;
    }

    /**
     * Transforme une date au format sql dans un autre format, format francais
     * jj/mm/yyyy par défaut
     *
     * @param string $dateSql date au format sql
     * @param string $format  format de sortie accepté par date()
     *
     * @return string
     *
     * @link http://php.net/manual/en/function.date.php documentaion pour
     * paramètre $format
     */
    static public function sqlTo($dateSql, $format = 'd/m/Y')
    {
        $date = new \DateTime($dateSql);
        return $date->format($format);
    }

    /**
     *
     * 
     * @param string $dateFr
     * @param string $delimiter
     *
     * @return string
     * @throws \Slrfw\Exception\lib En cas d'erreur de format
     */
    static public function frToSql($dateFr, $delimiter = '/')
    {
        $sizeExpected = 8 + 2 * strlen($delimiter);
        if (strlen($dateFr) != $sizeExpected) {
            $format  = 'Wrong french date format %s';
            $message = sprintf($format, $dateFr);
            throw new \Slrfw\Exception\lib($message);
        }

        $dateArray = explode($delimiter, $dateFr);
        $dateArray = array_reverse($dateArray);
        $dateSql   = implode('-', $dateArray);
        unset($dateArray);

        return $dateSql;
    }
}

