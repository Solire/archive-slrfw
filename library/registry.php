<?php
/**
 * Registre
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

/**
 * Registre
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Registry
{
    /**
     * Contenu du registre
     *
     * @var array
     */
    private static $_maps;

    /**
     * Instancie le registre (jamais utilisé)
     *
     * @ignore
     */
    private function __construct()
    {
    }

    /**
     * Enregistre une variable dans le registre
     *
     * @param string $key   Nom/Code de l'élement à stocker
     * @param mixed  $value Valeur de l'élément à stocker
     *
     * @return void
     */
    public static function set($key, $value)
    {
        self::$_maps[$key] = $value;
    }

    /**
     * Récupère une valeur du registre
     *
     * @param string $key Nom/Code de l'élement stocké
     *
     * @return mixed
     */
    public static function get($key)
    {
        if (isset(self::$_maps[$key])) {
            return self::$_maps[$key];
        }

        return null;
    }
}

