<?php
/**
 * Affichage de message
 *
 * @package    Library
 * @subpackage Error
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw\Library;

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
     * Affiche le message en html
     *
     * @return void
     * @throws LibException
     */
    private function displayHtml()
    {
        $fileName = 'lib/message.phtml';
        if (file_exists($fileName)) {
            include $fileName;
        } else {
            throw new LibException('Le fichier message.phtml est absent');
        }
    }
}

