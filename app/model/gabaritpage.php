<?php
/**
 * Description of page
 *
 * @author thomas
 */
class gabaritPage extends gabaritBloc {
    /**
     *
     * @var array
     */
    private $_meta = array();
    
    /**
     *
     * @var array 
     */
    private $_version = array();


    /**
     *
     * @var array
     */
    private $_blocs = array();

    /**
     *
     * @var array 
     */
    private $_parents = array();
    
    /**
     *
     * @param array $meta 
     */
    public function __construct() {   
        $this->_values = array();
    }
    
    
    // SETTERS
    
    /**
     *
     * @param array $meta 
     */
    public function setMeta($meta) {
        $this->_meta = $meta;
        $this->_id = $meta['id'];
    }
    
    public function setVersion($data) {
        $this->_version = $data;
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
     * @param array $values 
     */
    public function setValue($key, $value) {
        $this->_values[$key] = $value;
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
     * @param gabaritPage $child 
     */
    public function setChildren($children) {
        $this->_children= $children;
    }
    
    /**
     *
     * @param gabaritPage $child 
     */
    public function getChildren() {
        return $this->_children;
    }
    
    /**
     *
     * @param gabaritPage $firstChild 
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
    public function getVersion($key = NULL) {
        if ($key != NULL) {
            if (is_array($this->_version) && array_key_exists($key, $this->_version))
                return $this->_version[$key];
            
            return NULL;
        }
        
        return $this->_version;
    }
    
    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function getValues($key = NULL) {
        if ($key == NULL)
            return $this->_values;
        
        if (is_array($this->_values) && array_key_exists($key, $this->_values))
            return $this->_values[$key];
        
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
     * @return gabaritPage 
     */
    public function getParent($i) {
        if (array_key_exists($i, $this->_parents))
            return $this->_parents[$i];
        
        return FALSE;
    }
    
    /**
     *
     * @param int $id_gabarit
     * @return gabaritPage 
     */
    public function getParents() {
        return $this->_parents;
    }
    
    public function getFirstChild(){
        return $this->_firstChild;
    }
    
    /**
     *
     * @param type $mobile
     * @return string 
     */
    public function getForm($action, $retour, $upload_path, $mobile = FALSE, $meta = TRUE, $versionId) {        
        $metaId = isset($this->_meta['id']) ? $this->_meta['id'] : 0;
        $metaLang = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : 1;
        
        $noMeta = !$meta ? ' style="display: none;" ' : '';
        
        $parentSelect = '';
        
        if ($metaId && $this->_meta['id_parent'] > 0) {            
            $parentSelect = '<div class="line">'
                          . '<label for="id_parent">' . $this->_gabarit->getGabaritParent("label") . '</label>'
                          . '<select disabled="disabled"><option>' . $this->getParent(0)->getMeta("titre") . '</option></select>'
                          . '<input type="hidden" disabled="disabled" name="id_parent" value="' . $this->getParent(0)->getMeta("id") . '" />'
                          . '</div>';
        }
        elseif (!$metaId && $this->_gabarit->getIdParent() > 0) {
            $parentSelect = '<div class="line">'
                          . '<label for="id_parent">' . $this->_gabarit->getGabaritParent("label") . '</label>'
                          . $this->_gabarit->getParentsSelect()
                          . '</div>';
        }
        
        $form = '<form action="' . $action . '" method="post" enctype="multipart/form-data">'
		      . '<input type="hidden" name="id_gabarit" value="' . $this->_gabarit->getId() . '" />'
			  . '<input type="hidden" name="id_gab_page" value="' . $metaId . '" />'
			  . '<input type="hidden" name="id_version" value="' . $metaLang . '" />'
              
              . $parentSelect
              . '<div ' . $noMeta . ' class="line">'
              . '<label for="titre-' . $metaLang . '">Titre <span class="required">*</span> </label>'
              . '<input type="text" name="titre" id="titre-' . $metaLang . '" value="' . (isset($this->_meta['titre']) ? $this->_meta['titre'] : '') . '" class="' . ($meta ? 'form-controle form-oblig form-mix' : '') . '" />'
              . '</div>';
        
        if (isset($this->_version['exotique']) && $this->_version['exotique'] > 0) {
            $form .= '<div ' . $noMeta . ' class="line">'
                   . '<label for="titre_rew-' . $metaLang . '">Titre pour le rewriting <span class="required">*</span> </label>'
                   . '<input type="text" name="titre_rew" id="titre_rew-' . $metaLang . '" value="' . (isset($this->_meta['titre_rew']) ? $this->_meta['titre_rew'] : '') . '" class="' . ($meta ? 'form-controle form-oblig form-mix' : '') . '" />'
                   . '</div>';
        }

        $form .= '<fieldset ' . $noMeta . '><legend>Balise Meta</legend><div style="display:none;">'

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

		$form .= $this->buildForm($upload_path, $versionId);
		
		$form .= '<div class="buttonfixed">'
               . ($mobile ? '<div class="btn gradient-green cb fl"><a href="#" class="changemedia">Version mobile</a>' : '')
               . '<div class="btn gradient-green cb fl"><a href="#" class="formajaxsubmit">Valider</a></div>'
               . '<div class="btn gradient-green cb fl"><a href="#" class="uploader_popup">Fichiers</a></div>'
               . '<!--a href="#" class="btn gradient-green formprev fl">Prévisualiser</a-->'
               . '<div class="btn gradient-green cb fl"><a href="' . $retour
               . ($metaId ? '?id_gab_page=' . $metaId : '')
               . '">Retour</a></div>'
               . '</div>'
               . '</form>';
		
		return $form;
	}
	
    /**
     *
     * @return type 
     */
	public function buildForm($upload_path, $versionId) {      
        $form = '<input type="hidden" name="id_' . $this->_gabarit->getTable() . '" value="' . (isset($this->_values['id']) ? $this->_values['id'] : '') . '" />';
        
        $allchamps = $this->_gabarit->getChamps();
        
        $id_gab_page = isset($this->_meta['id']) ? $this->_meta['id'] : 0;
        
        foreach ($allchamps as $name_group => $champs) {
            $form .= '<fieldset><legend>' . $name_group . '</legend><div ' . ($id_gab_page ? 'style="display:none;"' : '') . '>';
            foreach ($champs as $champ) {
                $value = isset($this->_values[$champ['name']]) ? $this->_values[$champ['name']] : '';
                $id = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : '';
                $form .= $this->_buildChamp($champ, $value, $id, $upload_path, $id_gab_page);
            }
            $form .= '</div></fieldset>';
        }
        
        foreach ($this->_blocs as $blocName => $bloc)
            $form .=  $bloc->buildForm($upload_path, $id_gab_page, $versionId);
        
		return $form;
	}
    
}
