<?php
/**
 * Model des gabarits
 *
 * @package Model
 * @author  thansen <thansen@solire.fr>
 * @license Solire http://www.solire.fr/
 */

namespace Slrfw\Model;

/**
 * Description of gabaritmanager
 *
 * @package Model
 * @author  thansen <thansen@solire.fr>
 * @license Solire http://www.solire.fr/
 */
class gabaritManager extends manager
{
    /**
     * Tableau de mise en cache des versions.
     *
     * @var array
     */
    protected $_versions = array();

    /**
     * Tableau de mise en cache des gabarits.
     *
     * @var array
     */
    protected $_gabarits = array();

    /**
     * Tableau des identifiants des versions (utilisé lors de l'enregistrement
     * des pages.)
     *
     * @var int[]
     */
    protected $_versionsIds = array();

    /**
     *
     * @var bool
     */
    protected $modePrevisualisation = false;

    /**
     * Nom de la classe gabaritPage utilisé par défaut
     */
    const DEFAULT_GABARIT_CLASS = '\Slrfw\Model\GabaritPage';

    /**
     * Nom de la classe gabaritBloc utilisé par défaut
     */
    const DEFAULT_GABARIT_BLOC_CLASS = '\Slrfw\Model\GabaritBloc';

    /**
     * Nom de la classe à utiliser pour charger les gabarits page
     *
     * @var string
     */
    private $gabaritClassName = self::DEFAULT_GABARIT_CLASS;

    /**
     * Nom de la classe à utiliser pour charger les gabarits bloc
     *
     * @var string
     */
    private $gabaritBlocClassName = self::DEFAULT_GABARIT_BLOC_CLASS;

    /**
     * Donne l'identifiant d'une page d'après son rewriting et l'identifiant.
     *
     * @param int    $id_version identifiant de la version
     * @param int    $id_api     identifiant de l'api
     * @param string $rewriting  rewriting de la page
     * @param int    $id_parent  identifiant de la page parente si elle existe 0 sinon
     *
     * @return int
     */
    public function getIdByRewriting($id_version, $id_api, $rewriting, $id_parent = 0)
    {
        $query  = 'SELECT `id` FROM `gab_page`'
                . ' WHERE `suppr` = 0 AND `id_parent` = ' . $id_parent
                . ' AND `id_version` = ' . $id_version
                . ' AND `id_api` = ' . $id_api
                . ' AND `rewriting` = ' . $this->_db->quote($rewriting);

        if (!$this->modePrevisualisation) {
            $query .= ' AND `visible` = 1 ';
        }

        return $this->_db->query($query)->fetchColumn();
    }

