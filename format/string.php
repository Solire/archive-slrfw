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
    static function noAccent($String)
    {
        $String = preg_replace(static::$pattern, self::$repPat, $String);
        return $String;
    }
    
}

