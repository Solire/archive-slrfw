<?php
/**
 * Module de gestion de formulaires
 *
 * @package    Library
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw;

/**
 * Contrôle des formulaires
 *
 * @package    Library
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
class Formulaire
{
    /**
     * Force le retour de run() sous forme d'une liste
     */
    const FORMAT_LIST = 2;

    /**
     * Ordre dans lesquels les tableaux sont mergés
     *
     * p pour $_POST
     * g pour $_GET
     * c pour $_COOKIE
     *
     * @var string
     */
    private $_ordre = 'cgp';


    /**
     * tableau des paramètres du formulaire et de leurs options.
     *
     * @var array
     */
    private $_architecture;

    /**
     * valeur --config dans le fichier de configuration du formulaire
     *
     * @var array
     */
    private $_config;

    /**
     * Données du formulaire
     *
     * @var array
     */
    private $_data;

    /**
     * toutes les données
     *
     * @var array
     */
    private $_fullData;



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
     * @param array|string $iniPath Array contenant l'architecture ou le chemin du .ini
     *
     * @config main [dirs] "formulaire" Chemin du dossier des .ini d'architecture
     */
    public function __construct($iniPath)
    {
        $config = Registry::get('mainconfig');
        if (!is_array($iniPath)) {
            $iniPath = $config->get('dirs', 'formulaire') . $iniPath;
            $iniPath = new Path($iniPath);
            $this->_architecture = parse_ini_file($iniPath->get(), true);
        } else {
            $this->_architecture = $iniPath;
        }

        $this->parseArchi();
    }

    /**
     * Parcour l'architecture pour y trouver la configuration générale
     * et sortir le cas d'exemple
     *
     * @return boolean
     */
    private function parseArchi()
    {
        if (isset($this->_architecture['__config'])) {
            $this->_config = $this->_architecture['__config'];
            unset($this->_architecture['__config']);

            if (isset($this->_config['ordre'])) {
                $this->_ordre = $this->_config['ordre'];
            }
        }

        /* = Suppression d'_exemple
          `------------------------------------------------- */
        if (isset($this->_architecture['_exemple'])) {
            unset($this->_architecture['_exemple']);
        }

        return true;
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
        $this->_fullData = $this->catchData();
        $configuration = $this->_architecture;

        /* = On utilise cette formulation plutot que foreach parce que
         * $configuration peut évoluer dans la boucle. (et que dans un foreach
         * cela n'est pas pris en compte)
          ------------------------------- */
        while (list($name, $regles) = each($configuration)) {
            /* = Gestion des prefix dans le formulaire
            `------------------------------------------- */
            $target = $name;
            if (isset($regles['designe'])) {
                $target = $regles['designe'];
            }


            if (isset($this->_config['prefix'])) {
                $target = $this->_config['prefix'] . $target;
            }

            $temp = $this->get($target);

            /* = Si la variable n'est pas présente
            `------------------------------------ */
            if ($temp == null) {
                if ($regles['obligatoire'] == true) {
                    $this->throwError($regles);
                }

                continue;
            }

            $options = explode('|', $regles['test']);

            /* = Test si le fichier de configuration est au bon format
            `--------------------------------------------------------- */
            if (!is_array($options)) {
                throw new Exception\Lib("Config : Opt n'est pas un tableau");
            }

            /* = Si la variable ne passe pas les testes
            | on retourne un message d'erreur si celle-ci est
            | obligatoire, sinon, on l'ignore simplement.
            `---------------------------------------- */
            if (!$temp->tests($options)) {
                if ($regles['obligatoire'] == true) {
                    $this->throwError($regles);
                }

                continue;
            }

            if (isset($regles['renomme'])) {
                $name = $regles['renomme'];
            }

            $this->_data[$name] = $temp->get();
            unset($temp);

            /* = Passage en obligatoire des champs liés
              ------------------------------- */
            if (isset($regles['force'])) {
                $champs = explode('|', $regles['force']);
                foreach ($champs as $champ) {
                    $configuration[$champ]['obligatoire'] = true;
                }
                unset($champs, $champ);
            }

            /* = Contrôle d'egalité du champ
              ------------------------------- */
            if (isset($regles['egal'])) {
                if ($this->_data[$name] != $this->_data[$regles['egal']]) {
                    $this->throwError($regles);
                }
            }
        }

        $options = func_get_args();
        if (!empty($options)) {
            if ($options[0] == self::FORMAT_LIST) {
                return $this->getList();
            }
        }

        return $this->_data;
    }

    /**
     * Renvois les données collectées par le formulaire sous la forme d'un tableau
     *
     * @return array Tableau non associatif des valeurs
     */
    public function getList()
    {
        $list = array();
        foreach ($this->_data as $value) {
            $list[] = $value;
        }

        return $list;
    }

    /**
     * Génère une requête SQL pour que le contenu du formulaire puisse être inséré en base
     *
     * la table est à préciser pendant l'appel de la fonction ou dans le fichier
     * de configuration
     *
     * @param \PDO   $db    Connection à la bdd
     * @param string $table Nom de la table dans lequel faire l'insertion
     *
     * @return string
     *
     * @deprecated
     */
    public function makeQuery(\PDO $db, $table = null)
    {
        if (empty($table) && isset($this->_config['table'])) {
            $table = $this->_config['table'];
        }
        $query = 'DESC ' . $table;
        $archi = $db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        $values = array();
        foreach ($archi as $col) {
            if (isset($this->_data[$col])) {
                $values[] = $col . ' = ' . $db->quote($this->_data[$col]);
            }
        }

        $query = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $values);

        return $query;
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
    private function throwError($regles)
    {
        $error = null;

        if (!isset($regles['erreur'])) {
            $regles['erreur'] = '';
        }

        if (isset($regles['exception'])) {
            /* = Exception personnalisée au niveau du champ
            ------------------------------- */
            $error = new $regles['exception']($regles['erreur']);
        } elseif (isset($this->_config['exception'])) {
            /* = Exception personnalisée au niveau du formulaire
            ------------------------------- */
            $error = new $this->_config['exception']($regles['erreur']);
        } else {
            $error = new Exception\User($regles['erreur']);

            /* = Par défaut on redirige vers la page précédente
              ------------------------------- */
            if (isset($_SERVER['HTTP_REFERER'])) {
                $error->link($_SERVER['HTTP_REFERER'], 1);
            }
        }


        if (isset($this->_config['appelFonction'])) {
            if (is_callable($this->_config['appelFonction'])) {
                $error = call_user_func(
                    $this->_config['appelFonction'], $this, $error
                );
            }
        }
        throw $error;
    }

    /**
     * Récupère les données GET POST COOKIE
     *
     * @return array
     * @uses Formulaire::$_ordre
     */
    private function catchData()
    {
        $datas = array(
            'g' => $_GET,
            'p' => $_POST,
            'c' => $_COOKIE,
        );

        $result = array();
        for ($i = 0; $i < strlen($this->_ordre); $i++) {
            $lettre = $this->_ordre[$i];
            if (isset($datas[$lettre]) && !empty($datas[$lettre])) {
                $result = array_merge($result, $datas[$lettre]);
            }
        }
        return $result;
    }

    /**
     * Renvois le paramètre du nom $key sous la forme d'un objet Param
     *
     * @param string $key Nom du paramètre
     *
     * @return Param|null
     */
    protected function get($key)
    {
        if (isset($this->_fullData[$key])) {
            return new Param($this->_fullData[$key]);
        } else {
            return null;
        }
    }


    /**
     * Renvois les données collectées par le formulaire sous la forme
     * d'un tableau associatif
     *
     * @return array
     */
    public function getArray()
    {
        return $this->_data;
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
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
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
        if (isset($this->_data[$name])) {
            return true;
        }

        return false;
    }
}

