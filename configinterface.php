<?php
/**
 * Interface des classes de configuration
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Interface des classes de configuration
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
interface ConfigInterface
{
     /**
     * Renvois l'intégralité de la configuration
     *
     * @return array Tableau de la configuration
     */
    public function getAll();

    /**
     * Renvois la valeur d'un parametre de configuration
     *
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return mixed null si aucune configuration ne répond aux critères
     */
    public function get($section, $key = null);

    /**
     * Enregistre la valeur
     *
     * @param mixed  $value   Valeur à mettre dans la configuration
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return self
     */
    public function set($value, $section, $key = null);

    /**
     * Supprime un parametre de configuration
     *
     * @param string $section Code de la section
     * @param string $key     Nom de la clé de configuration
     *
     * @return self
     */
    public function kill($section, $key = null);
}
