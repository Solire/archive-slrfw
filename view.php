<?php
/**
 * Gestionnaire de vue
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

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

    /**
     * Chemins vers la vue
     *
     * @var array
     */
    private $dirs = array();

    private $_format = null;
    private $_translate = null;
    private $_controller;
    private $_action;

    /**
     * @var template
     */
    private $_Template = null;

    /**
     * @var String Nom du template
     */
    private $_TemplateName = null;

    public function __construct($translate)
    {
        $this->_translate = $translate;
    }

    public function _($string)
    {
        return $this->_translate->_($string);
    }

    public function enable($enable)
    {
        $this->_enable = $enable;
    }

    public function main($enable)
    {
        $this->_main = $enable;
    }

    public function isEnabled()
    {
        return $this->_enable;
    }

    public function isIncludeMain()
    {
        return $this->_main;
    }

    /**
     * Enregistrement des dossiers possible pour la vue
     *
     * @param string $dir Chemin vers la vue
     */
    public function setDir($dir)
    {
        $this->dirs[] = $dir;
    }

    /**
     * Renvois le chemin vers le fichier de vue
     *
     * @param string  $controller Nom du controller (peut être null, pour tester
     * les fichiers à la racine du dossier de vue, comme main.phtml par exemple)
     * @param string  $action     Nom de l'action
     * @param boolean $format     Appliquer le formatage de l'action
     *
     * @return boolean
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

        foreach ($this->dirs as $dir) {
            $path = new Path($dir . DS . $filePath, Path::SILENT);
            if ($path->get()) {
                return $path->get();
            }
        }

        return false;
    }

    public function setFormat($format)
    {
        $this->_format = $format;
    }

    public function setTemplate($Name)
    {
        $this->_TemplateName = $Name;
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

    /**
     * Affiche la vue
     *
     * @param string $controller Nom du controller
     * @param string $action     Nom de l'action
     * @param boolean $custom    ???
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

    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * Ajoute un fichier
     *
     * @param string $fileName Nom du fichier avec l'extension
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

