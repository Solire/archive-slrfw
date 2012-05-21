<?php
/**
 * @package Library
 */

require_once 'param.php';

/**
 *
 * @package Library
 */
class Formulaire
{
    /**
     * Force le retour de run() sous forme d'une liste
     */
    const FORMAT_LIST = '2';
    /**
     * Ordre dans lesquels les tableaux sont mergés
     * p pour $_POST
     * g pour $_GET
     * c pour $_COOKIE
     * @var string
     */
    private $_ordre = 'cgp';


    /**
     * tableau des paramètres du formulaire et de leurs options.
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
     * @var array
     */
	private $_data;

    /**
     * toutes les données
     * @var array
     */
	private $_fullData;


    /**
     * @todo mettre en place differents moyens d'initialiser une architecture
     */
	public function  __construct($iniPath)
	{
        $config = Registry::get('mainconfig');
        if (!is_array($iniPath)) {
            $iniPath = $config->get('formulaire', 'dirs') . $iniPath;
            $iniPath = new Path($iniPath);
            $this->_architecture = parse_ini_file($iniPath->get(), true);
        } else {
            $this->_architecture = $iniPath;
        }
	}

    /**
     * Parcour l'architecture pour y trouver la configuration générale
     * et sortir le cas d'exemple
     */
    private function _parseArchi()
    {
        if (isset($this->_architecture['__config'])) {
            $this->_config = $this->_architecture['__config'];
            unset($this->_architecture['__config']);

            if (isset($this->_config['ordre']))
                $this->_ordre = $this->_config['ordre'];
        }

        /* = _exemple est un
          `------------------------------------------------- */
        if (isset($this->_architecture['_exemple']))
            unset($this->_architecture['_exemple']);
    }

    /**
     * Traite le formulaire pour en renvoyer les données vérifiées
     *
     * @return array tableau des données du formulaire
     * @throws LibException En cas d'erreurs dans la configuration du formulaire
     * @throws UserException Si le formulaire est mal remplis
     * @uses Formulaire::_parseArchi()
     * @uses Formulaire::_catchData()
     * @uses Formulaire::get()
     */
    public function run()
    {
        $this->_parseArchi();
        $this->_fullData = $this->_catchData();

		foreach ($this->_architecture as $name => $regles) {
            /* = Gestion des prefix dans le formulaire
            `------------------------------------------- */
            $target = $name;
            if (isset($regles['designe']))
                $target = $regles['designe'];


            if (isset($this->_config['prefix']))
                $target = $this->_config['prefix'] . $target;

            $temp = $this->get($target);

            /* = Si la variable n'est pas présente
            `------------------------------------ */
            if ($temp == null) {
                if ($regles['obligatoire'] == true)
                    $this->_throwError($regles);

                continue;
            }

            $options = explode('|', $regles['test']);

            /* = Test si le fichier de configuration est au bon format
            `--------------------------------------------------------- */
			if (!is_array($options)) {
                throw new LibException("Config : Opt n'est pas un tableau");
            }

            /* = Si la variable ne passe pas les testes
            | on retourne un message d'erreur si celle-ci est
            | obligatoire, sinon, on l'ignore simplement.
            `---------------------------------------- */
			if (!$temp->tests($options)) {
                if ($regles['obligatoire'] == true)
                    $this->_throwError($regles);

                continue;
            }

            if (isset($regles['renomme'])) {
                $name = $regles['renomme'];
            }

            $this->_data[$name] = $temp->get();
            unset($temp);
		}
          
        if (func_num_args() >= 1 && func_get_arg(0)) {
            if (func_get_arg(0) == self::FORMAT_LIST) {
                return $this->getList();
            }
        }

        return $this->_data;
    }

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
     * @param PDO $db
     * @param string $table
     * @return string
     */
    public function makeQuery(PDO $db, $table = null)
    {
        if (empty($table) && isset($this->_config['table']))
            $table = $this->_config['table'];
        $query = 'DESC ' . $table;
        $archi = $db->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);

        $values = array();
        foreach ($archi as $col) {
            if (isset($this->_data[$col]))
                $values[] = $col . ' = ' . $db->quote($this->_data[$col]);
        }

        $query = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $values);

        return $query;
    }

    /**
     * Envois l'exception de l'erreur
     *
     * Le type d'exception envoyé peut être paramétré à deux endroits, (voir le
     * fichier de configuration) au niveau du champ, ou au niveau du formulaire.
     * <br/>Par défaut une {@link UserException} est envoyée.
     *
     * @todo faire un tutorial expliquant le paramétrage des champs d'un formulaire
     * @param array $regles
     * @throws mixed
     * @throws UserException
     */
    private function _throwError($regles)
    {
        /* = Exception personnalisée au niveau du champ
          ------------------------------- */
        if (isset($regles['exception'])) {
            throw new $regles['exception']($regles['erreur']);
        }

        /* = Exception personnalisée au niveau du formulaire
          ------------------------------- */
        if (isset($this->_config['exception'])) {
            throw new $this->_config['exception']($regles['erreur']);
        }

        throw new UserException($regles['erreur']);
    }

    /**
     * Récupère les données GET POST COOKIE
     * @uses Formulaire::$_ordre
     * @return array
     */
    private function _catchData()
    {
        $datas = array(
            'g' => $_GET,
            'p' => $_POST,
            'c' => $_COOKIE,
        );

        $result = array();
        for ($i = 0; $i < strlen($this->_ordre); $i++) {
            $lettre = $this->_ordre[$i];
            if (isset($datas[$lettre]) && !empty($datas[$lettre]))
                $result = array_merge($result, $datas[$lettre]);
        }
        return $result;
    }

	/**
	 * Renvois le paramètre du nom $Key
	 * sous la forme d'un objet Param
	 *
	 * @param string $Key
	 * @return Param|null
	 */
	protected function get($Key)
	{
        if (isset($this->_fullData[$Key]))
            return new Param($this->_fullData[$Key]);
        else
            return null;
	}


    /**
     * Renvois les données collectées par le formulaire
     * @return array
     */
	public function getArray()
	{
		return $this->_data;
	}
}
