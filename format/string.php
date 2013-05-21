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
     * Tableau des caractère accentué
     * @var array
     */
    static private $pattern = array('/À/', '/Á/', '/Â/', '/Ã/', '/Ä/', '/Å/', '/à/', '/á/', '/â/',
        '/ã/', '/ä/', '/å/', '/Ò/', '/Ó/', '/Ô/', '/Õ/', '/Ö/', '/Ø/', '/ò/',
        '/ó/', '/ô/', '/õ/', '/ö/', '/ø/', '/È/', '/É/', '/Ê/', '/Ë/', '/é/',
        '/è/', '/ê/', '/ë/', '/Ç/', '/ç/', '/Ì/', '/Í/', '/Î/', '/Ï/', '/ì/',
        '/í/', '/î/', '/ï/', '/Ù/', '/Ú/', '/Û/', '/Ü/', '/ù/', '/ú/', '/û/',
        '/ü/', '/ÿ/', '/Ñ/', '/ñ/', '/&/');

    /**
     * Tableau des caractères de remplacement des caractères accentués
     * @var array
     */
    static private $repPat = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a',
        'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'E', 'E',
        'E', 'E', 'e', 'e', 'e', 'e', 'C', 'c', 'I', 'I', 'I', 'I', 'i', 'i',
        'i', 'i', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'y', 'N', 'n', 'et');

    /**
     * Supprime l'intégralité des accents de la chaine.
     *
     * @param string $String chaîne à traiter
     *
     * @return string
     */
    static function noAccent($string)
    {
        $string = preg_replace(static::$pattern, self::$repPat, $string);
        return $string;
    }

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
        $string = '';
        switch ($type) {
            case self::NUMERIC :
                $chaine = '0123456789';
                break;

            case self::ALPHA :
                $chaine = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            case self::ALPHALOWER :
                $chaine = 'abcdefghijklmnopqrstuvwxyz';
                break;

            case self::ALPHAUPPER :
                $chaine = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            default :
                $chaine = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }

        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $strLen; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }
}

