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
class IsBoolean
{
    /**
     * Test si le parametre est un boolean
     *
     * @param mixed $data Valeur à tester
     *
     * @return boolean
     */
    public static function test($data)
    {
        if ($data == 0 || $data == 1) {
            return true;
        }
        return false;
    }
}
