<?php
/**
 * Erreur HTTP
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library\Exception;

/**
 * Erreur HTTP
 *
 * Les HttpErrorExceptions entraineront un blocage de la page et la modification du
 * header http pour afficher son code d'erreur
 *
 * @package    Library
 * @subpackage Error
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class HttpError extends \Exception
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
     *
     * @param int    $code Code HTTP de l'erreur
     * @param string $url  Url vers laquelle rediriger l'utilisateur
     *
     * @return void
     */
    public function http($code, $url = null)
    {
        $this->_code = $code;
        $this->_url = $url;
    }

    /**
     * Renvois les informations relatives à l'erreur http
     *
     * @return string|array peut être le code http ou un tableau contenant le
     * code http et l'url vers laquelle rediriger l'utilisateur
     */
    public function getHttp()
    {
        if ($this->getCode() !== 0) {
            return $this->getCode();
        }

        return array($this->_code, $this->_url);
    }
}

