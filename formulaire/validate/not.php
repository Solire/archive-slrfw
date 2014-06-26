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
class Not
{
    /**
     * Test si le parametre n'est pas vide.
     *
     * @param mixed $data  Valeur à tester
     * @param mixed $param Valeur de blocage
     *
     * @return boolean
     */
    public static function test($data, $param)
    {
        if ($data == $param) {
            return false;
        }

        return true;
    }
}
