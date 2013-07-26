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
    const RANDOM_ALL = 1;
    const RANDOM_NUMERIC = 2;
    const RANDOM_ALPHA = 3;
    const RANDOM_ALPHALOWER = 4;
    const RANDOM_ALPHAUPPER = 5;

    /**
     * Renvoi une chaine de n ($strLen) caracteres aleatoirement.
     *
     * @param int    $strLen
     * @param string $type "all" / "numeric" / "alphalower" / "alphaupper"
     *
     * @return string
     */
    public static function random($strLen, $type = self::RANDOM_ALL)
    {
        $string = "";
        switch ($type) {
            case self::RANDOM_NUMERIC :
                $chaine = '0123456789';
                break;

            case self::RANDOM_ALPHA :
                $chaine = 'abcdefghijklmnopqrstuvwxyz'
                        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            case self::RANDOM_ALPHALOWER :
                $chaine = 'abcdefghijklmnopqrstuvwxyz';
                break;

            case self::RANDOM_ALPHAUPPER :
                $chaine = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            default :
                $chaine = 'abcdefghijklmnopqrstuvwxyz'
                        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                        . '0123456789';
                break;
        }

        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $strLen; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }
    
    /**
     * Construit une url propre à partir d'une chaine de caractere,
     * @param string $string la chaine de base à transformer.
     * @param string $charReplacement caractere de remplacement.
     * @return string La chaine transfomée.
     */
    static function friendlyURL($string, $charReplacement = "-") {
        $string = preg_replace("`\[.*\]`U", "", $string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', $charReplacement, $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace("`&([a-z])?(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
        $string = preg_replace(array("`[^a-z0-9]`i", "`[$charReplacement]+`"), $charReplacement, $string);
        return strtolower(trim($string, $charReplacement));
    }
}

