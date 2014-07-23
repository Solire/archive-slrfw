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
    protected function getClassName($name, $tool)
    {
        if (strpos('\\', $name) !== false) {
            return $name;
        }

        return 'Slrfw\Formulaire\\' . $tool . '\\' . ucfirst($name);
    }

    protected function validatePlugin($className)
    {
        if (!in_array('Slrfw\Formulaire\ParamInterface', class_implements($config))) {
            throw new Exception('_' . $className . '_ n\'implemente pas Slrfw\Formulaire\ParamInterface');
        }

        return $className;
    }

    /**
     * Extrait les options du nom du traitement
     *
     * @param string $name Nom du traitement
     *
     * @return string[]
     */
    protected function extractOptions($name)
    {
        if (strpos($name, ':') === false) {
            return [$name, null];
        }
        $foo = explode(':', $name);
        return $foo;
    }

    /**
     * Execute un test sur la variable
     *
     * @param string $option Nom du test à effectuer
     *
     * @return boolean
     */
    public function validate($option)
    {
        list($option, $param) = $this->extractOptions($option);
        $className = $this->getClassName($option, 'Validate');
        return $className::validate($this->foo, $param);
    }

    /**
     * Execute un traitement sur la variable
     *
     * @param string $option Nom du traitement à effectuer
     *
     * @return boolean
     */
    public function sanitize($option)
    {
        list($option, $param) = $this->extractOptions($option);
        $className = $this->getClassName($option, 'Sanitize');
        return $className::validate($this->foo, $param);
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
            if ($this->validate($option) === true) {
                continue;
            }
            return false;
        }

        return true;
    }
}
