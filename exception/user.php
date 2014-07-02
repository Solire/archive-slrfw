<?php
/**
 * Erreur de l'utilisateur
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Exception;

/**
 * Erreur de l'utilisateur
 *
 * Par exemple, formulaire incomplet, ajout d'un produit non existant etc...
 * tout ce qui demande l'affichage d'un message pour l'utilisateur
 * <br/>Ces erreurs entraineront l'affichage d'un message paramétrable pour
 * l'utilisateur
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class User extends \Exception
{
    use \Slrfw\Formulaire\ExceptionTrait;

    /**
     * Lien vers la page qui suit le message
     * @var string
     */
    private $_link;

    /**
     * Temps avant réorientation de la page
     * @var int
     */
    private $_auto;

    /**
     * Paramètre les règles de redirection
     *
     * @param string $link url vers laquelle rediriger l'utilisateur
     * @param int    $auto Mettre le temps après lequel la redirection se fait automatiquement.
     * Laisser à vide pour ne pas avoir de redirection automatique
     *
     * @return void
     */
    public function link($link, $auto = null)
    {
        $this->_link = $link;
        $this->_auto = $auto;
    }

    /**
     * Renvois les paramètres de redirection
     *
     * @return array
     */
    public function get()
    {
        return array($this->_link, $this->_auto);
    }
}

