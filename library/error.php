<?php
/**
 * @package Library
 * @subpackage Error
 */

/**
 * Gestionnaire des erreurs
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
final class Error
{
    /**
     * Code HTTP de l'erreur
     * @var int
     */
    static protected $_code;

    /**
     * Liste des entêtes http utilisés
     * @todo Déporter les descriptions des erreurs http possible dans un tutorial
     * @var array
     */
    static private $_headers = array(
        301 => '301	Moved Permanently',
        401 => '401	Unauthorized', // Une authentification est nécessaire pour accéder à la ressource
        403 => '403	Forbidden', // L’authentification est refusée. Contrairement à l’erreur 401, aucune demande d’authentification ne sera faite
        404 => '404 Not Found',
        405	=> '405 Method Not Allowed',
        418 => '418	I’m a teapot',
        500 => '500 Internal Server Error',
        503 => '503	Service Unavailable', // Service temporairement indisponible ou en maintenance
    );

    /**
     * Fonctionnement par défaut, fait passer la page en erreur 500
     * @uses Error::http()
     */
    public static function run()
    {
        self::http(500);
    }

    /**
     * Affiche une erreur HTTP
     * @param int $code Code HTTP de l'erreur
     * @uses Error::setHeader()
     */
    public static function http($code)
    {
        self::$_code = $code;

        self::setHeader();

        $fileName = 'error/' . $code . '.phtml';
        if (file_exists($fileName))
            include $fileName;
        else
            include 'error/500.phtml';
    }

    /**
     * Affiche le message d'erreur demandé pour l'utilisateur
     * @uses Message
     * @param UserException $exc
     */
    public static function message(UserException $exc)
    {
        $message = new Message($exc->getMessage());
        $message->setEtat('error');
        list($link, $auto) = $exc->get();
        $message->addRedirect($link, $auto);
        try {
            $message->display();
        } catch (Exception $exc) {
            self::http(500);
        }
    }

    /**
     * Envois un rapport Marvin et affiche une erreur 500
     * @uses Marvin
     * @uses MarvinException::getTitle()
     * @param MarvinException $exc
     */
    public static function report(MarvinException $exc)
    {
        $marvin = new Marvin($exc->getTitle(), $exc);
        $marvin->display();

//        self::http(500);
    }



    /**
     * Affiche le header correspondant à l'erreur
     * @uses Error::$_headers
     */
    private static function setHeader()
    {
        header('HTTP/1.0 ' . self::$_headers[self::$_code]);
    }
}

