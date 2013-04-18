<?php
/**
 * Gestionnaire des hooks
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Gestionnaire des hooks
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Hook
{
    /**
     * Données d'environnement
     *
     * @var array
     */
    private $data = array();

    /**
     * Répertoires dans lesquels se trouve les hooks
     *
     * @var type
     */
    private $dirs = array();

    /**
     * Sous dossier dans lequel est rangé les hooks
     *
     * @var string
     */
    private $subDir = '';

    /**
     * Nom du hook
     *
     * @var string
     */
    protected $codeName;

    /**
     * Nom identifiant le gestionnaire de hook
     *
     * @var string
     */
    protected $hookLibName;


    /**
     * Chargement du gestionnaire de hook
     *
     * @param string $name Nom identifiant le type de gestionnaire de hook
     */
    public function __construct($name)
    {
        $this->hookLibName = $name;
    }

    /**
     * Chargement de la liste des répertoires dans lesqueslles se trouve les hooks
     *
     * @param array $dirs Liste des répertoires
     *
     * @return void
     */
    public function setDirs(array $dirs)
    {
        $this->dirs = $dirs;
    }

    /**
     * Enregistre le nom du sous dossier
     *
     * @param string $subDir Chemin du sous dossier
     *
     * @return void
     */
    public function setSubdirName($subDir)
    {
        $this->subDir = $subDir;
    }

    /**
     * Execution d'un hook
     *
     * @param string $codeName Identifiant du hook
     *
     * @return void
     * @uses Path Contrôle du chemin du fichier
     * @throws Exception\lib En cas de problème de configuration
     */
    public function exec($codeName)
    {
        if (empty($this->dirs)) {
            throw new Exception\lib('Problème de configuration appDirs');
        }

        $this->codeName = $codeName;
        unset($codeName);

        if (!empty($this->subDir)) {
            $baseDir = $this->subDir . DS;
        } else {
            $baseDir = '';
        }

        $baseDir .= $this->codeName;
        foreach ($this->dirs as $dirInfo) {
            $dir = $dirInfo['dir'] . $baseDir;
            $path = new Path($dir, Path::SILENT);
            if ($path->get() === false) {
                continue;
            }

            $dir = opendir($path->get());
            while ($file = readdir($dir)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                if (is_dir($path->get() . $file)) {
                    continue;
                }

                $funcName = $dirInfo['name'] . '\\';
                if (!empty($this->subDir)) {
                    $funcName .= ucfirst($this->subDir) . '\\';
                }
                $funcName .= ucfirst($this->codeName)
                          . '\\' . pathinfo($file, PATHINFO_FILENAME);
                if (!function_exists($funcName)) {
                    include $path->get() . $file;
                }
                $funcName($this);
            }
            closedir($dir);
        }
    }

    /**
     * Enregistrement des variables d'environnement
     *
     * @param string $name  Nom de la variable
     * @param mixed  $value Contenu de la variable
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Renvois la valeur de la variable de l'environnement
     *
     * @param string $name Nom de la variable
     *
     * @return mixed
     * @ignore
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * Test l'existence d'une variable de l'environnement
     *
     * @param string $name Nom de la variable
     *
     * @return boolean
     * @ignore
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
}

