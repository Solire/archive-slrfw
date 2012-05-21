<?php
/**
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @filesource
 */

/**
 * Classe de contrôle des chemins de fichiers.
 * @package Library
 * @author Adrien <aimbert@solire.fr>
 */
class Path
{

    /**
     * Envois d'exceptions activé ou non
     * false par défaut
     * @var boolean
     */
    private static $_silentMode = false;

    /**
     * Chemin absolu vers le fichier
     * @var string
     */
    protected $_path = '';

    /**
     * Test le chemin relatif et renvois grace à {@link path::get()} le chemin absolu
     *
     * @param string $filePath Chemin relatif à tester
     * @throws LibException Fichier introuvable.
     * @uses Path::$_slientMode
     */
    public function __construct($filePath)
    {
        $this->_path = $this->test($filePath);

        if ($this->_path == false) {
            if (!self::$_silentMode) {
                throw new LibException('Fichier introuvable : ' . $filePath);
            }
        }
    }

    /**
     * Active ou enlenve l'envois d'exceptions
     *
     * @param boolean $value
     * @uses Path::$_slientMode Edition
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
     * @return string
     */
    public function get()
    {
        if (is_dir($this->_path))
            return $this->_path . DIRECTORY_SEPARATOR;
        else
            return $this->_path;
    }

    /**
     * Permet d'ajouter des dossiers dans lesquelles chercher les fichiers
     *
     * @param string $path
     * @return boolean True si l'opération c'est bien déroulée.
     */
    static public function addPath($path)
    {
        $path = realpath($path);
        if (!$path)
            return false;

        $usePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($usePaths as $usePath) {
            if ($usePath == $path)
                return true;
        }

        set_include_path(get_include_path()
            . PATH_SEPARATOR . $path
        );

        return true;
    }

    /**
     * Test le chemin
     *
     * @param string $filePath
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