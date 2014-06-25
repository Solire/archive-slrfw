<?php
/**
 * Gestionnaire des fichiers de configurations
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Gestionnaire des fichiers de configurations
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class FastConfig implements ConfigInterface
{
    /**
     * Contenu du fichier de config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Renvois le contenu du fichier de configuration
     *
     * @return array Tableau de la configuration
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Renvois la valeur d'un parametre de configuration
     *
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return mixed null si aucune configuration ne répond aux critères
     */
    public function get($section, $key = null)
    {
        if (!empty($key)) {
            if (isset($this->config[$section][$key])) {
                return $this->config[$section][$key];
            }

        } else {
            if (isset($this->config[$section])) {
                return $this->config[$section];
            }
        }

        return null;
    }

    /**
     * Enregistre la valeur
     *
     * @param mixed  $value   Valeur à mettre dans la configuration
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return self
     */
    public function set($value, $section, $key = null)
    {
        if (!empty($key)) {
            $this->config[$section][$key] = $value;
            return $this;
        } else {
            $this->config[$section] = $value;
        }

        return $this;
    }

    /**
     * Supprime un parametre de configuration
     *
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return self
     */
    public function kill($section, $key = null)
    {
        if (!empty($key)) {
            if (isset($this->config[$section][$key])) {
                unset($this->config[$section][$key]);
            }

        } else {
            if (isset($this->config[$section])) {
                unset($this->config[$section]);
            }
        }
        return $this;
    }
}