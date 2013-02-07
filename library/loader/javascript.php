<?php

namespace Slrfw\Library\Loader;

/** @todo faire la présentation du code */

class Javascript {

    private $libraries;

    public function __construct() {
        $this->libraries = array();
    }

    public function getLibraries() {
        return $this->libraries;
    }

    public function loadedLibraries() {
        return array_keys($this->libraries);
    }

    public function  __toString()
    {
        $js = "";
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

            $js .= '        <script src="' . $lib["src"] . $filemtime . '" type="text/javascript"></script>' . "\n";
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
    public function addLibrary($path, $local = true)
    {
        if ($local === true) {
            $this->libraries[]['src'] = (substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://' ? '' : 'js/') . $path;
        } else {
            $this->libraries[]['src'] = $path;
        }
    }

}

