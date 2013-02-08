<?php
/**
 * Contrôle de variables
 *
 * @package    Library
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw\Library;

/** @todo faire la présentation du code */

/**
 * Contrôle de variables
 *
 * @package    Library
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Param
{
    /**
     * Variable
     *
     * @var mixed
     */
    private $_foo = null;

    /**
     * Charge une nouvelle variable
     *
     * @param mixed $param Valeur de la variable à tester
     */
    public function __construct($param = null)
    {
        $this->_foo = $param;
    }

    /**
     * Retourne la valeur du paramètre.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->_foo;
    }

    /**
     * Envois d'une erreur
     *
     * @param string $message Message d'erreur
     *
     * @return void
     * @throws Exception\Lib
     */
    private function error($message)
    {
        throw new Exception\Lib($message);
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
            return $this->error('$options doit être un tableau');
        }

        foreach ($options as $option) {
            $param = null;
            if (strpos($option, ':')) {
                $foo = explode(':', $option);
                $option = $foo[0];
                $param = $foo[1];
                unset($foo);
            }
            $method = 'test' . ucwords($option);
            if (!method_exists('Param', $method)) {
                return $this->error('erreur : ' . $method . ' n\'existe pas');
            }

            if (!$this->$method($param)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Test si le parametre n'est pas vide.
     *
     * @return boolean
     */
    public function testNotEmpty()
    {
        if (empty($this->_foo)) {
            return false;
        }
        return true;
    }

    /**
     * Test si le parametre est un entier
     *
     * @return boolean
     */
    public function testIsInt()
    {
        if ((string)((int)$this->_foo) == (string)$this->_foo) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est un boolean
     *
     * @return boolean
     */
    public function testIsBoolean()
    {
        if ($this->_foo == 0 || $this->_foo == 1) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est positif
     *
     * @return boolean
     */
    public function testIsPositive()
    {
        if ($this->_foo > 0) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est un float
     *
     * @return boolean
     */
    public function testIsFloat()
    {
        if ((string)((float)$this->_foo) == (string)$this->_foo) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est un mail
     *
     * @return boolean
     */
    public function testIsMail()
    {
        $mask = '#^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z0-9]{2,5}$#i';
        if (preg_match($mask, $this->_foo)) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est un tableau
     *
     * @return boolean
     */
    public function testIsArray()
    {
        if (is_array($this->_foo)) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est une chaine
     *
     * @return boolean
     */
    public function testIsString()
    {
        if ((string)$this->_foo === $this->_foo) {
            return true;
        }
        return false;
    }

    /**
     * Test si le parametre est un numéro de téléphone
     *
     * @return boolean
     */
    public function testIsPhone()
    {
        if (preg_match('#^0[1-9]([-. ]?[0-9]{2}){4}$#', $this->_foo)) {
            return true;
        }
        return false;
    }


    /**
     * Test la longueur en nombre de charactères d'une chaine
     *
     * @param int $length Chaine au formatage spéciale, voir .ini
     *
     * @return boolean
     */
    public function testLength($length)
    {
        $sign = preg_replace('#([0-9]+)#', '', $length);
        $length = str_replace($sign, '', $length);

        switch ($sign) {
            case '=':
                if (strlen($this->_foo) == $length) {
                    return true;
                }
                break;
            case '>=':
                if (strlen($this->_foo) >= $length) {
                    return true;
                }
                break;
            case '<=':
                if (strlen($this->_foo) <= $length) {
                    return true;
                }
                break;
            case '>':
                if (strlen($this->_foo) > $length) {
                    return true;
                }
                break;
            case '<':
                if (strlen($this->_foo) < $length) {
                    return true;
                }
                break;

        }

        return false;
    }


    /**
     * Test si le parametre ne contient que des chiffres
     *
     * @return boolean
     */
    public function testOnlyNumber()
    {
        $char = preg_replace('#([0-9]+)#', '', $this->_foo);
        if (!empty($char)) {
            return false;
        }

        return true;
    }
}

