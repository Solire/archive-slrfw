<?php
/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw\Formulaire\Validate;

/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class IsString
{
    /**
     * Test si le parametre est une chaine
     *
     * @param mixed $data Valeur à tester
     *
     * @return boolean
     */
    public static function test($data)
    {
        if ((string) $data === $data) {
            return true;
        }
        return false;
    }
}
