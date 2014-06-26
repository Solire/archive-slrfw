<?php
/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

use Slrfw\Exception\Lib as Exception;

/**
 * Contrôle de variables
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Param
{
    /**
     * Variable
     *
     * @var mixed
     */
    private $foo = null;

    /**
     * Charge une nouvelle variable
     *
     * @param mixed $param Valeur de la variable à tester
     */
    public function __construct($param = null)
    {
        $this->foo = $param;
    }

    /**
     * Retourne la valeur du paramètre.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->foo;
    }

    /**
     * Renvois le nom de la classe de test
     *
     * @param string $name Nom du test
     *
     * @return string
     */
    protected function getClassName($name)
    {
        if (strpos('\\', $name) !== false) {
            return $name;
        }

        return 'Slrfw\Param\\' . ucfirst($name);
    }

    /**
     * Permet d'effectuer differents tests sur la variable
     *
     * @param array $options Tableau de tests à effectuer
     *
     * @return boolean
     */
    public function tests($options)
    {
        if (!is_array($options) || empty ($options)) {
            throw new Exception('$options doit être un tableau');
        }

        foreach ($options as $option) {
            $param = null;
            if (strpos($option, ':') !== false) {
                $foo = explode(':', $option);
                $option = $foo[0];
                $param = $foo[1];
                unset($foo);
            }

            $className = $this->getClassName($option);

            if ($className::test($this->foo, $param) !== true) {
                return false;
            }
        }

        return true;
    }
}
