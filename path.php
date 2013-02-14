<?php
/**
 * Classe de contrôle des chemins de fichiers
 *
 * @package    Library
 * @subpackage Core
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw;

/**
 * Classe de contrôle des chemins de fichiers
 *
 * @package    Library
 * @subpackage Core
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Path
{
    /**
     * Envois d'exceptions activé ou non
     * false par défaut
     *
     * @var boolean
     * @deprecated
     */
    private static $_silentMode = false;

    /**
     * Chemin absolu vers le fichier
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Mode silencieux
     * À mettre dans $option du construct pour annuler les envois d'exception
     */
    const SILENT = 18;

    /**
     * Test le chemin relatif $filePath
     *
     * @param string $filePath Chemin relatif à tester
     * @param mixed  $option   Constante à mettre pour changer le comportement (voir SILENT)
     *
     * @throws Exception Fichier introuvable.
     * @uses Path::test()
     * @uses Path::$_slientMode
     */
    public function __construct($filePath, $option = 0)
    {
        $this->_path = $this->test($filePath);

        if ($this->_path == false) {
            if (!self::$_silentMode && $option != self::SILENT) {
                throw new \Exception('Fichier introuvable : ' . $filePath);
            }
        }
    }

    /**
     * Active ou enlenve l'envois d'exceptions
     *
     * @param boolean $value Vrai pour mettre en silencieux
     *
     * @return void
     * @uses Path::$_slientMode Edition
     * @deprecated
     */
    public static function silence($value = true)
    {
        self::$_silentMode = $value;
    }

    /**
     * Donne le chemin absolue vers le fichier
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Renvois le chemin du fichier ou du dossier
     *
     * @return string
     */
    public function get()
    {
        if (!$this->_path) {
            return false;
        }

        if (is_dir($this->_path)) {
            return $this->_path . DIRECTORY_SEPARATOR;
        } else {
            return $this->_path;
        }
    }

    /**
     * Permet d'ajouter des dossiers dans lesquelles chercher les fichiers
     *
     * @param string $path Dossier à ajouter
     *
     * @return boolean True si l'opération c'est bien déroulée.
     * @static
     */
    static public function addPath($path)
    {
        $path = realpath($path);
        if (!$path) {
            return false;
        }

        $usePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($usePaths as $usePath) {
            if ($usePath == $path) {
                return true;
            }
        }

        set_include_path(get_include_path()
            . PATH_SEPARATOR . $path
        );

        return true;
    }

    /**
     * Test le chemin
     *
     * @param string $filePath Chemin vers le fichier
     *
     * @return mixed le chemin du fichier ou FALSE si il n'existe aucun fichier
     */
    private function test($filePath)
    {
        $usePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($usePaths as $usePath) {
            if ($usePath != '.') {
                $testFilePath = $usePath . DIRECTORY_SEPARATOR . $filePath;
            } else {
                $testFilePath = $filePath;
            }
            if (file_exists($testFilePath)) {
                return realpath($testFilePath);
            }
        }

        return false;
    }
}

