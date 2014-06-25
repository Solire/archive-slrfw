<?php
/**
 * Gestionnaire des fichiers de configurations
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw\Formulaire;

use Slrfw\ConfigInterface;
use Slrfw\Exception\Lib as Exception;

/**
 * Gestionnaire des fichiers de configurations
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class FastConfig extends \Slrfw\FastConfig implements ConfigInterface
{
    /**
     * Configuration par défaut d'une valeur
     *
     * @var array
     */
    private $default = [
        'test' => '',
        'obligatoire' => false,
    ];

    private $rules = [
        'test', 'obligatoire', 'erreur', 'renomme', 'designe', 'exception',
        'force', 'egale'
    ];

    /**
     * Ajoute un champ dans la configuration du formulaire
     *
     * @param string $varName Nom du champs à ajouter
     *
     * @return self
     */
    public function create($varName)
    {
        $this->varName = (string) $varName;
        $this->config[(string) $varName] = $this->default;

        return $this;
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

        $this->config[$this->varName][$ruleName] = $value;

        return $this;
    }
}
