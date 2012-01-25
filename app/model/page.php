<?php
/**
 * Description of page
 *
 * @author thomas
 */
class page extends bloc {    
    /**
     *
     * @var array
     */
    private $_meta = array();
    
    /**
     *
     * @var array
     */
    private $_blocs = array();

    /**
     *
     * @param array $meta 
     */
    public function __construct($meta = NULL) {
        if ($meta != NULL)
            $this->setMeta ($meta);
        
        $this->_values = array();
    }
    
    
    // SETTERS
    
    /**
     *
     * @param array $meta 
     */
    public function setMeta($meta) {
        $this->_meta = $meta;
    }
    
    /**
     *
     * @param array $values 
     */
    public function setValues($values) {
        $this->_values = $values;
    }
    
    /**
     *
     * @param array $blocs tableau de page 
     */
    public function setBlocs($blocs) {
        $this->_blocs = $blocs;
    }
    
    /**
     *
     * @param array $parents 
     */
    public function setParents($parents) {
        $this->_parents = $parents;
    }
    
    /**
     *
     * @param page $child 
     */
    public function setChildren($children) {
        $this->_children= $children;
    }
    
    /**
     *
     * @param page $firstChild 
     */
    public function setFirstChild($firstChild) {
        $this->_firstChild = $firstChild;
    }
    
    // GETTERS
    
    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function getMeta($key = NULL) {
        if ($key != NULL) {
            if (is_array($this->_meta) && array_key_exists($key, $this->_meta))
                return $this->_meta[$key];
            
            return NULL;
        }
        
        return $this->_meta;
    }
    
    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function getValues($key = NULL) {
        if (is_array($this->_values) && array_key_exists($key, $this->_values))
            return $this->_values[$key];
        
        if ($key == NULL)
            return $this->_values;
        
        return '';
    }
    
    /**
     *
     * @return type 
     */
    public function getBlocs($name = NULL) {
        if ($name == NULL || !isset ($this->_blocs[$name]))
            return $this->_blocs;
        
        return $this->_blocs[$name];
    }  

    /**
     *
     * @param int $id_gabarit
     * @return page 
     */
    public function getParent($id_gabarit) {
        if (array_key_exists($id_gabarit, $this->_parents))
            return $this->_parents[$id_gabarit];
        
        if ($id_gabarit == $this->_gabarit->getId())
            return $this;
        
        return FALSE;
    }
    
    /**
     *
     * @param int $id_gabarit
     * @return page 
     */
    public function getParents() {
        return $this->_parents;
    }
    
