<?php
/**
 * Module de gestion de formulaires
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

use Slrfw\Exception\Lib as Exception;
use Slrfw\Exception\Internal;
use \Slrfw\Formulaire\Champ;

/**
 * Contrôle des formulaires
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Formulaire
{
    /**
     * Nom de la section pour la configuration globale
     */
    const CONFIG = '__global';

    /**
     * Force le retour de run() sous forme d'une liste
     */
    const FORMAT_LIST = 1;

    /**
     * Ordre dans lesquels les tableaux sont mergés
     *
     * p pour $_POST
     * g pour $_GET
     * c pour $_COOKIE
     *
     * @var string
     */
    protected $defaultOrder = 'cgp';

    /**
     * Liste des plugins
     *
     * @var array
     */
    protected $plugins;

    /**
     * tableau des paramètres du formulaire et de leurs options.
     *
     * @var array
     */
    protected $_architecture;

    /**
     * valeur --config dans le fichier de configuration du formulaire
     *
     * @var array
     */
    protected $_config;

    /**
     * Données du formulaire
     *
     * @var array
     */
    protected $data;

    /**
     * toutes les données
     *
     * @var array
     */
    protected $_fullData;

    /**
     * Nom du champ en cours d'annalyse
     *
     * @var string
     */
    protected $target = '';

    /**
     * Liste des noms des champs du formulaire
     *
     * @var array
     */
    protected $inputNames = [];

    /**
     * Charge un nouveau formulaire
     *
     * Pour comprendre la configuration voici un exmple de .ini
     * ;; Configuration générale du formulaire
     * [__config]
     * ;; Option pour prendre en compte le préfixage de tous les champs du formulaire
     * ;; Chaque [nom] (ou designe) sera préfixé par cette chaine
     * prefix = C
     *
     * ;; chaine d'ordre d'utilisation des variables $_GET $_POST $_COOKIE
     * ;; définie l'ordre dans lequel ces tableaux sont passés dans la fonction merge
     * ;; exemple : gpc mettera les cookie prioritaires sur les posts qui seront
     * ;; prioritaires sur les get
     * ordre = p
     *
     * ;; Exception utilisée, faute de précision au niveau du champ pour ce formulaire.
     * exception = UserException
     *
     * ;; Fonction appellée lors d'une erreur
     * appelFonction = "CompteController::erreurInscription"
     *
     * ;; Les champs sont à parametrer de cette façon :
     * ;; Nom de la variable
     * [_exemple]
     * ;; Nom des tests (voir param.php pour les connaitre) dans une chaine
     * ;; séparés par |
     * test = ""
     *
     * ;; Variable obligatoire ou non, si elle est obligatoire, en cas d'erreur ou
     * ;; d'oublie une exceptions era envoyée, sinon elle sera simplement ignorée du
     * ;; tableau de retour
     * ;; Valeurs Possible : boolean
     * obligatoire = true
     *
     * ;; Message d'erreur si la variable n'est pas présente ou mal renseignée
     * ;; Valeurs Possible : string
     * erreur = "Message d'erreur à renseigner"
     *
     * ;; Nom dans le tableau de sortie de la variable
     * ;; ([nom] sera utilisé par défaut si rien n'est précisé)
     * ;; Valeurs Possible : string
     * renomme = "valeur de retour"
     *
     * ;; Nom dans le tableau d'entrée de la variable
     * ;; ([nom] sera utilisé par défaut si rien n'est précisé)
     * ;; Valeurs Possible : string
     * designe = "Nom du champ dans le formulaire"
     *
     * ;; Exception envoyée si le champ ne répond pas aux critères
     * ;; Valeurs Possible : string (Nom des objets exception)
     * exception = "Exception"
     *
     * ;; Si le champ est validé, il passe le ou les champs désignées en obligatoire
     * ;; Les autres champs doivent obligatoirement être après dans la liste
     * ;; de contrôle.
     * ;; Valeurs Possible : string (nom du ou des champs séparés par |)
     * force = "code"
     *
     * ;; Nom du champ dans le tableau de sortie (soit [nom] ou renomme) auquel le
     * ;; champ doit être égal.
     * ;; Valeurs Possible : string (nom du champs)
     * egal = "code"
     *
     * @param array|string $iniPath  Array contenant l'architecture ou le chemin du .ini
     * @param boolean      $complete Si le chemin est absolu
     *
     * @config main [dirs] "formulaire" Chemin du dossier des .ini d'architecture
     */
    public function __construct($config)
    {
        if (!is_object($config) || !in_array('Slrfw\ConfigInterface', class_implements($config))) {
            throw new Exception('Configuration non valide');
        }

        $this->config = $config;
    }

    /**
     * Renvois la configuration du formulaire
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Parcour l'architecture pour y trouver la configuration générale
     * et sortir le cas d'exemple
     *
     * @return self
     */
    protected function parseArchi()
    {
        if (isset($this->_architecture[\Slrfw\Config::KEY_CONF])) {
            $this->_config = $this->_architecture[\Slrfw\Config::KEY_CONF];
            unset($this->_architecture[\Slrfw\Config::KEY_CONF]);
        }

        if (isset($this->_config['ordre'])) {
            $this->_ordre = $this->_config['ordre'];
        }

        /** Récupération des plugin **/
        if (isset($this->_config['plugins'])) {
            $this->plugins = explode('|', $this->_config['plugins']);
        }

        /* = Suppression d'_exemple
        `------------------------------------------------- */
        if (isset($this->_architecture['_exemple'])) {
            unset($this->_architecture['_exemple']);
        }

        return $this;
    }

    /**
     * Supprime une option de l'architecture
     *
     * Utile si l'on veut se servir que partiellement d'un .ini par exemple
     *
     * @param string $name Nom du champ à oublier
     *
     * @return boolean Vrai si l'élément était présent
     */
    public function archiUnset($name)
    {
        if (!isset($this->_architecture[$name])) {
            return false;
        }

        unset($this->_architecture[$name]);

        return true;
    }

    /**
     * Edition de la configuration du formulaire
     *
     * @param array   $newConfig Tableau associatif de la nouvelle configuration
     * @param boolean $replace   Si vrais, la nouvelle configuration remplace l'ancienne,
     * sinon il y a un merge des deux tableaux
     *
     * @return void
     */
    public function alterConfig(array $newConfig, $replace = false)
    {
        if ($replace) {
            $this->_config = $newConfig;
        } else {
            $this->_config = array_merge($this->_config, $newConfig);
        }
    }

    /**
     *
     * @param string      $name  Nom du champ
     * @param array|Champ $rules Données du champ
     *
     * @return \Slrfw\Formulaire\Champ
     */
    protected function loadChamp($name, $rules)
    {
        if (is_object($rules) && $rules instanceof Champ) {
            return $rules;
        }

        $champ = new \Slrfw\Formulaire\Champ($name);

        if (!is_array($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $key => $value) {
            $champ->setRule($key, $value);
        }

        return $champ;
    }

    /**
     * Renvois le nom du champ à récupérer dans les variables http
     *
     * @param Champ $field Champ en cours de traitement
     *
     * @return string
     */
    protected function getFieldName(Champ $field)
    {
        $target = $field->getTargetName();

        if ($this->config->get(self::CONFIG, 'prefix') !== null) {
            $target = $this->config->get(self::CONFIG, 'prefix') . $target;
        }

        $this->inputNames[] = $target;

        return $target;
    }

    /**
     * Traite le formulaire pour en renvoyer les données vérifiées
     *
     * @return array tableau des données du formulaire
     *
     * @throws Exception\Lib  En cas d'erreurs dans la configuration du formulaire
     * @throws Exception\User Si le formulaire est mal remplis
     *
     * @uses Formulaire::catchData()
     * @uses Formulaire::get()
     */
    public function run()
    {
        $this->fullData = $this->catchData();
        $configuration = $this->config->getAll();

        unset($configuration[self::CONFIG]);

        /* = On utilise cette formulation plutot que foreach parce que
         * $configuration peut évoluer dans la boucle. (et que dans un foreach
         * cela n'est pas pris en compte)
          ------------------------------- */
        reset($configuration);
        while (list($name, $regles) = each($configuration)) {
            $champ = $this->loadChamp($name, $regles);

            try {
                $temp = $this->extractFromHttpVars($champ);
            } catch (Internal $exc) {
                continue;
            }

            $controls = $champ->getTests();

            /* = Si la variable ne passe pas les testes
            | on retourne un message d'erreur si celle-ci est
            | obligatoire, sinon, on l'ignore simplement.
            `---------------------------------------- */
            if (!$temp->tests($controls)) {
                if ($champ->isRequired() === true) {
                    $this->throwError($champ);
                }

                continue;
            }

            $name = $champ->getFinalName();
            $this->data[$name] = $temp->get();
            unset($temp);

//            /* = Passage en obligatoire des champs liés
//              ------------------------------- */
//            if (isset($regles['force'])) {
//                $champs = explode('|', $regles['force']);
//                foreach ($champs as $champ) {
//                    $configuration[$champ]['obligatoire'] = true;
//                }
//                unset($champs, $champ);
//            }
//
//            /* = Contrôle d'egalité du champ
//              ------------------------------- */
//            if (isset($regles['egal'])) {
//                if ($this->_data[$name] != $this->_data[$regles['egal']]) {
//                    $this->throwError($regles);
//                }
//            }
        }

//        if (!empty($this->plugins)) {
//            foreach ($this->plugins as $plugin) {
//                if (in_array('Slrfw\Formulaire\PluginInterface', class_implements($plugin))) {
//                    $plugin::form($this->_data);
//                } else {
//                    $this->throwError(array('erreur' => 'plugin incompatible'));
//                }
//            }
//        }

        $options = func_get_args();
        if (!empty($options)) {
            if ($options[0] == self::FORMAT_LIST) {
                return $this->getList();
            }
        }

        return $this->data;
    }

    /**
     * Renvois les données collectées par le formulaire sous la forme d'un tableau
     *
     * @return array Tableau non associatif des valeurs
     */
    public function getList()
    {
        $list = array();
        foreach ($this->data as $value) {
            $list[] = $value;
        }

        return $list;
    }

    /**
     * Envois l'exception de l'erreur
     *
     * Le type d'exception envoyé peut être paramétré à deux endroits, (voir le
     * fichier de configuration) au niveau du champ, ou au niveau du formulaire.
     * <br/>Par défaut une {@link Exception\User} est envoyée.
     *
     * @param array $regles Tableau associatif de règles pour la gestion d'erreurs
     *
     * @return void
     * @throws mixed
     * @throws Exception\User Si il y a une erreur dans le formulaire
     *
     * @todo faire un tutorial expliquant le paramétrage des champs d'un formulaire
     */
    protected function throwError(Champ $field)
    {
        $message = $field->getErrorMessage();

        if ($field->hasPersonalException() === true) {
            // Exception personnalisée au niveau du champ
            $className = $field->getPersonalException();
            $error = new $className($message);
        } elseif ($this->config->get(self::CONFIG, 'exception') !== null) {
            // Exception personnalisée au niveau du formulaire
            $className = $this->config->get(self::CONFIG, 'exception');
            $error = new $className($message);
        } else {
            $className = 'Exception';
            $error = new Exception($message);
        }

        if (method_exists($error, 'setErrorInputName')) {
            $error->setErrorInputName($this->target);
        }

//        if (isset($this->_config['appelFonction'])) {
//            if (is_callable($this->_config['appelFonction'])) {
//                $error = call_user_func(
//                    $this->_config['appelFonction'], $this, $error
//                );
//            }
//        }
        throw $error;
    }

    /**
     * Récupère les données GET POST COOKIE
     *
     * @return array
     */
    protected function catchData()
    {
        $datas = [
            'g' => $_GET,
            'p' => $_POST,
            'c' => $_COOKIE,
        ];

        $order = $this->defaultOrder;
        if ($this->config->get(self::CONFIG, 'ordre') !== null) {
            $order = $this->config->get(self::CONFIG, 'ordre');
        }

        $result = [];
        for ($i = 0; $i < strlen($order); $i++) {
            $httpVar = $order[$i];
            if (isset($datas[$httpVar]) && !empty($datas[$httpVar])) {
                $result = array_merge($result, $datas[$httpVar]);
            }
        }
        return $result;
    }

    /**
     * Renvois la liste des champs input du formulaire
     *
     * @return array
     */
    public function getInputNamesList()
    {
        return $this->inputNames;
    }

    /**
     * Renvois le paramètre du nom $key sous la forme d'un objet Param
     *
     * @param string $key Nom du paramètre
     *
     * @return Param|null
     */
    protected function extractFromHttpVars(Champ $field)
    {
        $key = $this->getFieldName($field);

        if (isset($this->fullData[$key])) {
            return new Param($this->fullData[$key]);
        }

        $this->markError($field);
    }

    protected function markError(Champ $field)
    {
        if ($field->isRequired() === true) {
            $this->throwError($field);
        }

        throw new Internal('Ignore field');
    }

    /**
     * Renvois les données collectées par le formulaire sous la forme
     * d'un tableau associatif
     *
     * @return array
     */
    public function getArray()
    {
        return $this->data;
    }


    /**
     * __get() est sollicitée pour lire des données depuis des propriétés inaccessibles
     *
     * Cette focntion permet d'appeller les variables du formulaire directement par $obj->var
     *
     * @param string $name Nom de la variable
     *
     * @return null
     * @ignore
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * __isset() est sollicitée pour tester des données depuis des propriétés inaccessibles
     *
     * Cette fonction permet de tester (isset()) les variables
     *
     * @param string $name Nom de la variable
     *
     * @return boolean
     * @ignore
     */
    public function __isset($name)
    {
        if (isset($this->data[$name])) {
            return true;
        }

        return false;
    }
}

