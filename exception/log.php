<?php
/**
 * Erreur HTTP
 *
 * @package    Library
 * @subpackage Error
 * @author     Dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Exception;

/**
 * Erreur HTTP
 *
 * @package    Library
 * @subpackage Error
 * @author     Dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Log extends \Exception
{

    /**
     * "constructeur de mon expetion"
     *
     * @param string $strErrorMsg message d'erreur à afficher.
     */
    public function __construct($strErrorMsg)
    {
        parent::__construct($strErrorMsg);
    }

    /**
     * affiche l'erreur et stop l'éxecution du script
     *
     * @return void
     */
    public function makeLogExeption()
    {
        echo '<pre>';
        echo print_r(
                utf8_decode(
                        'Erreur fatal ! -> Ficher ' . $this->getFile() . ' ligne '
                        . $this->getLine() . "\n"
                        . 'Description de l\'erreur : ' . $this->getMessage() . "\n"
                ), true
        );
        echo print_r(utf8_decode("\n" . $this->getTraceAsString()), true);
        echo '<pre>';
        exit;
    }

    /**
     * retourne l'objet sous forme de chaine
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        return '<pre>' . print_r($this, true) . '</pre>';
    }
}

