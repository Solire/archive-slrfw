<?php

namespace Slrfw\Model;

/**
 * Description of gabarit
 *
 * @author thomas
 */
class gabarit
{
    private $_data = array();

    private $_table;

    private $_api = array();

    private $_champs = array();

    private $_joins = array();

    private $_gabaritParent = array();

    private $_parents = array();

    public function __construct($row) {
        $this->_data = $row;
    }

    public function setIdParent($id_parent) {
        $this->_data['id_parent'] = $id_parent;
    }

    public function setApi($api) {
        $this->_api = $api;
    }

    public function setName($name) {
        $this->_data['name'] = $name;
    }

    public function setLabel($label) {
        $this->_data['label'] = $label;
    }

    public function setTable($table) {
        $this->_table = $table;
    }

    public function setChamps($champs) {
        $this->_champs = $champs;
    }

    public function setJoins($joins) {
        $this->_joins = $joins;
    }

    public function setGabaritParent($dbRow) {
        $this->_gabaritParent = $dbRow;
    }

    public function setParents($parents) {
        $this->_parents = $parents;
    }

    public function setView($view) {
        $this->_data['view'] = $view;
    }

    public function getId() {
        return $this->_data['id'];
    }

    public function getIdParent() {
        return $this->_data['id_parent'];
    }

    public function getName() {
        return $this->_data['name'];
    }

    public function getMain() {
        return $this->_data['main'];
    }

    public function getCreable() {
        return $this->_data['creable'];
    }

    public function getDeletable() {
        return $this->_data['deletable'];
    }


    /**
     * Renvois une variable de $_data
     *
     * @param string $name Nom de la variable à renvoyer
     *
     * @return mixed
     */
    public function getData($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        return null;
    }

    public function getSortable() {
        return $this->_data['sortable'];
    }

    public function getMake_hidden() {
        return $this->_data['make_hidden'];
    }

    public function getEditable() {
        return $this->_data['editable'];
    }
    
    public function getEditableMiddleOffice() {
        return $this->_data['editable_middle_office'];
    }

    public function getMeta() {
        return $this->_data['meta'];
    }

    public function getExtension() {
        return $this->_data['extension'];
    }

    public function getView() {
        return $this->_data['view'];
    }

    public function get301_editable() {
        return $this->_data['301_editable'];
    }

    public function getMeta_titre() {
        return $this->_data['meta_titre'];
    }

    public function getLabel() {
        return $this->_data['label'];
    }

    public function getTable() {
        return $this->_table;
    }

    public function getApi() {
        return $this->_api;
    }

    public function getChamps() {
        return $this->_champs;
    }

    public function getChamp($name, $bloc = false) {
        foreach ($this->_champs as $champsGroup) {
            if ($bloc) {
                $champsGroup = array($champsGroup);
            }
            foreach ($champsGroup as $champ) {
                if ($champ["name"] == $name) {
                    return $champ;
                }
            }
        }
        return false;
    }

    public function getJoins() {
        return $this->_joins;
    }

    public function getParents()
    {
        return $this->_parents;
    }

    public function getGabaritParent($key = NULL) {
        if ($key == NULL) {
            return $this->_gabaritParent;
        }

        if (isset($this->_gabaritParent[$key])) {
            return $this->_gabaritParent[$key];
        }

        return NULL;
    }
}

