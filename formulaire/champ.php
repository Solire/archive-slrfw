<?php
/**
 * Module de gestion de formulaires
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw\Formulaire;

use Slrfw\Exception\Lib as Exception;

/**
 * Contrôle des formulaires
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Champ
{
    /**
     * @var array Règles acceptées par Formulaire
     */
    private $rules = [
        'test', 'obligatoire', 'erreur', 'renomme', 'designe', 'exception',
        'force', 'egale'
    ];

    /**
     * @var string Nom du champ
     */
    protected $name;

    /**
     * @var array Configuration du champ
     */
    protected $config = [];

    /**
     * Règles pour un champ du formulaire
     *
     * @param string $name Nom du champ
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Ajoute une règle au dernier champ créé
     *
     * @param string $ruleName Nom de la règle à ajouter
     * @param mixed  $value    Valeur de l'option
     *
     * @return self
     * @throws Exception si la règle demandée n'existe pas
     */
    public function setRule($ruleName, $value)
    {
        if (!in_array($ruleName, $this->rules)) {
            throw new Exception($ruleName . ' n\'est pas une règle formulaire');
        }

        $this->config[$ruleName] = $value;

        return $this;
    }

    /**
     * Supprime une règle
     *
     * @param string $ruleName Nom de la règle à supprimer
     *
     * @return self
     */
    public function rmRule($ruleName)
    {
        unset($this->config[$ruleName]);

        return $this;
    }

    /**
     * Renvois le nom de l'attribut GET / POST ou COOKIE du champ
     *
     * @return string
     */
    public function getTargetName()
    {
        if (isset($this->config['designe'])) {
            return $this->config['designe'];
        }

        return $this->name;
    }

    /**
     * Renvois le nom de l'attribut de sortie
     *
     * @return string
     */
    public function getFinalName()
    {
        if (isset($this->config['renomme'])) {
            return $this->config['renomme'];
        }

        return $this->name;
    }

    /**
     * Renvois le message d'erreur à afficher si le champ n'est pas correcte
     *
     * @return string
     */
    public function getErrorMessage()
    {
        if (isset($this->config['erreur'])) {
            return $this->config['erreur'];
        }

        return $this->name;
    }

    /**
     * Indique si le champ possède une personnalisation au niveau de l'exception
     *
     * @return boolean
     */
    public function hasPersonalException()
    {
        if (!isset($this->config['exception'])) {
            return false;
        }

        if (empty($this->config['exception'])) {
            return false;
        }

        return true;
    }

    /**
     * Renvois le nom de la classe à utiliser comme exception
     *
     * @return string
     * @throws Exception si aucune exception n'est configurée
     */
    public function getPersonalException()
    {
        if (!isset($this->config['exception']) || empty($this->config['exception'])) {
            throw new Exception('Aucune class exception de configurée pour ce champ');
        }

        return $this->config['exception'];
    }

    /**
     * Indique si le champ est requis
     *
     * @return boolean
     */
    public function isRequired()
    {
        if (isset($this->config['obligatoire'])) {
            return (boolean) $this->config['obligatoire'];
        }

        return false;
    }

    /**
     * Renvois les noms des testes à effectuer sur le champ
     *
     * @return array
     */
    public function getTests()
    {
        if (!isset($this->config['test'])) {
            return [];
        }

        if (is_array($this->config['test'])) {
            return $this->config['test'];
        }

        return explode('|', $this->config['test']);
    }
}
