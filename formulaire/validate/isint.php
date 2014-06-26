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
class IsInt
{
    /**
     * Test si le parametre est un entier
     *
     * @param mixed $data Valeur à tester
     *
     * @return boolean
     */
    public static function test($data)
    {
        if ((string)((int) $data) == (string) $data) {
            return true;
        }

        return false;
    }
}
