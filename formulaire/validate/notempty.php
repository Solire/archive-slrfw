<?php
/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw\Formulaire\Validate;

use Slrfw\Formulaire\ParamInterface;

/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class NotEmpty implements ParamInterface
{
    /**
     * Test si le parametre n'est pas vide.
     *
     * @param mixed $data  Valeur à tester
     * @param mixed $param Non utilisé
     *
     * @return boolean
     */
    public static function validate($data, $param)
    {
        if (empty($data)) {
            return false;
        }
        return true;
    }
}