    /**
     *
     * @param type $mobile
     * @return string 
     */
    public function getForm($retour, $mobile = FALSE) {
        $metaId = isset($this->_meta['id']) ? $this->_meta['id'] : 0;
        $metaLang = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : 1;
        
        $form = '<form action="page/save.html" method="post" enctype="multipart/form-data">'
		      . '<input type="hidden" name="id_gabarit" value="' . $this->_gabarit->getId() . '" />'
			  . '<input type="hidden" name="id_gab_page" value="' . $metaId . '" />'
			  . '<input type="hidden" name="id_version" value="' . $metaLang . '" />';
        
//        $parents = $this->getParents();
//
//        if ($parents && (!isset($this->_meta['id']) || isset($this->_meta['id_parent']))) {
//
//            $form .= '<div class="line">'
//                   . '<label for="id_parent-' . $metaLang . '">' . $this->_gabarit->getParent("label") . ' parent(e)</label>'
//                   . '<select name="id_parent" '
//                   . ($metaId ? 'disabled="disabled" ' : 'class="controle oblig notnul" ')
//                   . 'id="id_parent-' . $metaLang . '">'
//                   . ($metaId ? '' : '<option value="">Choisissez une ' . $this->_gabarit->getParent("label") . ' parent(e)</option>');
//
//            $idpar = 0;
//            $idparpar = 0;
//            foreach ($parents as $parent) {
//                if ($idparpar != $parent['pp_id']) {
//                    $idparpar = $parent['pp_id'];
//                    $form .= '<option disabled="disabled" class="option1">' . $parent['pp_titre'] . '</option>';
//                }
//
//                if ($idpar != $parent['p_id']) {
//                    $idpar = $parent['p_id'];
//                    $form .= '<option disabled="disabled" class="option2">' . $parent['p_titre'] . '</option>';
//                }
//
//                $form .= '<option value="' . $parent['id'] . '"' . (isset($this->_meta['id_parent']) && $this->_meta['id_parent'] == $parent['id'] ? ' selected="selected"' : '') . ' class="option3">' . $parent['titre'] . '</option>';
//            }
//
//            $form .= '</select></div>';
//        }
			
        $form .= '<div class="line">'
               . '<label for="titre-' . $metaLang . '">Titre</label>'
               . '<input type="text" name="titre" id="titre-' . $metaLang . '" value="' . (isset($this->_meta['titre']) ? $this->_meta['titre'] : '') . '" class="form-controle form-oblig form-mix" />'
               . '</div>'

               . '<fieldset><legend>Balise Meta</legend><div style="display:' . (/*$metaId ? 'block' : */'none') . ';">'

               . '<div class="line">'
               . '<label for="rewriting-' . $metaLang . '">Rewriting</label>'
               . '<input type="text" name="rewriting" id="rewriting-' . $metaLang . '" value="' . (isset($this->_meta['rewriting']) ? $this->_meta['rewriting'] : '') . '" disabled="disabled" />'
               . '</div>'

               . '<div class="line">'
               . '<label for="bal_title-' . $metaLang . '">Title</label>'
               . '<input type="text" name="bal_title" id="bal_title-' . $metaLang . '" value="' . (isset($this->_meta['bal_title']) ? $this->_meta['bal_title'] : '') . '" size="80" maxlength="80" />'
               . '</div>'

               . '<div class="line">'
               . '<label for="bal_descr-' . $metaLang . '">Description</label>'
               . '<input type="text" name="bal_descr" id="bal_descr-' . $metaLang . '" value="' . (isset($this->_meta['bal_descr']) ? $this->_meta['bal_descr'] : '') . '" size="80" maxlength="250" />'
               . '</div>'

               . '<div class="line">'
               . '<label for="bal_key-' . $metaLang . '">Keywords (<i>séparés par des ,</i>)</label>'
               . '<input type="text" name="bal_key" id="bal_key-' . $metaLang . '" value="' . (isset($this->_meta['bal_key']) ? $this->_meta['bal_key'] : '') . '" size="80" maxlength="250" />'
               . '</div>'

               . '<div class="line">'
               . '<label for="importance-' . $metaLang . '">Importance (<i>de 0,1 à 0,9</i>)</label>'
               . '<select name="importance" id="importance-' . $metaLang . '">';

        for ($ii = 1 ; $ii < 10 ; $ii++)
            $form .= '<option value="' . $ii . '"' . (isset($this->_meta['importance']) && $ii == $this->_meta['importance'] ? ' selected="selected"' : '') . '>' . $ii . '</option>';

        $form .= '</select>'
               . '</div>'

               . '<div class="line">'
               . '<label for="no_index' . $metaLang . '">No-index</label>'
               . '<input type="checkbox" name="no_index" id="no_index' . $metaLang . '"' . (isset($this->_meta['no_index']) && $this->_meta['no_index'] > 0 ? ' checked="checked"' : '') . ' />'
               . '</div>'

               . '</div>'
               . '</fieldset>';


		$form .= $this->buildForm();
		
		$form .= '<div class="buttonfixed">'
               . ($mobile ? '<a href="#" class="button bleu changemedia"><span class="bleu">Version mobile</span></a>' : '')
               . '<a href="#" class="button vert formajaxsubmit" style="clear:both;"><span class="vert">Valider</span></a>'
               . '<a href="#" class="button vert uploader_popup" style="clear:both;"><span class="vert">Fichiers</span></a>'
               . '<!--a href="#" class="button vert formprev" style="clear:both;"><span class="vert">Prévisualiser</span></a-->'
               . '<a href="' . $retour
               . ($metaId ? '?id_gab_page=' . $metaId : '')
               . '" class="button vert" style="clear:both;"><span class="vert">Retour</span></a>'
               . '</div>'
               . '</form>';
		
		return $form;
	}
	
    /**
     *
     * @return type 
     */
	public function buildForm() {      
        $form = '<input type="hidden" name="id_' . $this->_gabarit->getTable() . '" value="' . (isset($this->_values['id']) ? $this->_values['id'] : '') . '" />';
        
        $champs = $this->_gabarit->getChamps();
        
        foreach ($champs as $champ) {
            $value = isset($this->_values[$champ['name']]) ? $this->_values[$champ['name']] : '';
            $id = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : '';
            $form .= $this->_buildChamp($champ, $value, $id);
        }
        
        foreach ($this->_blocs as $blocName => $bloc)
            $form .=  $bloc->buildForm();
        
		return $form;
	}
    
}