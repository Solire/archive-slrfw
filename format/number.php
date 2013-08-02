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
class Number
{

    /**
     * Affichage des prix
     *
     * @param float  $number
     * @param bool   $formatShow
     * @param string $currencyChar Caractère de la devise
     *
     * @return string
     * @deprecated
     */
    static function formatMoney($number, $formatShow = false, $currencyChar = "")
    {
        return self::money($number, $formatShow, $currencyChar);
    }

    /**
     * Formate un prix pour un affichage
     *
     * @param float  $price    Prix à afficher
     * @param bool   $show     Afficher ou non le ",00"
     * @param string $currency Nom de la monnaie utilisée
     *
     * @return string
     */
    static function money($price, $show = true, $currency = '')
    {
        $price = number_format($price, 2, ',', ' ');

        if ($show === false) {
            $price = str_replace(',00', '', $price);
        }
        return $price . $currency;
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

