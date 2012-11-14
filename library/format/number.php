<?php

/**
 * Formatage des nombres
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library\Format;

/**
 * Formatage des nombres
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Number {

    /**
     *
     * @param float $number 
     * @param bool $formatShow
     * @param string $currencyChar Caractère de la devise
     * @return string 
     */
    static function formatMoney($number, $formatShow = false, $currencyChar = "") {
        if (!$formatShow)
            $valformat = str_replace(".00", "", sprintf("%01.2f", "$number"));
        else
            $valformat = number_format($number, 2, '.', ' ');
        return $valformat . $currencyChar;
    }
    
    /**
     *
     * @param float $number 
     * @param int $nbZero
     * @param string $currencyChar Caractère de la devise
     * @return string 
     */
    static function zeroFill($number, $nbZero = 11) {
        return printf("%0" . $nbZero . "d", $number);
    }

}

