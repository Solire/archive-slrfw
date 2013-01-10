<?php

namespace Slrfw\Model;

/**
 * Description of gabarit
 *
 * @author thomas
 */
class gabarit
{
    private $_id;

    private $_id_parent;

    private $_api;

    private $_table;

    private $_name;

    private $_main;

    private $_creable;

    private $_deletable;

    private $_sortable;

    private $_make_hidden;

    private $_editable;

    private $_meta;

    private $_label;

    private $_301_editable;

    private $_meta_titre;

    private $_extension;

    private $_champs = array();

    private $_joins = array();


    /**
     *
     * @var array
     */
    private $_gabaritParent = array();

    private $_parents = array();

    public function __construct($id = 0, $id_parent = 0, $name = '', $label = '', $main = TRUE, $creable = TRUE, $deletable = TRUE, $sortable = TRUE, $make_hidden = TRUE, $editable = TRUE, $meta = TRUE, $_301_editable = TRUE, $meta_titre = TRUE, $extension = "/") {
        $this->_id = $id;
        $this->_id_parent = $id_parent;
        $this->_name = $name;
        $this->_main = $main;
        $this->_creable = $creable;
        $this->_deletable = $deletable;
        $this->_sortable = $sortable;
        $this->_make_hidden = $make_hidden;
        $this->_editable = $editable;
        $this->_301_editable = $_301_editable;
        $this->_meta_titre = $meta_titre;
        $this->_extension = $extension;
        $this->_meta = $meta;
        $this->_label = $label;
    }

    public function setIdParent($id_parent) {
        $this->_id_parent = $id_parent;
    }

    public function setApi($api) {
        $this->_api = $api;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function setLabel($label) {
        $this->_label = $label;
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





    public function getId() {
        return $this->_id;
    }

    public function getApi() {
        return $this->_api;
    }

    public function getIdParent() {
        return $this->_id_parent;
    }

    public function getName() {
        return $this->_name;
    }

    public function getMain() {
        return $this->_main;
    }

    public function getCreable() {
        return $this->_creable;
    }

    public function getDeletable() {
        return $this->_deletable;
    }

    public function getSortable() {
        return $this->_sortable;
    }

    public function getMake_hidden() {
        return $this->_make_hidden;
    }

    public function getEditable() {
        return $this->_editable;
    }

    public function getMeta() {
        return $this->_meta;
    }

    public function getExtension() {
        return $this->_extension;
    }

    public function get301_editable() {
        return $this->_301_editable;
    }

    public function getMeta_titre() {
        return $this->_meta_titre;
    }

    public function getLabel() {
        return $this->_label;
    }

    public function getTable() {
        return $this->_table;
    }

    public function getChamps() {
        return $this->_champs;
    }

    public function getJoins() {
        return $this->_joins;
    }

    public function getGabaritParent($key = NULL) {
        if ($key == NULL)
            return $this->_gabaritParent;

        if (array_key_exists($key, $this->_gabaritParent))
            return $this->_gabaritParent[$key];

        return NULL;
    }

    public function getParentsSelect() {
        $form = '<select name="id_parent" id="id_parent-1"'
              . ($this->_gabaritParent['id'] != $this->_id ? ' class="form-controle form-oblig form-notnul"' : '')
              . '><option value="0">---</option>';

        $idpar = 0;
        $idparpar = 0;
        foreach ($this->_parents as $parent) {
            if ($idparpar != $parent['pp_id']) {
                $idparpar = $parent['pp_id'];
                $form .= '<option disabled="disabled" class="option1">' . $parent['pp_titre'] . '</option>';
            }

            if ($idpar != $parent['p_id']) {
                $idpar = $parent['p_id'];
                $form .= '<option disabled="disabled" class="option2">' . $parent['p_titre'] . '</option>';
            }

            $selected = '';

            if(count($this->_parents) == 1)
                $selected = 'selected="selected"';

            $form .= '<option ' . $selected . ' value="' . $parent['id'] . '"' . (isset($this->_meta['id_parent']) && $this->_meta['id_parent'] == $parent['id'] ? ' selected="selected"' : '') . ' class="option3">' . $parent['titre'] . '</option>';
        }

        $form .= '</select>';

        return $form;
    }
}