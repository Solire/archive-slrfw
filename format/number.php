<?php

/**
 * Formatage des nombres
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Format;

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
    
    static function formatSize($valeur) {
        $strTmp = "";

        if (preg_match("#^[0-9]{1,}$#", $valeur)) {
            if ($valeur >= 1000000) {
                // Taille supÃ©rieur Ã  1 MegaOctet
                $strTmp = sprintf("%01.2f", $valeur / 1000000);
                // Suppression des "0" en fin de chaine
                $strTmp = preg_replace("#[\.]{1}[0]{1,}$#", "", $strTmp) . " Mo";
            } else if ($valeur >= 1000) {
                // Taille infÃ©rieur Ã  1 MegaOctet
                $strTmp = sprintf("%01.2f", $valeur / 1000);
                // Suppression des "0" en fin de chaine
                $strTmp = preg_replace("#[\.]{1}[0]{1,}$#", "", $strTmp) . " Ko";
            } else if ($valeur >= 0) {
                // Taille infÃ©rieur Ã  1 KiloOctet
                $strTmp = $valeur . " octect";
                if ($valeur > 0)
                    $strTmp .= "s";
            }
            else {
                $strTmp = $valeur;
            }
        } else {
            $strTmp = $valeur;
        }

        return $strTmp;
    }

}

