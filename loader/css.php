<?php

namespace Slrfw\Loader;

use Slrfw\FrontController;
use Slrfw\Path;

/**
 * Gestionnaire des fichiers css.
 *
 * @author     Dev <dave@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Css
{
    /**
     * Liste des librairies css à intégrer.
     *
     * @var array
     */
    private $libraries = [];

    /**
     * Chargement du gestionnaire de css.
     */
    public function __construct()
    {
    }

    /**
     * Renvois la liste des librairies css.
     *
     * @return array
     */
    public function getLibraries()
    {
        return $this->libraries;
    }

    /**
     * Renvois les clé de la liste des librairies.
     *
     * @return array
     *
     * @deprecated ???
     */
    public function loadedLibraries()
    {
        return array_keys($this->libraries);
    }

    /**
     * Ajoute une librarie css.
     *
     * @param string $path  chemin absolu ou relatif du fichier
     * @param string $media media de la librarie
     * @param bool   $local Active ou non le préfixage par css/
     *
     * @return void
     */
    public function addLibrary($path, $media = 'screen')
    {
        $this->libraries[] = [
            'src' => $path,
            'media' => $media,
        ];
    }

    /**
     * Affiche le code html pour l'intégration des librairies css.
     *
     * @return string
     */
    public function html($space = 8)
    {
        $css = '';
        foreach ($this->libraries as $lib) {
            if (substr($lib['src'], 0, 7) != 'http://'
                && substr($lib['src'], 0, 8) != 'https://'
            ) {
                $path = $this->getPath($lib['src']);
                if (empty($path)) {
                    $path = $lib['src'];
                } else {
                    $fileInfo = pathinfo($path);

                    $filemtime = filemtime($path);

                    $path = $fileInfo['dirname'] . '/' . $fileInfo['filename']
                          . '.' . $filemtime . '.css';
                }
            } else {
                $path = $lib['src'];
            }

            $css .= str_pad('', $space)
                  . '<link '
                  . 'rel="stylesheet" '
                  . 'href="' . $path. '" '
                  . 'type="text/css" media="' . $lib['media'] . '">'
                  . PHP_EOL
            ;
        }

        return $css;
    }

    public function __toString()
    {
        return $this->html();
    }

    /**
     * Renvois le chemin absolu vers la librairie en fonction des AppDirs.
     *
     * @param string $filePath Chemin relatif de la librairie
     *
     * @return string
     */
    protected function getPath($filePath)
    {
        $dirs = FrontController::getAppDirs();

        foreach ($dirs as $dir) {
            $path = new Path($dir['dir'] . DS . $filePath, Path::SILENT);
            if ($path->get()) {
                return $dir['dir'] . DS . $filePath;
            }
        }

        return null;
    }
}
