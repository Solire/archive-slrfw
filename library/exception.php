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
    private $_title = 'Erreur';

    /**
     * Instancie une erreur qui fera l'objet d'un rapport
     * @param Exception $exc
     * @param string $title Facultatif Titre de l'erreur
     */
    public function __construct(Exception $exc, $title = null)
    {
        parent::__construct($exc->getMessage(), 0, $exc);

        if (!empty($title))
            $this->title($title);
    }

    /**
     * Ajoute un code HTTP à l'erreur
     * @param int $code
     */
    public function title($string)
    {
        $this->_title = $string;
    }

    /**
     * Renvois le code HTTP de l'erreur
     * @return int
     */
    public function getTitle()
    {
        return $this->_title;
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
 * Les HttpErrorExceptions entraineront un blocage de la page et la modification du
 * header http pour afficher son code d'erreur
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class HttpErrorException extends Exception
{
    /**
     * Code HTTP
     * @var int
     */
    private $_code = 500;

    /**
     * Url de redirection
     * @var string
     */
    private $_url = null;

    /**
     * Ajoute un code HTTP à l'erreur
     * @param int $code
     * @param string $url
     */
    public function http($code, $url = null)
    {
        $this->_code = $code;
        $this->_url = $url;
    }

    /**
     * Renvois le code HTTP de l'erreur
     * @return int
     */
    public function getHttp()
    {
        if ($this->getCode() !== 0) {
            return $this->getCode();
        }

        return array($this->_code, $this->_url);
    }
}

/**
 * Exception de base
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class LibException extends Exception {}
