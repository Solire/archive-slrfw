<?php
/**
 * Gestionnaire des erreurs
 *
 * @package    Library
 * @subpackage Error
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @filesource
 */

namespace Slrfw;

/**
 * Gestionnaire des erreurs
 *
 * @package    Library
 * @subpackage Error
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @filesource
 */
final class Error
{
    /**
     * Code HTTP de l'erreur
     *
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
        /** Une authentification est nécessaire pour accéder à la ressource */
        401 => '401	Unauthorized',
        /** L’authentification est refusée. Contrairement à l’erreur 401, aucune
         * demande d’authentification ne sera faite
         */
        403 => '403	Forbidden',
        404 => '404 Not Found',
        405	=> '405 Method Not Allowed',
        418 => '418	I’m a teapot',
        500 => '500 Internal Server Error',
        503 => '503	Service Unavailable', // Service temporairement indisponible ou en maintenance
    );

    /**
     * Fonctionnement par défaut, fait passer la page en erreur 500
     *
     * @return void
     * @uses Error::http()
     */
    public static function run()
    {
        self::http(500);
    }

    /**
     * Affiche une erreur HTTP
     *
     * @param int|array $code Code HTTP de l'erreur
     *
     * @return void
     * @uses Error::setHeader()
     */
    public static function http($code)
    {
        $url = null;
        if (is_array($code)) {
            $url = $code[1];
            $code = $code[0];
        }

        self::$_code = $code;

        self::setHeader($url);

        $fileName = self::getPath($code);
        if ($fileName !== false) {
            include $fileName;
        } else {
            include 'slrfw/error/500.phtml';
        }
    }


    /**
     * Renvois le chemin vers la vue relative à l'erreur
     *
     * @param string $code code de l'erreur
     *
     * @return mixed Chemin vers le fichier ou false
     */
    private static function getPath($code)
    {
        $dirs = FrontController::getAppDirs();
        foreach ($dirs as $dir) {
            $path = $dir['dir'] . DS . 'error' . DS;
            $path = new Path($path . $code . '.phtml', Path::SILENT);
            if ($path->get()) {
                return $path->get();
            }
        }

        return false;
    }

    /**
     * Affiche le message d'erreur demandé pour l'utilisateur
     *
     * @param Exception\User $exc Exception utilisateur
     *
     * @return void
     * @uses Message
     */
    public static function message(Exception\User $exc)
    {
        $message = new Message($exc->getMessage());
        $message->setEtat('error');
        list($link, $auto) = $exc->get();
        $message->addRedirect($link, $auto);
        if ($exc->getTargetInputName() !== '') {
            $message->inputName = $exc->getTargetInputName();
        }
        try {
            $message->display();
        } catch (\Exception $exc) {
            self::http(500);
        }
    }

    /**
     * Envois un rapport Marvin et affiche une erreur 500
     *
     * @param Exception\Marvin $exc Exception à marquer d'un rapport
     *
     * @return void
     * @uses Marvin
     * @uses Exception\Marvin::getTitle()
     */
    public static function report(Exception\Marvin $exc)
    {
        $marvin = new Marvin($exc->getTitle(), $exc);
        $marvin->send();

        self::run();
    }



    /**
     * Affiche le header correspondant à l'erreur
     *
     * @param string $url Ajoute une redirection au header
     *
     * @return void
     * @uses Error::$_headers
     */
    private static function setHeader($url = null)
    {
        header('HTTP/1.0 ' . self::$_headers[self::$_code]);
        if ($url !== null) {
            self::setHeaderRedirect($url);
        }
    }

    /**
     * Ajoute une redirection dans le header
     *
     * @param string $url Url vers laquelle on redirige l'utilisateur
     *
     * @return void
     */
    private static function setHeaderRedirect($url)
    {
        header('Location: ' . $url);
    }
}

