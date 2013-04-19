 <?php
/**
 * Gestionnaire de vue
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Gestionnaire de vue
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class View
{
    private $_enable = true;
    private $_main = true;

    private $_format = null;
    private $_translate = null;
    private $_controller;
    private $_action;

    /**
     * Chargement d'une nouvelle vue
     *
     * @param TranslateMysql $translate Gestionnaire de traduction
     */
    public function __construct($translate)
    {
        $this->_translate = $translate;
    }

    /**
     * Alias à l'utilisation de translate
     *
     * @param string $string Chaine à traduire
     * @param string $aide   ??
     *
     * @return string
     * @todo
     */
    public function _($string, $aide = '')
    {
        return $this->_translate->_($string, $aide);
    }

    /**
     * Activer ou désactiver la vue
     *
     * @param boolean $enable Vrai pour activer
     *
     * @return void
     */
    public function enable($enable)
    {
        $this->_enable = $enable;
    }

    /**
     * Activer ou désactiver l'utilisation du main
     *
     * @param boolean $enable Vrai pour activer
     *
     * @return void
     */
    public function main($enable)
    {
        $this->_main = $enable;
    }

    /**
     * Test si la vue est active
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_enable;
    }

    /**
     * Test si le main est actif
     *
     * @return boolean
     */
    public function isIncludeMain()
    {
        return $this->_main;
    }

    /**
     * Renvois le chemin vers le fichier de vue
     *
     * @param string  $controller Nom du controller (peut être null, pour tester
     * les fichiers à la racine du dossier de vue, comme main.phtml par exemple)
     * @param string  $action     Nom de l'action
     * @param boolean $format     Appliquer le formatage de l'action
     *
     * @return string|boolean
     */
    private function getpath($controller, $action, $format = true)
    {
        if ($format === true) {
            $action = sprintf($this->_format, $action);
        }

        if (!empty($controller)) {
            $filePath = $controller . DS . $action;
        } else {
            $filePath = $action;
        }

        $dir = FrontController::$mainConfig->get('dirs', 'views');
        return FrontController::search($dir . $filePath);
    }

    /**
     * Enregistre le template de format des actions
     *
     * @param string $format format des actions
     *
     * @return void
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }

    /**
     * Affiche le contenu
     *
     * @return void
     */
    public function content()
    {
        $path = $this->getpath($this->_controller, $this->_action);
        if ($path !== false) {
            include $path;
        }
    }

    /**
     * Test si la vue existe pour la combinaison controller / action
     *
     * @param string $controller Nom du controller
     * @param string $action     Nom de l'action
     *
     * @return boolean
     */
    public function exists($controller, $action)
    {
        $controller = strtolower($controller);
        $action = strtolower($action);
        $path = $this->getpath($controller, $action);
        if ($path !== false) {
            return true;
        }
        return false;
    }

    // @todo Documenter à quoi sert custom
    /**
     * Affiche la vue
     *
     * @param string  $controller Nom du controller
     * @param string  $action     Nom de l'action
     * @param boolean $custom     ???
     *
     * @return void
     */
    public function display($controller, $action, $custom = true)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);

        if (($this->isEnabled() || $custom) && $this->isIncludeMain()) {
            $main = $this->getpath(null, 'main');
            if ($main !== false) {
                include $main;
            }
        } else {
            $this->content();
        }
    }

    /**
     * Ajoute un fichier
     *
     * @param string $fileName Nom du fichier avec l'extension
     *
     * @return void
     */
    public function add($fileName)
    {
        $file = $this->getpath(null, $fileName, false);
        if ($file !== false) {
            include $file;
        }
    }

    /**
     * Inclut un fichier phtml du dossier template.
     *
     * @param string $file Nom du fichier template à inclure
     *
     * @return void
     */
    public function template($file)
    {
        $file = $this->getpath('template', $file);
        if ($file !== false) {
            include $file;
        }
    }
}

