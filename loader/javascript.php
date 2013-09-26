<?php
/**
 * Gestionnaire des fichiers js
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Dev <dave@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Loader;

/**
 * Gestionnaire des fichiers js
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Dev <dave@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Javascript
{
    /**
     * Liste des librairies js à intégrer
     * @var array
     */
    private $libraries = array();

    /**
     * Chargement du gestionnaire de js
     */
    public function __construct()
    {}

    /**
     * Renvois la liste des librairies js
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
     * Affiche le code html pour l'intégration des librairies JS
     *
     * @return string
     */
    public function __toString()
    {
        $js = '';
        foreach ($this->libraries as $lib) {
            if (substr($lib['src'], 0, 7) != 'http://'
                && substr($lib['src'], 0, 8) != 'https://'
            ) {
                $path = $this->getPath($lib['src']);

                if (empty($path)) {
                    $path  = $lib['src'];
                } else {
                    $fileInfo  = pathinfo($path);

                    $filemtime = filemtime($path);

                    $path = $fileInfo['dirname'] . '/' . $fileInfo['filename']
                          . '.' . $filemtime . '.js';
                }
            } else {
                $path = $lib['src'];
            }

            $js .= '        <script src="' . $path
                 . '" type="text/javascript"></script>' . "\n";
        }

        return $js;
    }


    /**
     * Ajoute une librairie js à la page
     *
     * @param string  $path  Chemin absolu ou relatif vers le fichier
     * @param boolean $local Active ou non le préfixage par js/
     *
     * @return void
     */
    public function addLibrary($path)
    {
            $this->libraries[] = array(
                'src' => $path
            );
    }
}

