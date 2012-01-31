<?php
/**
 * Description of gabarit
 *
 * @author thomas
 */
class gabarit
{   
    private $_id;
    
    private $_id_parent;
    
	private $_table;

    private $_name;
    
    private $_label;

	private $_champs = array();
    
    
    /**
     *
     * @var array 
     */
    private $_gabaritParent = array();
    
    private $_parents = array();
    
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
    
    public function setGabaritParent($dbRow) {
        $this->_gabaritParent = $dbRow;
    }
    
    public function setParents($parents) {
        $this->_parents = $parents;
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
    
    public function getGabaritParent($key = NULL) {
        if ($key == NULL)
            return $this->_gabaritParent;
        
        if (array_key_exists($key, $this->_gabaritParent))
            return $this->_gabaritParent[$key];
        
        return NULL;
    }
    
    public function getParentsSelect() {
//        if (count($this->_parents) == 0)
//            return "";
        
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

            $form .= '<option value="' . $parent['id'] . '"' . (isset($this->_meta['id_parent']) && $this->_meta['id_parent'] == $parent['id'] ? ' selected="selected"' : '') . ' class="option3">' . $parent['titre'] . '</option>';
        }

        $form .= '</select>';
        
        return $form;
    }
}