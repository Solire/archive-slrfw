<?php
/**
 * Classe simple d'envois de mails utilisant les View (avec TranslateMysql)
 *  et Zend_Mail()
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Classe simple d'envois de mails utilisant les View (avec TranslateMysql)
 *  et Zend_Mail()
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Mail
{
    /**
     * Données du mail
     *
     * @var array
     */
    private $data = array();

    /**
     * Identifiant du mail
     *
     * @var string
     */
    protected $codeName;

    /**
     * Sujet du mail
     *
     * @var string
     */
    public $subject;

    /**
     * Contenu du mail
     *
     * @var string
     */
    public $body;

    /**
     * Adresse mail de l'expediteur
     *
     * @var string
     */
    public $from;

    /**
     * Adresse mail du destinataire
     *
     * @var string
     */
    public $to;

    /**
     * Adresse mail des destinaires en copie carpone
     *
     * @var string
     */
    public $bcc;

    /**
     *
     * @var View
     */
    private $view;

    /**
     * utilisation du main.phtml
     *
     * @var boolean
     */
    private $mainUse = false;

    /**
     * Création d'un nouveau mail
     *
     * Instantiation d'une vue avec chargement des outils de traduction suivis
     * du chargement des informations relatives au mail dans le fichier
     * de configuration relatif à l'environnement.
     *
     * @param string $name Nom identifiant la vue utilisée
     *
     * @uses Registry envconfig
     * @uses View
     * @link http://solire-02/wiki/index.php/Mail_%28lib%29 explication & docu
     */
    public function __construct($name)
    {
        $this->codeName = $name;
        $this->view = new View();

        $configLoc = Registry::get('envconfig');
        $default = $configLoc->get('mail');
        foreach ($default as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Renvois la vue utilisée dans le mail
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Active l'utilisation du main.phtml
     *
     * @return self
     */
    public function setMainUse()
    {
        $this->mainUse = true;

        return $this;
    }

    /**
     * Désactive l'utilisation du main.phtml
     *
     * @return self
     */
    public function disableMainUse()
    {
        $this->mainUse = false;

        return $this;
    }

    /**
     * Envois du mail
     *
     * @return void
     */
    public function send()
    {
        $mail = new \Zend\Mail\Message();

        $mail->setEncoding('utf-8')
             ->setFrom($this->from)
             ->addTo($this->to)
             ->setSubject($this->subject);

        if (isset($this->bcc)) {
            $mail->addBcc($this->bcc);
        }

        $htmlMarkup = $this->loadBody();
        $html = new \Zend\Mime\Part($htmlMarkup);
        $html->type = 'text/html; charset="utf-8"';

        $body = new \Zend\Mime\Message();
        $body->setParts(array($html));

        $mail->setBody($body);

        $transport = new \Zend\Mail\Transport\Sendmail();
        $transport->send($mail);
    }


    /**
     * Charge le corps du mail
     *
     * A noter que le main.phtml ne sera pas utilisé par déaut.
     * Il faut utiliser self::setMainUse() pour l'activer.
     *
     * @return string contenu du mail
     * @uses FrontController search
     * @uses Registry mainconfig
     * @throws Exception\Lib Aucun fichier phtml trouvé
     */
    public function loadBody()
    {
        if (!isset($this->body)) {
            $config = Registry::get('mainconfig');

            $this->view->setPathPrefix($config->get('dirs', 'mail'));

            if ($this->mainUse) {
                /**
                 * On cherche le fichier main
                 */

                $this->view->setMainPath('main.phtml');
            }

            ob_start();
            $this->view
                ->setViewPath($this->codeName . '.phtml')
                ->display()
            ;
            $this->body = ob_get_clean();
        }

        return $this->body;
    }

    /**
     * Supprime le cache du body du mail
     *
     * @return self
     */
    public function resetBody()
    {
        $this->body = null;

        return $this;
    }


    /**
     * Enregistrement des variables pour le mail
     *
     * @param string $name  Nom de la variable
     * @param mixed  $value Contenu de la variable
     *
     * @return void
     * @ignore
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        $this->view->$name = $value;
    }

    /**
     * Renvois la valeur de la variable pour le mail
     *
     * @param string $name Nom de la variable
     *
     * @return mixed
     * @ignore
     */
    public function __get($name)
    {
        if (!isset($this->data[$name])) {
            throw new Exception\Lib('Information ' . $name . ' manquante');
        }

        return $this->data[$name];
    }

    /**
     * Test l'existence d'une variable pour le mail
     *
     * @param string $name Nom de la variable
     *
     * @return boolean
     * @ignore
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
}
