<?php
/**
 * Gestionnaire de mails
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Gestionnaire de mails
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
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
     * @param string $name Nom identifiant la vue utilisée
     *
     * @uses Registry envconfig
     * @uses Registry db
     * @uses View
     * @uses TranslateMysql
     * @uses DB
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
     * @return void
     */
    public function setMainUse()
    {
        $this->mainUse = true;
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
     * @return void
     * @uses FrontController search
     * @uses Registry mainconfig
     * @throws Exception\Lib Aucun fichier phtml trouvé
     */
    public function loadBody()
    {
        if (!isset($this->body)) {
            $config = Registry::get('mainconfig');
            $path = $config->get('dirs', 'mail') . $this->codeName . '.phtml';
            $realPath = FrontController::search($path, false);

            if (empty($realPath)) {
                $realPath = FrontController::search($path);

                if (empty($realPath)) {
                    throw new Exception\Lib('Aucun fichier mail ' . $this->codeName);
                }
            }

            ob_start();
            $this->view->displayPath($realPath, $this->mainUse);
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

