<?php
/**
 * Affichage de message
 *
 * @package    Library
 * @subpackage Error
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw;

/**
 * Affichage de message
 *
 * @package    Library
 * @subpackage Error
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Message
{
    /**
     * Message pour l'utilisateur
     *
     * @var string
     */
    public $message;

    /**
     * Etat par défaut
     *
     * @var string
     */
    public $etat = 'success';

    /**
     * Temps avant la redirection html
     *
     * @var int
     */
    public $auto = null;

    /**
     * Valeurs possible pour l'etat
     *
     * @var array
     */
    private $etats = array('alert', 'error', 'success');

    /**
     * Basehref du site
     *
     * @var string
     */
    public $baseHref = '';


    /**
     * Prépare un message
     *
     * @param string $message Phrase à afficher
     *
     * @registry basehref
     */
    public function __construct($message)
    {
        $this->baseHref = Registry::get('basehref');
        $this->message = $message;
    }

    /**
     * Modifie l'etat du message
     *
     * @param string $etat Etat à appliquer au message
     *
     * @return void
     */
    public function setEtat($etat)
    {
        if (in_array($etat, $this->etats)) {
            $this->etat = $etat;
        }
    }


    /**
     * Ajoute une redirection automatique
     *
     * @param string $url  Url vers laquelle rediriger l'utilisateur
     * @param string $auto Durée en seconde de la redirection
     *
     * @return void
     */
    public function addRedirect($url, $auto = null)
    {
        if ($url == '/') {
            $url = '';
        }

        if (strpos($url, $this->baseHref) === false) {
            $this->url = $this->baseHref . $url;
        } else {
            $this->url = $url;
        }
        $this->auto = $auto;
    }

    /**
     * Affiche le message
     *
     * @return void
     */
    public function display()
    {
        $ajax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
        if ($ajax) {
            $this->displayJson ();
        } else {
            $this->displayHtml ();
        }
    }

    /**
     * Affiche le message sous la forme d'un json pour sa gestion en ajax
     *
     * @return void
     */
    private function displayJson()
    {
        foreach ($this as $key => $value) {
            if (strpos($key, '_') === 0) {
                continue;
            }

            $data[$key] = $value;
        }

        echo json_encode($data);
    }

    /**
     * Renvois le chemin vers la vue relative à l'erreur
     *
     * @param string $code code de l'erreur
     *
     * @return mixed Chemin vers le fichier ou false
     */
    private function getPath()
    {
        $dirs = FrontController::getAppDirs();
        foreach ($dirs as $dir) {
            $path = $dir['dir'] . DS . 'error' . DS . 'message.phtml';
            $path = new Path($path, Path::SILENT);
            if ($path->get()) {
                return $path->get();
            }
        }

        /** utilisation du message.phtml présent dans slrfw **/
        $path = pathinfo(__FILE__, PATHINFO_DIRNAME) . DS . 'error/message.phtml';
        $path = new Path($path, Path::SILENT);
        if ($path->get()) {
            return $path->get();
        }

        return false;
    }

    /**
     * Affiche le message en html
     *
     * @return void
     * @throws Slrfw\Exception\Lib
     */
    private function displayHtml()
    {
        $path = $this->getPath();
        if ($path !== false) {
            include $path;
        } else {
            throw new Exception\Lib('Le fichier message.phtml est absent');
        }
    }
}