    /**
     * Récupère les informations de la version selon son id
     *  Avec mise en cache
     *
     * @param int $id_version
     * @return array
     */
    public function getVersion($id_version) {
        if (!isset($this->_versions[$id_version])) {
            $query = 'SELECT * FROM `version` WHERE `id` = ' . $id_version;
            $this->_versions[$id_version] = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);
        }
        return $this->_versions[$id_version];
    }

    /**
     * Spécifie la classe à utiliser dans le chargement des pages
     *
     * @param string $className Nom de la classe avec les namespaces. Si null
     * passé, le nom de la classe est remis à la valeur par défaut.
     *
     * @return boolean Vrais si la classe à été enregistrée comme class à utiliser
     * pour le gabaritPage
     * @throws \Slrfw\Exception\Lib Lorsqu'aucune classe n'est trouvée
     */
    public function setPageClass($className)
    {
        /** Enregistrement de la valeur par défaut si null **/
        if ($className === null) {
            $this->gabaritClassName = self::DEFAULT_GABARIT_CLASS;
            return true;
        }

        $classNameOff = \Slrfw\FrontController::searchClass($className);

        if (!empty($classNameOff)) {
            $this->gabaritClassName = $classNameOff;
            return true;
        }

        throw new \Slrfw\Exception\Lib('Aucune classe trouvée ' . $className);
    }

    /**
     * Spécifie la classe à utiliser dans le chargement des blocs
     *
     * @param string $className Nom de la classe avec les namespaces. Si null
     * passé, le nom de la classe est remis à la valeur par défaut.
     *
     * @return boolean Vrais si la classe à été enregistrée comme class à utiliser
     * pour le gabaritBloc
     * @throws \Slrfw\Exception\Lib Lorsqu'aucune classe n'est trouvée
     */
    public function setBlocClass($className)
    {
        /** Enregistrement de la valeur par défaut si null **/
        if ($className === null) {
            $this->gabaritBlocClassName = self::DEFAULT_GABARIT_BLOC_CLASS;
            return true;
        }

        $classNameOff = \Slrfw\FrontController::searchClass($className);

        if (!empty($classNameOff)) {
            $this->gabaritBlocClassName = $classNameOff;
            return true;
        }

        throw new \Slrfw\Exception\Lib('Aucune classe trouvée ' . $className);
    }

    /**
     * Retourne un objet page à partir de l'identifiant de la page
     * <br />ou un objet page vide à partir de l'idenfiant du gabarit
     *
     * @param int  $id_version  identifiant de la version
     * @param int  $id_api      identifiant de l'api
     * @param int  $id_gab_page identifiant de la page
     * @param int  $id_gabarit  identifiant du gabarit
     * @param bool $join        chercher les infos sur les pages jointes
     * @param bool $visible     si oui uniquement les blocs visibles seront récupérés
     *
     * @return boolean|\Slrfw\Model\gabaritPage
     * @hook gabarit/ <gabaritName>Page A la fin du chargement de la page
     */
    public function getPage(
        $id_version,
        $id_api,
        $id_gab_page,
        $id_gabarit = 0,
        $join = false,
        $visible = false
    ) {
        $page = new $this->gabaritClassName();

        /**
         * Visibilité pour le front.
         * Même si le mode prévisu est activé, il ne faut pas montrer les
         * blocs dynamique caché.
         */
        $visibleFront = $visible && !$this->modePrevisualisation;

        if ($id_gab_page) {
            $query  = 'SELECT *'
                    . ' FROM `gab_page`'
                    . ' WHERE `id_version` = ' . $id_version
                    . ' AND `id_api` = ' . $id_api
                    . ' AND `id` = ' . $id_gab_page
                    . ' AND `suppr` = 0';

            if ($visibleFront) {
                $query .= ' AND `visible` = 1'
                        . ' AND `visible_parent` = 1';
            }

            $meta = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

            if (!$meta) {
                return false;
            }

            $page->setMeta($meta);
            $id_gabarit = $meta['id_gabarit'];
        }

        $data = $this->getVersion($id_version);
        $page->setVersion($data);

        $gabarit = $this->getGabarit($id_gabarit);

        $query  = 'SELECT *'
                . ' FROM `gab_gabarit`'
                . ' WHERE `id` = ' . $gabarit->getIdParent();
        $parentData = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);
        $gabarit->setGabaritParent($parentData);

        if (!$id_gab_page && $gabarit->getIdParent() > 0) {
            $parents = $this->getParentsPotentiels($id_version, $id_api, $parentData);
            $gabarit->setParents($parents);
        }

        $page->setGabarit($gabarit);

        $blocs = $this->getBlocs($gabarit);
        $page->setBlocs($blocs);

        if ($id_gab_page) {
            $parents = $this->getParents($meta['id_parent'], $id_version);
            $page->setParents($parents);

            $values = $this->getValues($page);

            if ($values) {
                $page->setValues($values);

                if ($join) {
                    $this->getJoinsValues($page, $id_version, $id_api, $visibleFront);
                }

                $blocs = $page->getBlocs();
                foreach ($blocs as $blocName => $bloc) {
                    $valuesBloc = $this->getBlocValues($bloc, $id_gab_page,
                        $id_version, $visible);

                    if ($valuesBloc) {
                        $bloc->setValues($valuesBloc);

                        if ($join) {
                            $this->getBlocJoinsValues($page, $blocName,
                                $id_version, $visible);
                        }
                    }
                }
            }
        }

        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('gabarit');

        $hook->page      = $page;
        $hook->idGabPage = $id_gab_page;
        $hook->visible   = $visible;

        $hook->exec($gabarit->getName() . 'Page');

        return $page;
    }

    /**
     * Renvoi les pages parentes potentiels pour une page en création
     *
     * @param int   $id_version identifiant de la version
     * @param int   $id_api     identifiant de l'api
     * @param array $parentData parents potentiels
     *
     * @return array
     */
    public function getParentsPotentiels($id_version, $id_api, $parentData)
    {
        $parents = array();

        /**
         * Si le gabarit parent est lui-même son propre parent
         */
        if ($parentData['id_parent'] == $parentData['id']) {
            $parents = $this->getList($id_version, $id_api, 0,
                $parentData['id']);

            foreach ($parents as $parent) {
                $enfants = $this->getList($id_version, $id_api,
                    $parent->getMeta('id'), $parentData['id']);
                $parent->setChildren($enfants);

                if (count($enfants) == 0) {
                    $firstChlid = $this->getFirstChild($id_version,
                        $parent->getMeta('id'), false);
                    $parent->setFirstChild($firstChlid);
                }

                foreach ($enfants as $enfant) {
                    $ptenfants = $this->getList($id_version, $id_api,
                        $enfant->getMeta('id'), $parentData['id']);
                    $enfant->setChildren($ptenfants);

                    if (count($ptenfants) == 0) {
                        $firstChlid = $this->getFirstChild($id_version,
                            $enfant->getMeta('id'), false);

                        $enfant->setFirstChild($firstChlid);
                    }
                }
            }
        } else {
            $idParents = array();

            $idTemp  = $parentData['id'];

            while ($idTemp > 0) {
                $idParents[] = $idTemp;
                $query  = 'SELECT `id_parent` FROM `gab_gabarit` WHERE `id` = ' . $idTemp;
                $idTemp   = $this->_db->query($query)->fetch(\PDO::FETCH_COLUMN);
            }

            $idParents = array_reverse($idParents);

            $parents = $this->getList($id_version, $id_api, 0, $idParents[0]);

            if (isset($idParents[1])) {
                foreach ($parents as $parent) {
                    $enfants = $this->getList($id_version, $id_api,
                        $parent->getMeta('id'), $idParents[1]);
                    $parent->setChildren($enfants);

                    if (isset($idParents[2])) {
                        foreach ($enfants as $enfant) {
                            $ptenfants = $this->getList($id_version, $id_api,
                                $enfant->getMeta('id'), $idParents[2]);
                            $enfant->setChildren($ptenfants);
                        }
                    }
                }
            }
        }

        return $parents;
    }

    /**
     * Retourne un objet gabarit à partir de l'identifiant du gabarit
     *  Avec mise en cache
     *
     * @param int $id_gabarit identifiant du gabarit en BDD
     *
     * @return \Slrfw\Model\gabarit
     */
    public function getGabarit($id_gabarit)
    {
        $query = 'SELECT * FROM `gab_gabarit` WHERE `id` = ' . $id_gabarit;
        $row = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

        $gabarit = new gabarit($row);

        if ($row['id_api'] > 0) {
            $query  = 'SELECT *'
                    . ' FROM `gab_api`'
                    . ' WHERE `id` = ' . $row['id_api'];
            $api = $this->_db->query($query)->fetch();
            $gabarit->setApi($api);
            $table = $api['name'] . '_' . $row['name'];
        } else {
            $table = $row['name'];
        }
        $gabarit->setTable($table);

        /**
         * Récupération des champs
         */
        $query  = 'SELECT IF (`g`.`label` IS NULL, "general", `g`.`label`), `c`.*'
                . ' FROM `gab_champ` `c`'
                . ' LEFT JOIN `gab_champ_group` `g` ON `g`.`id` = `c`.`id_group`'
                . ' WHERE `id_parent` = ' . $id_gabarit
                . ' AND `type_parent` = "gabarit"'
                . ' ORDER BY `g`.`ordre`, `c`.`ordre`';
        $champs = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        /**
         * TODO
         * a optimiser (1 requete pour champ dyn et champ normaux,
         * filtrer par id champ + type, voir faire des jointure sur gab_champ)
         */
        $query  = 'SELECT `gc`.`id`, `gcpv`.*'
                . ' FROM `gab_champ` `gc`'
                . ' INNER JOIN `gab_champ_param_value` `gcpv`'
                . ' ON `gcpv`.`id_champ` = `gc`.`id`'
                . ' ORDER BY `id_group`, `ordre`';
        $gabChampTypeParams = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $query  = 'SELECT `gct`.`code`, `gcp`.*, `gcp`.`default_value` `value`'
                . ' FROM `gab_champ_type` `gct`'
                . ' INNER JOIN `gab_champ_param` `gcp`'
                . ' ON `gct`.`code` = `gcp`.`code_champ_type`'
                . ' ORDER BY  `gct`.`ordre`, `gct`.`code`';
        $gabChampTypeParamsDefault = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        foreach ($gabChampTypeParamsDefault as $type => $params) {
            $paramsDefault[$type] = array();

            foreach ($params as $param) {
                $paramsDefault[$type][$param['code']] = $param['value'];
            }
        }

        $joins = array();
        foreach ($gabChampTypeParams as $idField => $params) {
            $params2 = array();

            foreach ($params as $param) {
                $params2[$param['code_champ_param']] = $param['value'];
            }
            foreach ($champs as &$group) {

                foreach ($group as &$champ) {
                    if (!isset($champ['params'])) {
                        if (isset($paramsDefault[$champ['type']])) {
                            $champ['params'] = $paramsDefault[$champ['type']];
                        } else {
                            $champ['params'] = array();
                        }
                    }

                    if ($champ['id'] == $idField) {
                        $champ['params'] = array_merge($champ['params'], $params2);
                    }

                    if ($champ['type'] == 'JOIN') {
                        $joins[$champ['id']] = $champ;
                        unset($champ);
                    }
                }
            }
        }
        $gabarit->setChamps($champs);
        $gabarit->setJoins($joins);
        $this->_gabarits[$id_gabarit] = $gabarit;
        return $this->_gabarits[$id_gabarit];
    }

    /**
     * Retourne les blocs dynamiques d'un gabarit/d'une page
     *
     * @param gabarit $gabarit     gabarit parent des blocs
     * @param int     $id_gab_page identifiant de la page 0 sinon
     *
     * @return gabaritBloc tableau associatif des blocs dynamiques
     */
    public function getBlocs($gabarit)
    {
        $query  = 'SELECT *'
                . ' FROM `gab_bloc`'
                . ' WHERE `id_gabarit` = ' . $gabarit->getId();
        $rows   = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        /**
         * TODO
         * a optimiser (1 requete pour champ dyn et champ normaux,
         * filtrer par id champ + type, voir faire des jointure sur gab_champ)
         */
        $query  = 'SELECT `gc`.`id`, `gcpv`.*'
                . ' FROM `gab_champ` `gc`'
                . ' INNER JOIN `gab_champ_param_value` `gcpv`'
                . ' ON `gcpv`.`id_champ` = `gc`.`id`'
                . ' ORDER BY `id_group`, `ordre`';
        $gabChampTypeParams = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $query  = 'SELECT `gct`.`code`, `gcp`.*, `gcp`.`default_value` `value`'
                . ' FROM `gab_champ_type` `gct`'
                . ' INNER JOIN `gab_champ_param` `gcp`'
                . ' ON `gct`.`code` = `gcp`.`code_champ_type`'
                . ' ORDER BY  `gct`.`ordre`, `gct`.`code`';
        $gabChampTypeParamsDefault = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        foreach ($gabChampTypeParamsDefault as $type => $params) {
            $paramsDefault[$type] = array();
            foreach ($params as $param) {
                $paramsDefault[$type][$param['code']] = $param['value'];
            }
        }

        $blocs = array();
        foreach ($rows as $row) {
            $gabarit_bloc = new gabarit($row);

            $table = $gabarit->getTable() . '_' . $row['name'];
            $gabarit_bloc->setTable($table);

            $joins = array();

            $query  = 'SELECT *'
                    . ' FROM `gab_champ`'
                    . ' WHERE `id_parent` = ' . $row['id']
                    . ' AND `type_parent` = "bloc"'
                    . ' ORDER BY `ordre`';
            $champs = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            /** Paramètres */
            foreach ($gabChampTypeParams as $idField => $params) {
                $params2 = array();

                foreach ($params as $param) {
                    $params2[$param['code_champ_param']] = $param['value'];
                }
                foreach ($champs as &$champ) {
                    if (!isset($champ['params'])) {
                        if (isset($paramsDefault[$champ['type']])) {
                            $champ['params'] = $paramsDefault[$champ['type']];
                        } else {
                            $champ['params'] = array();
                        }
                    }

                    if ($champ['id'] == $idField) {
                        $champ['params'] = array_merge($champ['params'], $params2);
                    }

                    if ($champ['type'] == 'JOIN') {
                        $joins[$champ['id']] = $champ;
                        unset($champ);
                    }
                }
            }

            $gabarit_bloc->setChamps($champs);
            $gabarit_bloc->setJoins($joins);

            $bloc = new $this->gabaritBlocClassName();

            $bloc->setGabarit($gabarit_bloc);
            $blocs[$gabarit_bloc->getName()] = $bloc;
        }

        return $blocs;
    }

    /**
     * Retourne la ligne des infos de la table générée à partir d'une page.
     *
     * @param gabaritPage $page page
     *
     * @return array
     */
    public function getValues($page)
    {
        $query = 'SELECT * FROM `' . $page->getGabarit()->getTable() . '`'
                . ' WHERE `id_gab_page` = ' . $page->getMeta('id')
                . ' AND `id_version` = ' . $page->getMeta('id_version');

        return $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les lignes des infos de la table générée à partir d'un bloc
     *  et de la page parente.
     *
     * @param gabaritBloc $bloc        bloc
     * @param int         $idGabPage identifiant de la page parente.
     * @param int         $idVersion  identifiant de la version.
     * @param bool        $visible     si faux on récupère les blocs visibles ou non,
     *  si vrai on récupère uniquement les blocs visibles.
     *
     * @return array
     * @hook Gabarit/ <blocType>BlocGet A la fin d'un chargement d'un bloc
     */
    public function getBlocValues($bloc, $idGabPage, $idVersion, $visible = false)
    {
        $query = 'SELECT * '
                . 'FROM `' . $bloc->getGabarit()->getTable() . '` '
                . 'WHERE `id_gab_page` = ' . $idGabPage . ' '
                . ' AND `suppr` = 0 '
                . ' AND `id_version` = ' . $idVersion . ' ';

        if ($visible) {
            $query .= ' AND `visible` = 1 ';
        }

        $query .= 'ORDER BY `ordre` ';

        $values = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $type = $bloc->getGabarit()->getData('type');
        if (!empty($type)) {
            $hook = new \Slrfw\Hook();
            $hook->setSubdirName('gabarit');

            $hook->bloc = $bloc;
            $hook->idGabPage = $idGabPage;
            $hook->values = $values;

            $hook->exec($type . 'BlocGet');

            $values = $hook->values;
        }
        unset($type);

        return $values;
    }

    /**
     * Récupère les jointures simple
     *
     * @param gabaritPage $page       page
     * @param int         $id_version identifiant de la version
     * @param int         $id_api     identifiant de l'api
     * @param bool        $visible    si vrai récupère uniquement si les blocs
     * visible de la jointure
     *
     * @return void
     */
    public function getJoinsValues($page, $id_version, $id_api, $visible = false)
    {
        $joinFields = array();
        foreach ($page->getGabarit()->getJoins() as $joinField) {
            $joinFields[$joinField['name']] = array(
                'value' => $page->getValues($joinField['name']),
                'table' => $joinField['params']['TABLE.NAME'],
                'fieldId' => $joinField['params']['TABLE.FIELD.ID'],
            );
        }

        if (count($joinFields) == 0) {
            return;
        }

        foreach ($joinFields as $joinName => $joinField) {
            if (!$joinField['value']) {
                continue;
            }

            $join = $this->getPage($id_version, $id_api, $joinField['value'], 0,
                false, $visible);

            $page->setValue($joinName, $join);
        }
    }

    /**
     * Récupère les jointures des blocs dynamique d'une page
     *
     * @param gabaritPage $page       page
     * @param string      $name_bloc  nom du bloc
     * @param int         $id_version identifiant de la version
     * @param bool        $visible    si vrai uniquement les blocs visibles des
     * jointures
     *
     * @return null
     */
    public function getBlocJoinsValues($page, $name_bloc, $id_version, $visible = false)
    {
        $joinFields = array();
        $joins = $page->getBlocs($name_bloc)->getGabarit()->getJoins();
        foreach ($joins as $joinField) {
            $joinFields[$joinField['name']] = array(
                'values'      => array(),
                'table'       => $joinField['params']['TABLE.NAME'],
                'fieldId'     => $joinField['params']['TABLE.FIELD.ID'],
                'typeGabPage' => $joinField['params']['TYPE.GAB.PAGE'],
            );

            foreach ($page->getBlocs($name_bloc)->getValues() as $value) {
                if ($value[$joinField['name']] != 0
                    && $value[$joinField['name']] != ''
                ) {
                    $joinFields[$joinField['name']]['values'][] =
                        $value[$joinField['name']];
                }
            }
        }

        if (count($joinFields) == 0) {
            return null;
        }

        $parents = array();
        foreach ($joinFields as $joinName => $joinField) {
            if (count($joinField['values']) == 0) {
                continue;
            }

            if ($joinField['typeGabPage'] == 0) {
                /**
                 * Cas des tables jointes sans gab_page
                 */

                $query  = 'SELECT `' . $joinField['table'] . '`.`'
                        . $joinField['fieldId'] . '`,'
                        . ' `' . $joinField['table'] . '`.*'
                        . ' FROM `' . $joinField['table'] . '`'
                        . ' WHERE `' . $joinField['table'] . '`.`'
                        . $joinField['fieldId']
                        . '` IN (' . implode(',', $joinField['values']) . ')';
                $values = $this->_db->query($query)->fetchAll(
                    \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

                $blocsValues = $page->getBlocs($name_bloc)->getValues();
                foreach ($blocsValues as $keyValue => $value) {
                    $page->getBlocs($name_bloc)->setValue($keyValue,
                        $values[$value[$joinName]], $joinName);
                }

                return;
            }

            $query  = 'SELECT `gab_page`.`id`, `gab_page`.*'
                    . ' FROM `gab_page`'
                    . ' WHERE `id_version` = ' . $id_version
                    . ' AND  `id`  IN (' . implode(',', $joinField['values']) . ')'
                    . ' AND `suppr` = 0';

            if ($visible) {
                $query .= ' AND `visible` = 1'
                        . ' AND `visible_parent` = 1';
            }

            $meta = $this->_db->query($query)->fetchAll(
                \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
            if (!$meta) {
                $page->getBlocs($name_bloc)->setValues(array());

                continue;
            }

            if ($joinField['table'] == 'gab_page') {
                $values = array();
                foreach ($meta as $id_gab_page => $m) {
                    $gabarit    = $this->getGabarit($m['id_gabarit']);

                    $query  = 'SELECT * FROM `' . $gabarit->getTable() . '`'
                            . ' WHERE `id_gab_page` = ' . $id_gab_page
                            . ' AND `id_version` = ' . $id_version;

                    $value = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

                    $values[$id_gab_page] = $value;
                }
            } else {
                $query  = 'SELECT `' . $joinField['table'] . '`.`id_gab_page`,'
                        . ' `' . $joinField['table'] . '`.*'
                        . ' FROM `' . $joinField['table'] . '`'
                        . ' WHERE `id_gab_page` IN ('
                        . implode(',', array_keys($meta)) . ')'
                        . ' AND `' . $joinField['table']
                        . '`.`id_version` = ' . $id_version;

                $values = $this->_db->query($query)->fetchAll(
                    \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
            }

            /**
             * On recupere les pages jointes.
             */
            $blocsValues = $page->getBlocs($name_bloc)->getValues();
            for ($i =  0;$i < count($blocsValues); $i++) {
                $keyValue = $i;
                $value = $blocsValues[$keyValue];
                if (!isset($meta[$value[$joinName]])) {
                    $page->getBlocs($name_bloc)->deleteValue($keyValue);
                    unset($blocsValues[$i]);
                    $blocsValues = array_values($blocsValues);
                    $i--;
                    continue;
                }

                if ($meta[$value[$joinName]]['id_parent'] != 0) {
                    $parents[] = $meta[$value[$joinName]]['id_parent'];
                }

                $pageJoin = new gabaritPage();
                $pageJoin->setMeta($meta[$value[$joinName]]);
                $pageJoin->setValues($values[$value[$joinName]]);
                $page->getBlocs($name_bloc)->setValue($keyValue, $pageJoin, $joinName);
            }

            /**
             * Si on a des parents pour une des valeurs d'un bloc
             */
            $parentsPage = array();
            $parentsMeta = array();
            if (count($parents) > 0) {
                $parentsUnique = array_unique($parents);

                unset($parents);
                $parents = array();
                $query  = 'SELECT * FROM `gab_page`'
                        . ' WHERE `id_version` = ' . $id_version
                        . ' AND `id` IN (' . implode(', ', $parentsUnique) . ')'
                        . ' AND `suppr` = 0';
                $parentTmp = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                $parentsMeta = array_merge($parentsMeta, $parentTmp);

                foreach ($parentTmp as $parentMeta) {
                    if ($parentMeta['id_parent'] != 0) {
                        $parents[] = $parentMeta['id_parent'];
                    }

                    $pageTmp = new gabaritPage();
                    $pageTmp->setMeta($parentMeta);
                    $parentsPage[$parentMeta['id']] = $pageTmp;
                }


                /**
                 * Si on a des grands parents
                 */
                $parentsUnique2 = array_unique(array_merge($parentsUnique, $parents));
                unset($parents);
                $parents = array();

                /**
                 * Si on a des grandparents qu'on avait pas recuperer
                 */
                if (count($parentsUnique2) > count($parentsUnique)) {
                    $query  = 'SELECT * FROM `gab_page`'
                            . ' WHERE `id_version` = ' . $id_version
                            . ' AND `id` IN (' . implode(', ', $parentsUnique2) . ')'
                            . ' AND `suppr` = 0';
                    $parentTmp = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                    $parentsMeta = array_merge($parentsMeta, $parentTmp);

                    foreach ($parentTmp as $parentMeta) {
                        if ($parentMeta['id_parent'] != 0) {
                            $parents[] = $parentMeta['id_parent'];
                        }

                        $pageTmp = new gabaritPage();
                        $pageTmp->setMeta($parentMeta);
                        $parentsPage[$parentMeta['id']] = $pageTmp;
                    }

                    /**
                     * Si on a des grands grands parents
                     */
                    $parentsUnique3 = array_unique(array_merge($parentsUnique2, $parents));
                    unset($parents);
                    $parents = array();

                    /**
                     * Si on a des grandparents qu'on avait pas recuperer
                     */
                    if (count($parentsUnique3) > count($parentsUnique2)) {
                        $query  = 'SELECT * FROM `gab_page`'
                                . ' WHERE `id_version` = ' . $id_version
                                . ' AND `id` IN (' . implode(', ', $parentsUnique3) . ')'
                                . ' AND `suppr` = 0';
                        $parentTmp = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

                        $parentsMeta2 = array_merge($parentsMeta, $parentTmp);
                        foreach ($parentTmp as $parentMeta) {
                            if ($parentMeta['id_parent'] != 0) {
                                $parents[] = $parentMeta['id_parent'];
                            }

                            $pageTmp = new gabaritPage();
                            $pageTmp->setMeta($parentMeta);
                            $parentsPage[$parentMeta['id']] = $pageTmp;
                        }
                    }
                }


                /**
                 * On remplit les parents et grands parents des pages joins
                 */
                foreach ($page->getBlocs($name_bloc)->getValues() as $keyValue => $value) {
                    $pageJoin = $page->getBlocs($name_bloc)->getValue($keyValue, $joinName);
                    $parents = array();

                    /**
                     * Si on a un parent
                     */
                    if (!is_object($pageJoin)
                        || !isset($parentsPage[$pageJoin->getMeta('id_parent')])
                    ) {
                        continue;
                    }

                    if ($pageJoin->getMeta('id_parent') > 0) {
                        $id_tmp     = $pageJoin->getMeta('id_parent');
                        $parent_tmp = $parentsPage[$id_tmp];
                        $parents[]  = $parent_tmp;

                        /**
                         * Si on a un grand parent
                         */
                        if ($parent_tmp->getMeta('id_parent') > 0) {
                            $id_tmp     = $parent_tmp->getMeta('id_parent');
                            $parent_tmp = $parentsPage[$id_tmp];
                            $parents[]  = $parent_tmp;

                            /**
                             * Si on a un grand grand parent
                             */
                            if ($parent_tmp->getMeta('id_parent') > 0) {
                                $id_tmp     = $parent_tmp->getMeta('id_parent');
                                $parent_tmp = $parentsPage[$id_tmp];
                                $parents[]  = $parent_tmp;
                            }
                        }
                    }

                    $pageJoin->setParents($parents);

                    /**
                     * Recuperation des blocs
                     */
                    $gabarit = $this->getGabarit($pageJoin->getMeta('id_gabarit'));
                    $blocs = $this->getBlocs($gabarit);
                    foreach ($blocs as $blocName => $bloc) {
                        $valuesBloc = $this->getBlocValues($bloc,
                            $pageJoin->getMeta('id'), $id_version, true);
                        if ($valuesBloc) {
                            $bloc->setValues($valuesBloc);
                        }
                    }
                    $pageJoin->setGabarit($gabarit);
                    $pageJoin->setBlocs($blocs);
                }
            }
        }
    }

    /**
     * Retourne les parents, grand-parents, aïeuls etc.
     * <br />dans un tableau associatif `nom du gabarit` => `objet page correspondant`
     *
     * @param int $id_gab_page_parent identifiant de la page parente
     * @param int $id_version         identifiant de la version
     *
     * @return array
     */
    public function getParents($id_gab_page_parent, $id_version)
    {
        $parents = array();
        $version = $this->getVersion($id_version);

        while ($id_gab_page_parent > 0) {
            $query  = 'SELECT * FROM `gab_page`'
                    . ' WHERE `id_version` = ' . $id_version
                    . ' AND `id` = ' . $id_gab_page_parent
                    . ' AND `suppr` = 0';
            $parentMeta = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

            $parentPage = new gabaritPage();
            $parentPage->setMeta($parentMeta);

            $parentPage->setVersion($version);

            $gabarit = $this->getGabarit($parentMeta['id_gabarit']);
            $parentPage->setGabarit($gabarit);

            $parents[] = $parentPage;

            $id_gab_page_parent = $parentMeta['id_parent'];
        }

        return $parents;
    }

    /**
     * Retourne un tableau de page a partir de l'identifiant d'un parent.
     * <br />On peut préciser l'identifiant du gabarit.
     *
     * @param int    $id_version identifiant de la version
     * @param int    $id_api     identifiant de l'api
     * @param int    $id_parent  identifiant de la page parente
     * <br />si page les plus haute dans le plan du site : <b>0</b>
     * <br />si ce critère ne doit pas être pris en compte : <b>false</b>
     * @param int    $id_gabarit identifiant(s) du/des gabarits
     * @param bool   $visible    si vrai uniquement les pages visibles
     * @param string $orderby    champ dans la bdd pour ordonner les pages
     * @param string $sens       ASC/DESC sens d'ordre
     * @param int    $debut      rang à partir duquel récupérer les pages
     * @param int    $nbre       nombre de pages à récupérer
     * @param bool   $main       ???
     *
     * @return \Slrfw\Model\gabaritPage|array tableau de page
     */
    public function getList(
        $id_version,
        $id_api = 1,
        $id_parent = false,
        $id_gabarit = 0,
        $visible = false,
        $orderby = 'ordre',
        $sens = 'ASC',
        $debut = 0,
        $nbre = 0,
        $main = false
    ) {
        if ($this->modePrevisualisation) {
            $visible = false;
        }

        $query = 'SELECT `p`.*, COUNT(`e`.`id`) `nbre_enfants`'
                . ' FROM `gab_page` `p` LEFT JOIN `gab_page` `e`'
                . ' ON `e`.`id_parent` = `p`.`id` AND `e`.`suppr` = 0'
                . ' AND `e`.`id_version` = ' . $id_version;

        if ($visible) {
            $query .= ' AND `e`.`visible` = 1';
        }

        if ($main) {
            $query .= ' INNER JOIN `gab_gabarit` `g`'
                    . ' ON `p`.`id_gabarit` = `g`.`id`'
                    . ' AND `g`.`main` = 1';
        }

        $query .= ' WHERE `p`.`suppr` = 0 AND `p`.`id_version` = ' . $id_version
                . ' AND `p`.`id_api` = ' . $id_api;

        if ($visible) {
            $query .= ' AND `p`.`visible` = 1'
                    . ' AND `p`.`visible_parent` = 1';
        }

        if ($id_parent !== false) {
            $query .= ' AND `p`.`id_parent` = ' . $id_parent;
        }

        if ($id_gabarit) {
            if (is_array($id_gabarit)) {
                if (count($id_gabarit) > 0) {
                    $query .= ' AND `p`.`id_gabarit` IN ('
                            . implode(', ', $id_gabarit) . ')';
                }
            } else {
                $query .= ' AND `p`.`id_gabarit` = ' . $id_gabarit;
            }
        }

        $query .= ' GROUP BY `p`.`id`';

        $query .= ' ORDER BY `p`.`' . $orderby . '` ' . $sens;

        if ($nbre) {
            $query .= ' LIMIT ' . $debut . ', ' . $nbre;
        }

        $metas = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $version = $this->getVersion($id_version);

        $pages = array();
        foreach ($metas as $meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            $page->setVersion($version);
            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * Retourne les gabarits et leurs pages qui sont checkés comme main
     *
     * @param int $id_version identifiant de la version
     * @param int $id_api     identifiant de l'api
     *
     * @return array
     */
    public function getMain($id_version, $id_api)
    {
        $query = 'SELECT `g`.`name`, `p`.*'
                . ' FROM `gab_page` `p` '
                . ' INNER JOIN `gab_gabarit` `g` ON `p`.`id_gabarit` = `g`.`id`'
                . ' AND `g`.`main` = 1 AND  `g`.`id_api` = ' . $id_api
                . ' WHERE `p`.`suppr` = 0 AND `p`.`id_version` = ' . $id_version
                . ' ORDER BY `p`.`ordre` ASC';

        $metas = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $pages = array();
        foreach ($metas as $gabaritName => $metasGabarit) {
            foreach ($metasGabarit as $meta) {
                $page = new gabaritPage();
                $page->setMeta($meta);
                $pages[$gabaritName][] = $page;
            }
        }

        return $pages;
    }

    /**
     * Retourne un tableau de page dont le titre contient un terme
     *
     * @param int    $id_version identifiant de la version
     * @param string $term       chaine de caractère à chercher
     * @param int    $id_gabarit identifiant du gabarit
     * @param int    $id_parent  identifiant de la page parente
     * @param bool   $visible    si vrai uniquement les pages visible
     *
     * @return gabaritPage tableau de page
     */
    public function getSearch(
        $id_version,
        $term,
        $id_gabarit = 0,
        $id_parent = false,
        $visible = false
    ) {
        if ($this->modePrevisualisation) {
            $visible = false;
        }

        $query  = 'SELECT *'
                . ' FROM `gab_page`'
                . ' WHERE `suppr` = 0 AND `id_version` = ' . $id_version
                . ' AND `titre` LIKE ' . $this->_db->quote('%' . $term . '%');

        if ($id_gabarit) {
            $query .= ' AND `id_gabarit` = ' . $id_gabarit;
        }

        if ($visible) {
            $query .= ' AND `visible` = 1';
        }

        if ($id_parent != false) {
            $query .= ' AND `id_parent` = ' . $id_parent;
        }

        $metas = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $version = $this->getVersion($id_version);

        $pages = array();
        foreach ($metas as $meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            $page->setVersion($version);

            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * Permet de récupère le premier enfant
     * (exemple : pour les rubriques qui n'ont pas de `view`)
     *
     * @param int $id_version identifiant de la version
     * @param int $id_parent  identifiant de la page parente
     *
     * @return gabaritPage
     */
    public function getFirstChild($id_version, $id_parent = 0, $visible = true)
    {
        if ($this->modePrevisualisation) {
            $visible = false;
        }

        $query  = 'SELECT *'
                . ' FROM `gab_page`'
                . ' WHERE `id_parent` = ' . $id_parent
                . ' AND `suppr` = 0'
                . ' AND `id_version` = ' . $id_version;

        if ($visible) {
            $query .= ' AND `visible` = 1';
        }

        $query .= ' ORDER BY `ordre`'
                . ' LIMIT 0, 1';
        $meta = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

        if ($meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            return $page;
        }

        return null;
    }

    /**
     * Supprime une page (logiquement en bdd)
     *
     * @param int $id_version  identifiant de la version
     * @param int $id_api      identifiant de l'api
     * @param int $id_gab_page identifiant de la page
     *
     * @return bool
     */
    public function delete($id_gab_page)
    {
        $query  = 'UPDATE gab_page SET'
                . ' suppr = 1,'
                . ' date_modif = NOW()'
                . ' WHERE id = ' . $id_gab_page;
        return $this->_db->exec($query);
    }

    /**
     * Rend visible / cache une page
     *
     * @param int $id_version  identifiant de la version
     * @param int $id_api      identifiant de l'api
     * @param int $id_gab_page identifiant de la page
     * @param 0|1 $visible     <b>0</b> si on cache la page, <b>1</b> si on la
     * rend visible
     *
     * @return bool
     */
    public function setVisible($id_version, $id_api, $id_gab_page, $visible)
    {
        $query  = 'UPDATE gab_page SET'
                . ' visible = ' . $visible
                . ' WHERE id = ' . $id_gab_page
                . ' AND id_version = ' . $id_version
                . ' AND id_api = ' . $id_api;
        $this->_db->exec($query);

        return $this->setVisibleParent($id_version, $id_api, $id_gab_page, $visible);
    }

    /**
     * Met à jour la visibilité d'une page pour ses enfants (champ
     * `visible_parent` en bdd)
     *
     * @param int $id_version identifiant de la version
     * @param int $id_api     identifiant de l'api
     * @param int $id_parent  identifiant de la page
     * @param 0|1 $visible    <b>0</b> si on cache la page, <b>1</b> si on la
     * rend visible
     *
     * @return true
     */
    public function setVisibleParent($id_version, $id_api, $id_parent, $visible)
    {
        $query  = 'SELECT visible, visible_parent'
                . ' FROM gab_page'
                . ' WHERE id = ' . $id_parent
                . ' AND id_version = ' . $id_version
                . ' AND id_api = ' . $id_api;
        $page   = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

        $query  = 'UPDATE gab_page SET visible_parent = ' . $visible
                . ' WHERE id_parent = ' . $id_parent
                . ' AND id_version = ' . $id_version
                . ' AND id_api = ' . $id_api;
        $this->_db->exec($query);

        $query  = 'SELECT id'
                . ' FROM gab_page'
                . ' WHERE id_parent = ' . $id_parent
                . ' AND id_version = ' . $id_version
                . ' AND id_api = ' . $id_api;
        $enfants = $this->_db->query($query)->fetchAll(\PDO::FETCH_COLUMN);

        if ($page['visible'] + $page['visible_parent'] >= 2) {
            $visibleEnfant = 1;
        } else {
            $visibleEnfant = 0;
        }

        foreach ($enfants as $enfant) {
            $this->setVisibleParent($id_version, $id_api, $enfant, $visibleEnfant);
        }

        return true;
    }

    /**
     * Sauve une page et ses blocs dynamique.
     *
     * @param array $donnees données à enregistrer
     *
     * @return gabaritPage|false|null Retourne<ul>
     * <li>gabaritPage crée ou réelement modifiée</li>
     * <li>FALSE si aucune modification n'est apparue</li>
     * <li>NULL si l'identifiant de page ou de gabarit n'est pas valide</li>
     * </ul>
     */
    public function save($donnees)
    {
        $gabarit = $this->getGabarit($donnees['id_gabarit']);
        $api = $gabarit->getApi();

        if (isset($donnees['id_version'])) {
            $versionId = $donnees['id_version'];
        } else {
            $versionId = BACK_ID_VERSION;
        }

        $query  = 'SELECT `id`'
                . ' FROM `version`'
                . ' WHERE id_api = ' . $api['id']
                . ' ORDER BY `id` != ' . $versionId;
        $this->_versionsIds = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_COLUMN);

        $updating = ($donnees['id_gab_page'] > 0);

        if ($updating) {
            $page = $this->getPage(
                $versionId,
                $api['id'],
                $donnees['id_gab_page'],
                0
            );
        } else {
            $page = $this->getPage(
                $versionId,
                $api['id'],
                0,
                $donnees['id_gabarit']
            );
        }

        if (!$page) {
            return null;
        }

        $res = $this->saveMeta($page, $donnees);

        if ($updating > 0) {
            $id_gab_page = $donnees['id_gab_page'];

            if ($res === false) {
                return false;
            }
        } else {
            $id_gab_page = $res;
        }

        $page = $this->getPage($versionId, $api['id'], $id_gab_page, 0);

        $this->savePage($page, $donnees);

        $blocs = $page->getBlocs();
        foreach ($blocs as $bloc) {
            $this->saveBloc($bloc, $id_gab_page, $page->getMeta('id_version'),
                $donnees);
        }

        $newPage = $this->getPage($versionId, $api['id'], $page->getMeta('id'), 0);

        return $newPage;
    }

    /**
     * Passe en mode prévisualisation
     *
     * @param bool $enabled mode de prévisualisation ou pas
     *
     * @return void
     */
    public function setModePrevisualisation($enabled = false)
    {
        $this->modePrevisualisation = $enabled;
    }

    /**
     * Sauve les infos meta de la page
     *
     * @param gabaritPage $page    page
     * @param array       $donnees données à enregistrer
     *
     * @return int|boolean
     */
    protected function saveMeta($page, $donnees)
    {
        $updating = $donnees['id_gab_page'] > 0;

        /**
         * On recupere les ids de gabarits pour l'api courante
         */
        $api    = $page->getGabarit()->getApi();
        $query  = 'SELECT `gab_gabarit`.id FROM `gab_gabarit`'
                . ' WHERE `gab_gabarit`.`id_api` = ' . $api['id'];
        $gabaritsFromCurrentApi = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_COLUMN);

        $modif = false;

        if ($updating) {
            /**
             * Mise a jour d'une page
             */

            if ($page->getMeta('rewriting') == '') {
                /**
                 * Cas d'une page qui n'a pas été traduite
                 */

                if ($donnees['rewriting'] == '') {
                    if ($page->getVersion('exotique') > 0) {
                        $titre_rew = $donnees['titre_rew'];
                    } else {
                        $titre_rew = $donnees['titre'];
                    }
                } else {
                    $titre_rew = $donnees['rewriting'];
                }
            } else {
                if ($donnees['rewriting'] == '') {
                    $titre_rew = $page->getMeta('rewriting');
                } else {
                    $titre_rew = $donnees['rewriting'];
                }
            }

            $query  = 'AND `suppr` = 0 AND `id_gabarit` IN ('
                    . implode(', ', $gabaritsFromCurrentApi) . ')'
                    . ' AND `id_parent` = ' . $page->getMeta('id_parent')
                    . ' AND `id_version` = ' . $page->getMeta('id_version')
                    . ' AND `id` != ' . $page->getMeta('id');
            $rewriting = $this->_db->rewrit(
                $titre_rew, 'gab_page', 'rewriting', $query
            );

            $query = 'UPDATE `gab_page` SET'
                    . ' `titre` = ' . $this->_db->quote($donnees['titre']) . ',';

            if ($page->getVersion('exotique') > 0) {
                $query .= ' `titre_rew`      = '
                        . $this->_db->quote($donnees['titre_rew']) . ',';
            }

            if (!isset($donnees['author'])) {
                $donnees['author'] = 0;
            }

            $query .= ' `bal_title` = ' . $this->_db->quote($donnees['bal_title']) . ','
                    . ' `bal_key` = ' . $this->_db->quote($donnees['bal_key']) . ','
                    . ' `author` = ' . $this->_db->quote($donnees['author']) . ','
                    . ' `bal_descr` = ' . $this->_db->quote($donnees['bal_descr']) . ','
                    . ' `importance` = ' . $donnees['importance'] . ','
                    . ' `date_modif` = NOW(),';

            if (isset($donnees['no_index']) && $page->getMeta('id') != 1) {
                $query .= ' `no_index`   = ' . $donnees['no_index'] . ',';
            } else {
                $query .= ' `no_index`   = 0,';
            }

            $query .= ' `canonical` = ' . $this->_db->quote($donnees['canonical']) . ','
                    . ' `rewriting` = ' . $this->_db->quote($rewriting)
                    . ' WHERE `id` = ' . $page->getMeta('id')
                    . ' AND `id_api` = ' . $page->getMeta('id_api')
                    . ' AND `id_version` = ' . $page->getMeta('id_version');

            $modfiTmp = $this->_db->exec($query);
            if (!$modif && $modfiTmp > 0) {
                $modif = true;
            }

            $urlParent = '';
            $parents = $this->getParents($page->getMeta('id_parent'),
                $page->getMeta('id_version'));
            foreach ($parents as $parent) {
                $urlParent = $parent->getMeta('rewriting') . '/' . $urlParent;
            }
            $newUrl = $urlParent . $rewriting . $page->getGabarit()->getExtension();

            $redirections = $donnees['301'];
            /** Si le rewriting a été modifié */
            if ($rewriting != $page->getMeta('rewriting')
                && $page->getMeta('rewriting') != ''
            ) {
                $redirections[]   = $urlParent . $page->getMeta('rewriting')
                                    . $page->getGabarit()->getExtension();
            }
            $redirections = array_unique($redirections);

            /** On supprime toutes les urls de redirection 301 pour la page courante */
            $query2Del  = 'DELETE FROM `redirection`'
                        . ' WHERE `new` = ' . $this->_db->quote($newUrl)
                        . ' AND `id_version` = ' . $page->getMeta('id_version')
                        . ' AND `id_api` = ' . $api['id'];
            $this->_db->exec($query2Del);

            /** On insert toutes les urls dans le bloc redirection 301 */
            $queries2 = array();
            foreach ($redirections as $redirect301) {
                $oldUrl = $redirect301;

                if ($oldUrl != ''
                    && $oldUrl != $newUrl
                ) {
                    $queries2[] = 'INSERT INTO `redirection` SET'
                                . ' `old` = ' . $this->_db->quote($oldUrl) . ', '
                                . ' `new` = ' . $this->_db->quote($newUrl) . ', '
                                . ' `id_api` = ' . $api['id'] . ', '
                                . ' `id_version` = ' . $page->getMeta('id_version') . ';';
                }
            }

            foreach ($queries2 as $query2) {
                $modfiTmp = $this->_db->exec($query2);
                if (!$modif && $modfiTmp > 0) {
                    $modif = true;
                }
            }

            return $modif;
        } else {
            /**
             * Insertion dans la table `gab_page`.
             */

            if (isset($donnees['id_parent']) && $donnees['id_parent']) {
                $id_parent = $donnees['id_parent'];

                $query  = 'SELECT id_version, (visible + visible_parent)'
                        . ' FROM gab_page'
                        . ' WHERE id = ' . $donnees['id_parent']
                        . ' AND id_api = ' . $api['id'];
                $visibles_parents = $this->_db->query($query)->fetchAll(
                    \PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
            } else {
                $id_parent = 0;
            }

            if ($donnees['rewriting'] == '') {
                $titre_rew = $donnees['titre'];
            } else {
                $titre_rew = $donnees['rewriting'];
            }

            if ($donnees['bal_title'] == '') {
                $donnees['bal_title'] = $donnees['titre'];
            }

            $query  = 'AND `suppr` = 0'
                    . ' AND `id_gabarit` IN ('
                    . implode(', ', $gabaritsFromCurrentApi) . ')'
                    . ' AND `id_parent` = ' . $id_parent
                    . ' AND `id_version` = ' . $donnees['id_version'];
            $rewriting = $this->_db->rewrit($titre_rew, 'gab_page', 'rewriting',
                $query);

            //Si niveau 0, on met l'ordre à MAX + 1 sinon MIN - 1
            $type = "MIN";
            if ($id_parent == 0)
                $type = "MAX";

            $query  = 'SELECT ' . $type . '(`ordre`)'
                    . ' FROM `gab_page`'
                    . ' WHERE `id_api` = ' . $api['id']
                    . ' AND `id_parent` = ' . $id_parent;
            $ordre = $this->_db->query($query)->fetch(\PDO::FETCH_COLUMN);
            if ($ordre !== false) {
                if($type == "MAX") {
                    $ordre++;
                } else {
                    $ordre--;
                }
            } else {
                $ordre = 0;
            }

            $id_gab_page = 0;
            foreach ($this->_versionsIds as $version) {
                $query = 'INSERT INTO `gab_page` SET ';

                if ($id_gab_page > 0) {
                    $query .= '`id` = ' . $id_gab_page . ',';
                }

                $query .= '`id_gabarit` = ' . $page->getGabarit()->getId() . ','
                        . '`titre` = ' . $this->_db->quote($donnees['titre']) . ',';

                if ($id_gab_page > 0) {
                    $query .= '`rewriting` = "",';
                } else {
                    $query .= '`rewriting` = ' . $this->_db->quote($rewriting) . ',';
                }

                $query .= '`bal_title` = ' . $this->_db->quote($donnees['bal_title']) . ','
                        . '`bal_key` = ' . $this->_db->quote($donnees['bal_key']) . ','
                        . '`author` = ' . $this->_db->quote($donnees['author']) . ','
                        . '`bal_descr` = ' . $this->_db->quote($donnees['bal_descr']) . ',';

                if (isset($donnees['no_index']) && $page->getMeta('id') != 1) {
                    $query .= ' `no_index`   = ' . $donnees['no_index'] . ',';
                } else {
                    $query .= ' `no_index`   = 0,';
                }

                $query .= '`canonical` = ' . $this->_db->quote($donnees['canonical']) . ','
                        . '`importance` = ' . $donnees['importance'] . ','
                        . '`id_parent` = ' . $id_parent . ', '
                        . '`ordre` = ' . intval($ordre) . ','
                        . '`date_crea` = NOW(),'
                        . '`date_modif` = NOW(),'
                        . '`visible` = 0,'
                        . '`id_api` = ' . $api['id'] . ','
                        . '`id_version` = ' . $version . ',';

                if ($id_parent > 0) {
                    if ($visibles_parents[$version] >= 2) {
                        $visible_parent = 1;
                    } else {
                        $visible_parent = 0;
                    }
                } else {
                    $visible_parent = 1;
                }

                $query .= '`visible_parent` = ' . $visible_parent;

                $this->_db->exec($query);

                if ($id_gab_page == 0) {
                    $id_gab_page = $this->_db->lastInsertId();
                }
            }

            $urlParent = '';
            foreach ($this->getParents($id_parent, $version) as $parent) {
                $urlParent = $parent->getMeta('rewriting') . '/' . $urlParent;
            }

            $newUrl = $urlParent . $rewriting
                    . $page->getGabarit()->getExtension();

            $donnees['301'] = array_unique($donnees['301']);

            /**
             * On insert toutes les urls dans le bloc redirection 301
             */
            $queries2 = array();
            foreach ($donnees['301'] as $redirect301) {
                $oldUrl = $redirect301;
                if ($oldUrl != '' && $oldUrl != $newUrl) {
                    $queries2[] = 'INSERT INTO `redirection` SET'
                                . ' `old` = ' . $this->_db->quote($oldUrl) . ', '
                                . ' `new` = ' . $this->_db->quote($newUrl) . ', '
                                . ' `id_api` = ' . $api['id'] . ', '
                                . ' `id_version` = 1;';
                }
            }

            foreach ($queries2 as $query2) {
                $this->_db->exec($query2);
            }

            return $id_gab_page;
        }
    }

    /**
     * Enregistre les données propre d'une page.
     *
     * @param gabaritPage $page         la page
     * @param array       $donnees     les données à enregistrer
     * @param boolean     $partialSave Ne sauvegarde que les champs présents
     *
     * @return boolean Retourne TRUE si le contenu de la page a été modifié,
     * FALSE sinon.
     * @hook gabarit/ <gabaritName>Save A la fin de l'enregistrement
     */
    public function savePage($page, $donnees, $partialSave = false)
    {
        $updating = $donnees['id_gab_page'] > 0;

        $gabarit = $page->getGabarit();
        $id_gab_page = $page->getMeta('id');
        $id_version = $page->getMeta('id_version');
        $table = $gabarit->getTable();

        $allchamps = $gabarit->getChamps();
        $filesUsed = array();
        $champsExiste = count($allchamps);

        if ($updating) {
            $query  = '';
            $where  = 'WHERE `id_version` = ' . $id_version
                    . ' AND `id_gab_page` = ' . $id_gab_page;

            $queryT = '';
            $whereT = 'WHERE `id_gab_page` = ' . $id_gab_page;
        } else {
            $query  = 'INSERT INTO `' . $table . '` SET'
                    . ' `id_gab_page` = ' . $id_gab_page . ',';
        }

        foreach ($allchamps as $name_group => $champs) {
            foreach ($champs as $champ) {
                if ($partialSave
                    && !isset($donnees['champ' . $champ['id']])
                ) {
                    continue;
                }

                if ($champ['visible'] == 0) {
                    continue;
                }

                $value = $donnees['champ' . $champ['id']][0];
                $value = trim($value);

                if ($champ['type'] != 'WYSIWYG'
                    && $champ['type'] != 'TEXTAREA'
                ) {
                    $value = str_replace('"', '&quot;', $value);
                }

                if ($champ['typedonnee'] == 'FILE'
                    && $value != ''
                ) {
                    $filesUsed[] = $value;
                }

                if ($champ['typedonnee'] == 'DATE' && $value != '') {
                    $value = \Slrfw\Format\DateTime::frToSql($value);
                }

                if ($champ['trad'] == 0 && $updating) {
                    $queryT    .= '`' . $champ['name'] . '` = '
                                . $this->_db->quote($value) . ',';
                }

                switch ($champ['type']) {
                    case "GMAP":
                        $lat = $donnees['champ' . $champ['id'] . "_lat"][0];
                        $lng = $donnees['champ' . $champ['id'] . "_lng"][0];
                        $zoom = $donnees['champ' . $champ['id'] . "_zoom"][0];
                        $query .= '`' . $champ['name'] . '_lat` = '
                                    . $this->_db->quote($lat) . ',';
                        $query .= '`' . $champ['name'] . '_lng` = '
                                    . $this->_db->quote($lng) . ',';
                        $query .= '`' . $champ['name'] . '_zoom` = '
                                    . $this->_db->quote($zoom) . ',';
                        break;

                    default:
                        $query .= '`' . $champ['name'] . '` = '
                                    . $this->_db->quote($value) . ',';
                        break;
                }
            }
        }

        $modif = false;

        if ($updating) {
            if ($champsExiste) {
                if ($query != '') {
                    $queryTmp   = 'UPDATE `' . $table . '` SET '
                                . substr($query, 0, -1) . ' ' . $where;
                }

                $tmpModif = $this->_db->exec($queryTmp);
                if (!$modif && $tmpModif > 0) {
                    $modif = true;
                }

                if ($queryT != '') {
                    $queryTmp   = 'UPDATE `' . $table . '` SET '
                                . substr($queryT, 0, -1) . ' ' . $whereT;
                }

                $tmpModif = $this->_db->exec($queryTmp);
                if (!$modif && $tmpModif > 0) {
                    $modif = true;
                }
            }
        } else {
            foreach ($this->_versionsIds as $id_version) {
                $queryTmp = $query . '`id_version` = ' . $id_version;

                $tmpModif = $this->_db->exec($queryTmp);
                if (!$modif && $tmpModif > 0) {
                    $modif = true;
                }
            }

            $id_gab_page = $this->_db->lastInsertId();
        }

        $this->deleteUsedFile($id_version, $id_gab_page);
        $this->saveUsedFile($id_version, $id_gab_page, $filesUsed);

        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('gabarit');

        $hook->data = $donnees;
        $hook->page = $page;
        $hook->exec($gabarit->getName() . 'Save');

        return $modif;
    }

    /**
     * Sauve un bloc dynamique d'une page
     *
     * @param gabaritBloc $bloc        bloc à sauver
     * @param int         $id_gab_page identifiant de la page parente du bloc
     * @param int         $id_version  identifiant de la version
     * @param array       &$donnees    données à enregistrer
     * @param boolean     $partialSave Ne sauvegarde que les champs présents
     *
     * @return boolean Retourne TRUE si une des lignes du bloc a été modifié,
     * FALSE sinon.
     * @hook gabarit/ <gabaritName>Bloc A la fin de l'enregistrement
     */
    public function saveBloc(
        $bloc,
        $id_gab_page,
        $id_version,
        &$donnees,
        $partialSave = false
    ) {
        $gabarit = $bloc->getGabarit();
        $table = $gabarit->getTable();
        $champs = $gabarit->getChamps();
        $ordre = 1;

        $modif = false;

        $firstField = current($bloc->getGabarit()->getJoins());
        if (count($bloc->getGabarit()->getJoins()) == 1
            && $firstField['type'] == 'JOIN'
            && $firstField['params']['VIEW'] == 'simple'
        ) {
            /**
             * Cas des types join en mode simpleFieldset (Checkbox)
             */

            $query  = 'DELETE FROM `' . $table . '` WHERE'
                    . ' `id_version` = ' . $id_version
                    . ' AND `id_gab_page` = ' . $id_gab_page;

            $tmpModif = $this->_db->exec($query);
            if (!$modif && $tmpModif > 0) {
                $modif = true;
            }

            if (isset($donnees['champ' . $firstField['id']])) {
                foreach ($donnees['champ' . $firstField['id']] as $value) {
                    $fieldSql   = '`' . $firstField['name'] . '` = '
                                . $this->_db->quote($value);

                    $query  = 'INSERT INTO `' . $table . '` SET'
                            . ' `id_gab_page` = ' . $id_gab_page . ','
                            . ' `id_version` = ' . $id_version . ','
                            . ' `visible` = 1,'
                            . $fieldSql;

                    $tmpModif = $this->_db->exec($query);
                    if (!$modif && $tmpModif > 0) {
                        $modif = true;
                    }
                }
            }
        } else {
            /**
             * Cas des blocs classiques
             */

            if (isset($donnees['id_' . $gabarit->getTable()])) {
                foreach ($donnees['id_' . $gabarit->getTable()] as $id_bloc) {
                    if ($id_bloc === 'ANNULE') {
                        continue;
                    }
                    $res = $this->saveBlocLine($table, $champs, $id_bloc,
                        $ordre, $donnees, $id_gab_page, $id_version, $partialSave);

                    if ($id_bloc) {
                        $ids_blocs[] = $id_bloc;
                    } else {
                        $ids_blocs[] = $res;
                    }

                    $tmpModif = $res;
                    if (!$modif && $tmpModif > 0) {
                        $modif = true;
                    }

                    $ordre++;
                }

                $query  = 'UPDATE `' . $table . '` SET `suppr` = NOW()'
                        . ' WHERE `suppr` = 0'
                        . ' AND `id_gab_page` = ' . $id_gab_page
                        . ' AND `id` NOT IN (' . implode(',', $ids_blocs) . ')';
                $tmpModif = $this->_db->exec($query);
                if (!$modif && $tmpModif > 0) {
                    $modif = true;
                }
            }
        }

        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('gabarit');

        $hook->data = $donnees;
        if (isset($ids_blocs)) {
            $hook->ids_blocs = $ids_blocs;
        } else {
            $hook->ids_blocs = array();
        }
        $hook->idGabPage = $id_gab_page;
        $hook->idVersion = $id_version;
        $hook->tableName = $table;
        $hook->exec($gabarit->getName() . 'Bloc');

        return $modif;
    }

    /**
     * Sauve une ligne d'un bloc dynamique
     *
     * @param string  $table       table en BDD du bloc
     * @param array   $champs      champs du bloc dynamique
     * @param int     $id_bloc     identifiant du bloc
     * @param int     $ordre       ordre du bloc
     * @param array   &$donnees    données à enregistrer
     * @param int     $id_gab_page identifiant de la page
     * @param int     $id_version  identifiant de la version
     * @param boolean $partialSave identifiant de la version
     *
     * @return int|bool Retourne :
     * <ul>
     * <li>si le bloc est crée, l'identifiant de la ligne crée en BDD</li>
     * <li>si le bloc existe déjà et la ligne en BDD est modifiée, TRUE</li>
     * <li>si le bloc existe déjà mais la ligne en BDD n'est pas modifiée, FALSE</li>
     * </ul>
     */
    protected function saveBlocLine(
        $table,
        $champs,
        $id_bloc,
        $ordre,
        &$donnees,
        $id_gab_page,
        $id_version,
        $partialSave = false
    ) {
        $filesUsed = array();
        $updating = ($id_bloc > 0);
        if (!$partialSave) {
            $visible  = array_shift($donnees['visible']);

            if ($updating) {
                $query  = 'UPDATE `' . $table . '` SET'
                        . ' `ordre` = ' . $ordre . ','
                        . ' `visible` = ' . $visible . ',';
            } else {
                $query  = 'INSERT INTO `' . $table . '` SET'
                        . ' `id_gab_page` = ' . $id_gab_page . ','
                        . ' `ordre` = ' . $ordre . ',';
            }
        } else {
            $query = 'UPDATE `' . $table . '` SET ';
        }

        foreach ($champs as $champ) {
            if ($partialSave && !isset($donnees['champ' . $champ['id']])) {
                continue;
            }
            if ($champ['visible'] == 0) {
                continue;
            }

            $value = array_shift($donnees['champ' . $champ['id']]);

            $value = trim($value);

            if ($champ['type'] != 'WYSIWYG'
                && $champ['type'] != 'TEXTAREA'
            ) {
                $value = str_replace('"', '&quot;', $value);
            }

            if ($champ['typedonnee'] == 'FILE'
                && $value != ''
            ) {
                $filesUsed[] = $value;
            }


            if ($champ['typedonnee'] == 'DATE' && $value != '') {
                $value = \Slrfw\Format\DateTime::frToSql($value);
            }

            switch ($champ['type']) {
                case "GMAP":
                    $lat = array_shift($donnees['champ' . $champ['id'] . "_lat"]);
                    $lng = array_shift($donnees['champ' . $champ['id'] . "_lng"]);
                    $zoom = array_shift($donnees['champ' . $champ['id'] . "_zoom"]);
                    $query .= '`' . $champ['name'] . '_lat` = '
                                . $this->_db->quote($lat) . ',';
                    $query .= '`' . $champ['name'] . '_lng` = '
                                . $this->_db->quote($lng) . ',';
                    $query .= '`' . $champ['name'] . '_zoom` = '
                                . $this->_db->quote($zoom) . ',';
                    break;

                default:
                    $query .= '`' . $champ['name'] . '` = '
                                . $this->_db->quote($value) . ',';
                    break;
            }
        }


        $modif = false;

        if ($updating) {
            $queryTmp   = substr($query, 0, -1)
                        . ' WHERE `id_version` = ' . $id_version
                        . ' AND `id` = ' . $id_bloc;

            $tmpModif = $this->_db->exec($queryTmp);
            if (!$modif && $tmpModif > 0) {
                $modif = true;
            }
        } else {
            $id_bloc = 0;
            foreach ($this->_versionsIds as $id_version) {
                $queryTmp = $query . ' `id_version`  = ' . $id_version;

                if ($id_bloc) {
                    $queryTmp  .= ', `id` = ' . $id_bloc
                                . ', `visible` = 0';
                } else {
                    $queryTmp  .= ', `visible` = ' . $visible;
                }

                $tmpModif = $this->_db->exec($queryTmp);
                if (!$modif && $tmpModif > 0) {
                    $modif = true;
                }

                $id_bloc = $this->_db->lastInsertId();
            }
        }

        $this->saveUsedFile($id_version, $id_gab_page, $filesUsed);

        if ($updating) {
            return $modif;
        } else {
            return $id_bloc;
        }
    }

    /**
     * Supprime l'état utilisé de tous les fichiers
     *
     * @param int   $id_version  identifiant de la version
     * @param int   $id_gab_page identifiant de la page
     *
     * @return boolean
     */
    protected function deleteUsedFile($id_version, $id_gab_page)
    {
        $query  = 'DELETE FROM `media_fichier_utilise`'
                . ' WHERE id_gab_page = ' . $id_gab_page
                . ' AND id_version = ' . $id_version;

        $modif = $this->_db->exec($query);

        return ($modif > 0);
    }

    /**
     * Enregistre les fichiers utilisés
     *
     * @param int   $id_version  identifiant de la version
     * @param int   $id_gab_page identifiant de la page
     * @param array $filesUsed   tableau des fichiers utilisés
     *
     * @return boolean
     */
    protected function saveUsedFile($id_version, $id_gab_page, $filesUsed)
    {
        $modif = false;

        foreach ($filesUsed as $file) {
            if ($file != '') {
                $query  = 'INSERT IGNORE INTO `media_fichier_utilise` SET'
                        . ' id_gab_page = ' . $id_gab_page . ','
                        . ' rewriting = ' . $this->_db->quote($file) . ','
                        . ' id_version = ' . $id_version;

                $tmpModif = $this->_db->exec($query);
                if (!$modif && $tmpModif) {
                    $modif = true;
                }
            }
        }

        return $modif;
    }

    /**
     * Permet de prévisualiser une page
     *
     * @param array $donnees données à prévisualiser
     *
     * @return gabaritPage
     */
    public function previsu($donnees)
    {
        $updating = ($donnees['id_gab_page'] > 0);

        if (isset($donnees['id_version'])) {
            $version = $donnees['id_version'];
        } else {
            $version = 1;
        }

        if ($updating) {
            $page = $this->getPage($version, $donnees['id_api'],
                $donnees['id_gab_page'], 0);
        } else {
            $page = $this->getPage($version, $donnees['id_api'], 0,
                $donnees['id_gabarit']);
        }

        $this->previsuMeta($page, $donnees);

        $this->previsuPage($page, $donnees);

        $blocs = $page->getBlocs();
        foreach ($blocs as $blocName => $bloc) {
            $this->previsuBloc($bloc, $donnees);
            $this->getBlocJoinsValues($page, $blocName, ID_VERSION, true);
        }

        if (isset($donnees['id_parent'])) {
            $parents = $this->getParents($donnees['id_parent'], ID_VERSION);
            $page->setParents($parents);
        }

        return $page;
    }

    /**
     * Rempli les meta de prévisualisation
     *
     * @param gabaritPage $page    page
     * @param array       $donnees données à prévisualisée
     *
     * @return void
     */
    protected function previsuMeta($page, $donnees)
    {
        $updating = $donnees['id_gab_page'] > 0;

        /** Insertion dans la table `gab_page` */
        if ($updating) {
            $meta = array(
                'titre'      => $donnees['titre'],
                'bal_title'  => $donnees['bal_title'],
                'bal_key'    => $donnees['bal_key'],
                'author'     => $donnees['author'],
                'bal_descr'  => $donnees['bal_descr'],
            );

            $meta = array_merge($page->getMeta(), $meta);
        } else {
            $meta = array(
                'id_version' => $page->getMeta('id_version'),
                'id_gabarit' => $page->getGabarit()->getId(),
                'titre'      => $donnees['titre'],
                'bal_title'  => $donnees['bal_title'],
                'bal_key'    => $donnees['bal_key'],
                'author'     => $donnees['author'],
                'bal_descr'  => $donnees['bal_descr'],
            );

            if ($donnees['id_temp']) {
                $meta['id'] = 'temp-' . $donnees['id_temp'];
            } else {
                $meta['id'] = 0;
            }

            if (isset($donnees['id_parent']) && $donnees['id_parent']) {
                $meta['id_parent'] = $donnees['id_parent'];
            } else {
                $meta['id_parent'] = 0;
            }

        }

        $page->setMeta($meta);
    }

    /**
     * Rempli les données de contenu de prévisualisation
     *
     * @param gabaritPage $page    page
     * @param array       $donnees données à prévisualisée
     *
     * @return void
     */
    protected function previsuPage($page, $donnees)
    {
        $gabarit = $page->getGabarit();
        $allchamps = $gabarit->getChamps();
        $values = array();

        foreach ($allchamps as $name_group => $champs) {
            foreach ($champs as $champ) {
                if ($champ['visible'] == 0) {
                    continue;
                }

                $value = $donnees['champ' . $champ['id']][0];

                if ($champ['type'] != 'WYSIWYG'
                    && $champ['type'] != 'TEXTAREA'
                ) {
                    $value = str_replace('"', '&quot;', $value);
                }

                if ($champ['typedonnee'] == 'DATE' && $value != '') {
                    $value = \Slrfw\Format\DateTime::frToSql($value);
                }

                $values[$champ['name']] = $value;
            }
        }

        $page->setValues($values);
    }

    /**
     * Rempli les données de contenu des blocs dynamiques de prévisualisation
     *
     * @param gabaritBloc $bloc     bloc
     * @param array       &$donnees données à prévisualisée
     *
     * @return boolean
     */
    protected function previsuBloc($bloc, &$donnees)
    {
        $gabarit = $bloc->getGabarit();
        $champs = $gabarit->getChamps();

        $allvalues = array();

        foreach ($donnees['id_' . $gabarit->getTable()] as $id_bloc) {
            $values = array(
                'id' => 0,
            );

            $visible = array_shift($donnees['visible']);

            foreach ($champs as $champ) {
                if ($champ['visible'] == 0) {
                    continue;
                }

                $value = array_shift($donnees['champ' . $champ['id']]);

                if ($champ['type'] != 'WYSIWYG'
                    && $champ['type'] != 'TEXTAREA'
                ) {
                    $value = str_replace('"', '&quot;', $value);
                }

                if ($champ['typedonnee'] == 'DATE' && $value != '') {
                   $value = \Slrfw\Format\DateTime::frToSql($value);
                }

                $values[$champ['name']] = $value;
            }

            if ($visible) {
                $allvalues[] = $values;
            }
        }

        $bloc->setValues($allvalues);

        return true;
    }
}

