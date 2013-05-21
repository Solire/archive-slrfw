<?php
/**
 * Formatage de chaines de caractères
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Format;

/**
 * Formatage de chaines de caractères
 *
 * @package    Library
 * @subpackage Format
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class String
{
    const ALL = 1;
    const NUMERIC = 2;
    const ALPHA = 3;
    const ALPHALOWER = 4;
    const ALPHAUPPER = 5;

    /**
     * Renvoi une chaine de n ($strLen) caracteres aleatoirement.
     *
     * @param int    $strLen
     * @param string $type "all" / "numeric" / "alphalower" / "alphaupper"
     *
     * @return string
     */
    public static function random($strLen, $type = self::ALL)
    {
        $string = "";
        switch ($type) {
            case self::NUMERIC :
                $chaine = "0123456789";
                break;

            case self::ALPHA :
                $chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;

            case self::ALPHALOWER :
                $chaine = "abcdefghijklmnopqrstuvwxyz";
                break;

            case self::ALPHAUPPER :
                $chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;

            default :
                $chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                break;
        }

        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $strLen; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }
}

