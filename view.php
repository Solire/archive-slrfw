<?php
/**
 * Gestionnaire de vue
 *
 * @author     dev <dev@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Gestionnaire de vue
 *
 * @author     dev <dev@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class View
{
    /**
     * Définit si la vue incluse automatiquement après execution de l'action
     *
     * @var bool
     */
    private $enable = true;

    /**
     * Objet de traduction
     *
     * @var TranslateMysql
     */
    private $translate = false;

    /**
     * Chemin vers la vue pour le contenu
     *
     * @var boolean|Path
     */
    private $contentPath = false;

    /**
     * Chemin vers la vue "main"
     *
     * @var boolean|Path
     */
    private $mainPath = false;


    /**
     * Chargement d'une nouvelle vue
     */
    public function __construct()
    {}

    /**
     * Chargement de la classe de traduction
     *
     * @param TranslateMysql $translate
     *
     * @return void
     */
    public function setTranslate($translate)
    {
        $this->translate = $translate;
    }

    /**
     * Alias à l'utilisation de translate
     *
     * @param string $string Chaine à traduire
     * @param string $aide   Texte permettant de situer l'emplacement de la
     * chaine à traduire, exemple : 'Situé sur le bas de page'
     *
     * @return string
     * @uses TranslateMysql
     */
    public function _($string, $aide = '')
    {
        if ($this->translate !== false) {
            return $this->translate->_($string, $aide);
        }

        return $string;
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
        $this->enable = (boolean) $enable;
    }

    /**
     * Test si la vue est active
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enable;
    }

    /**
     * Affiche le contenu
     *
     * @return void
     */
    public function content()
    {
        include $this->contentPath->get();
    }

    /**
     * Affiche la vue
     *
     * @return void
     */
    public function display()
    {
        if ($this->mainPath !== false) {
            include $this->mainPath->get();
        } else {
            $this->content();
        }
    }

    /**
     * Enregistre le fichier "main"
     *
     * @param string $strPath chemin vers le fichier de vue
     *
     * @return self
     * @uses Path pour contrôler le chemin
     */
    public function setViewPath($strPath)
    {
        $this->contentPath = new Path($strPath);

        return $this;
    }

    /**
     * Enregistre le fichier de vue pour le contenu
     *
     * @param string $strPath chemin vers le fichier de vue
     *
     * @return self
     * @uses Path pour contrôler le chemin
     */
    public function setMainPath($strPath)
    {
        $this->mainPath = new Path($strPath);

        return $this;
    }

    /**
     * Annule l'utilisation du fichier "main"
     *
     *  @return self
     */
    public function unsetMain()
    {
        $this->mainPath = false;

        return $this;
    }

    /**
     * Ajoute un fichier
     *
     * @param string $fileName Nom du fichier avec l'extension
     *
     * @return void
     */
    public function add($filePath)
    {
        $dir = FrontController::$mainConfig->get('dirs', 'views');
        $file = FrontController::search($dir . $filePath);
        if ($file !== false) {
            include $file;
        }
    }
}
