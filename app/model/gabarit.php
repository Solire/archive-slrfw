<?php
/**
 * Description of gabarit
 *
 * @author thomas
 */
class gabarit
{   
    private $_id_parent;
    
	private $_table;

    private $_name;
    
    private $_label;

	private $_champs = array();
    
    public function __construct($id = 0, $id_parent = 0, $name = '', $label = '') {
        $this->_id = $id;
        $this->_id_parent = $id_parent;
        $this->_name = $name;
        $this->_label = $label;
    }
    
    public function setIdParent($id_parent) {
        $this->_id_parent = $id_parent;
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
    
    public function getId() {
        return $this->_id;
    }
    
    public function getIdParent() {
        return $this->_id_parent;
    }
    
    public function getName() {
        return $this->_name;
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
}