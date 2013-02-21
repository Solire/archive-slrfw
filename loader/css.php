<?php
/**
 * Gestionnaire des fichiers css
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Dev <dave@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Loader;

/**
 * Gestionnaire des fichiers css
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Dev <dave@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Css
{
    /**
     * Liste des librairies css à intégrer
     * @var array
     */
    private $libraries = array();

    /**
     * Chargement du gestionnaire de css
     */
    public function __construct($base)
    {
        $this->base = $base;
    }

    /**
     * Renvois la liste des librairies css
     *
     * @return array
     */
    public function getLibraries()
    {
        return $this->libraries;
    }

    /**
     * Renvois les clé de la liste des librairies
     *
     * @return array
     * @deprecated ???
     */
    public function loadedLibraries()
    {
        return array_keys($this->libraries);
    }

    /**
     * Renvois le chemin absolu vers la librairie en fonction des AppDirs
     *
     * @param string $filePath Chemin relatif de la librairie
     *
     * @return string
     */
    protected function getPath($filePath)
    {
        $dirs = \Slrfw\FrontController::getAppDirs();

        foreach ($dirs as $dir) {
            $path = new \Slrfw\Path($dir['dir'] . DS . $filePath, \Slrfw\Path::SILENT);
            if ($path->get()) {
                return $dir['dir'] . DS . $filePath;
            }
        }

        return null;
    }

    /**
     * Affiche le code html pour l'intégration des librairies css
     *
     * @return string
     */
    public function __toString()
    {
        $css = '';
        foreach ($this->libraries as $lib) {
            if (substr($lib['src'], 0, 7) != 'http://'
                && substr($lib['src'], 0, 8) != 'https://'
            ) {
                $path = $this->getPath($lib['src']);
                if (!empty($path)) {
                    $path .= '?' . filemtime($path);
                }
            } else {
                $path = $lib['src'];
            }

            $css   .= '        <link rel="stylesheet" href="' . $path
                    . '" type="text/css" media="' . $lib['media']
                    . '" />' . "\n";
        }

        return $css;

    }

    /**
     * Ajoute une librarie css
     *
     * @param string  $path  chemin absolu ou relatif du fichier
     * @param string  $media media de la librarie
     * @param boolean $local Active ou non le préfixage par css/
     *
     * @return void
     */
    public function addLibrary($path, $media = 'screen')
    {
        $this->libraries[] = array(
            'src' => $path,
            'media' => $media,
        );
    }
}

