<?php
/**
 * Exception pour que Marvin la prenne en charge
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library\Exception;

/**
 * Les MarvinException seront traités par la classe Marvin
 *
 * Une MarvinException entrainera un arret du script et l'envois d'un rapport
 * Marvin
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Marvin extends \Exception
{
    /**
     * Titre du message d'erreur
     * @var string
     */
    private $_title = 'Erreur';

    /**
     * Instancie une erreur qui fera l'objet d'un rapport
     *
     * @param \Exception $exc   Exception qui fait l'objet d'un rapport
     * @param string     $title Facultatif Titre de l'erreur
     */
    public function __construct(\Exception $exc, $title = null)
    {
        parent::__construct($exc->getMessage(), 0, $exc);

        if (!empty($title)) {
            $this->title($title);
        }
    }

    /**
     * Ajoute un titre au rapport
     *
     * @param string $string titre du rapport
     *
     * @return void
     */
    public function title($string)
    {
        $this->_title = $string;
    }

    /**
     * Renvois le titre du rapport
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
}

