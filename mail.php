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
     * @uses Registry db
     * @uses View
     * @uses TranslateMysql
     * @uses DB
     * @link http://solire-02/wiki/index.php/Mail_%28lib%29 explication & docu
     */
    public function __construct($name)
    {
        $this->codeName = $name;

        $db = Registry::get('db');
        $translate = new TranslateMysql(ID_VERSION, ID_API, $db);
        $translate->addTranslation();
        $this->view = new View($translate);

        $configLoc = Registry::get('envconfig');
        $default = $configLoc->get('mail');
        foreach ($default as $key => $value) {
            $this->$key = $value;
        }
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
     * Envois du mail
     *
     * @return void
     */
    public function send()
    {
        /** Désolé c'est brutal **/
        $dir = pathinfo(__FILE__, PATHINFO_DIRNAME);
        include_once $dir . '/external/Zend/Mail.php';
        unset($dir);

        $mail = new \Zend_Mail('utf-8');

        $mail->setBodyHtml($this->loadBody())
             ->setFrom($this->from)
             ->addTo($this->to)
             ->setSubject($this->subject);

        if (isset($this->bcc)) {
            $mail->addBcc($this->bcc);
        }

        $mail->send();
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

            $realMainPath = false;

            if ($this->mainUse) {
                /**
                 * On cherche le fichier main
                 */

                $mainPath = $config->get('dirs', 'mail') . 'main.phtml';
                $realMainPath = FrontController::search($mainPath, false);
                if (empty($realMainPath)) {
                    $realMainPath = FrontController::search($mainPath);

                    if (empty($realMainPath)) {
                        throw new Exception\Lib('Aucun fichier mail main.phtml');
                    }
                }
            }

            $path = $config->get('dirs', 'mail') . $this->codeName . '.phtml';
            $realPath = FrontController::search($path, false);

            if (empty($realPath)) {
                $realPath = FrontController::search($path);

                if (empty($realPath)) {
                    throw new Exception\Lib('Aucun fichier mail ' . $this->codeName);
                }
            }

            ob_start();
            $this->view->displayPath($realPath, $realMainPath);
            $this->body = ob_get_clean();
        }

        return $this->body;
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
