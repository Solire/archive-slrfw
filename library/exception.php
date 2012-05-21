<?php
/**
 * Bibliothèque d'Exception
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 * @filesource
 */

/**
 * Les MarvinException seront traités par la classe Marvin
 *
 * Une MarvinException entrainera un arret du script et l'envois d'un rapport
 * Marvin
 *
 * @see Marvin
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class MarvinException extends Exception
{
    /**
     * Titre du message d'erreur
     * @var string
     */
    private $_code = 'Erreur';

    /**
     * Ajoute un code HTTP à l'erreur
     * @param int $code
     */
    public function title($code)
    {
        $this->_code = $code;
    }

    /**
     * Renvois le code HTTP de l'erreur
     * @return int
     */
    public function getTitle()
    {
        return $this->_code;
    }
}

/**
 * Erreur de l'utilisateur
 *
 * Par exemple, formulaire incomplet, ajout d'un produit non existant etc...
 * tout ce qui demande l'affichage d'un message pour l'utilisateur
 * <br/>Ces erreurs entraineront l'affichage d'un message paramétrable pour
 * l'utilisateur
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class UserException extends Exception
{
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
     * @param string $link
     * @param int $auto
     */
    public function link($link, $auto = null)
    {
        $this->_link = $link;
        $this->_auto = $auto;
    }

    /**
     * Renvois les paramètres de redirection
     * @return array
     */
    public function get()
    {
        return array($this->_link, $this->_auto);
    }
}

/**
 * Erreur HTTP
 *
 * Les HTTPexceptions entraineront un blocage de la page et la modification du
 * header http pour afficher son code d'erreur
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class HttpException extends Exception
{
    /**
     * Code HTTP
     * @var int
     */
    private $_code = 500;

    /**
     * Ajoute un code HTTP à l'erreur
     * @param int $code
     */
    public function http($code)
    {
        $this->_code = $code;
    }

    /**
     * Renvois le code HTTP de l'erreur
     * @return int
     */
    public function getHttp()
    {
        return $this->_code;
    }
}

/**
 * Exception de base
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class LibException extends Exception {}
