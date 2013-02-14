<?php

namespace Slrfw\Loader;

/** @todo faire la présentation du code */

class Css {

    private $libraries;

    public function __construct()
    {
        $this->libraries = array();
    }

    public function getLibraries()
    {
        return $this->libraries;
    }

    public function loadedLibraries()
    {
        return array_keys($this->libraries);
    }

    public function  __toString()
    {
        $css = "";
        foreach ($this->libraries as $lib) {
            if (substr($lib["src"], 0, 7) != 'http://'
                && substr($lib["src"], 0, 8) != 'https://'
                && file_exists("./medias/" . $lib["src"])
            ) {
                $filemtime = "?" . filemtime("./medias/" . $lib["src"]);
            }
            else {
                $filemtime = "";
            }

            $css   .= '        <link rel="stylesheet" href="' . $lib["src"]
                    . $filemtime . '" type="text/css" media="' . $lib["media"]
                    . '" title="" charset="utf-8" />' . "\n";
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
    public function addLibrary($path, $media = "screen", $local = true)
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
        {
            if ($local === true) {
                $this->libraries[] = array(
                    'src' => (substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://' ? '' : 'css/') . $path,
                    'media' => $media,
                );
            } else {
                $this->libraries[] = array(
                    'src' => $path,
                    'media' => $media,
                );
            }
        }
    }
}
