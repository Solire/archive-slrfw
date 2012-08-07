<?php
/**
 * Gestionnaire de connexion à la base de données
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

/**
 * Gestionnaire de connexion à la base de données
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Config
{
    /**
     * Contenu du fichier de config
     *
     * @var array
     */
    private $_ini = null;

    /**
     * Charge un nouveau fichier de configuration
     *
     * @param string $iniFile Chemin vers le fichier de configuration
     */
    public function __construct($iniFile)
    {
        $this->_ini = parse_ini_file($iniFile, true);
    }

    /**
     * Renvois la valeur d'un parametre de configuration
     *
     * @param string      $key     Parametre à renvoyer
     * @param string|null $section Nom de la section dans laquelle chercher le parametre
     *
     * @return mixed
     */
    public function get($key, $section = null)
    {
        if ($section) {
            if (isset($this->_ini[$section][$key])) {
                return $this->_ini[$section][$key];
            }

        } else {
            if (isset($this->_ini[$key])) {
                return $this->_ini[$key];
            }
        }

        return null;
    }
}

