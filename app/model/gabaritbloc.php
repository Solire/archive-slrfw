<?php

/**
 * Description of bloc
 *
 * @author thomas
 */
class gabaritBloc
{

    /**
     *
     * @var gabarit 
     */
    protected $_gabarit;

    /**
     *
     * @var array 
     */
    protected $_values = array(array());

    public function __construct()
    {
        
    }

    /**
     *
     * @param gabarit $gabarit 
     */
    public function setGabarit($gabarit)
    {
        $this->_gabarit = $gabarit;
    }

    /**
     *
     * @param array $values 
     */
    public function setValues($values)
    {
        $this->_values = $values;
    }
    
    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function setValue($i, $value, $key = NULL)
    {
        if ($i < 0 || $i >= count($this->_values))
            return false;

        $row = $this->_values[$i];

        if ($key == NULL)
            return $this->_values = $value;

        if (!isset($row[$key]))
            return false;

        return $this->_values[$i][$key] = $value;
    }

    /**
     *
     * @return gabarit 
     */
    public function getGabarit()
    {
        return $this->_gabarit;
    }

    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     *
     * @param string $key
     * @return mixed 
     */
    public function getValue($i, $key = NULL)
    {
        if ($i < 0 || $i >= count($this->_values))
            return NULL;

        $row = $this->_values[$i];

        if ($key == NULL)
            return $row;

        if (!isset($row[$key]))
            return NULL;

        return $row[$key];
    }

    /**
     *
     * @return type 
     */
    public function buildForm($upload_path, $id_gab_page)
    {
        $form = "\n"
                . '<fieldset><legend>' . $this->_gabarit->getName() . '(s)</legend><div class="sort-box">';
        $champs = $this->_gabarit->getChamps();

        foreach ($this->_values as $value) {
            $form .= '<fieldset class="sort-elmt" style="margin-left:30px;"><legend>' . $this->_gabarit->getName() . '</legend><div>'
                    . '<div class="line">'
                    . '<label for="visible-' . $this->_gabarit->getId() . '-' . (isset($value['id']) ? $value['id'] : 0) . '-' . (isset($value['id_version']) ? $value['id_version'] : 1) . '">Visible</label>'
                    . '<input type="checkbox" id="visible-' . $this->_gabarit->getId() . '-' . (isset($value['id']) ? $value['id'] : 0) . '-' . (isset($value['id_version']) ? $value['id_version'] : 1) . '" class="changevisible"' . (isset($value['visible']) && $value['visible'] ? ' checked="checked"' : '') . ' />'
                    . '<input type="hidden" value="' . (isset($value['visible']) && $value['visible'] ? 1 : 0) . '" name="visible[]" />'
                    . '</div><div' . (isset($value['visible']) && $value['visible'] ? '' : ' class="translucide"') . '>'
                    . '<input type="hidden" name="id_' . $this->_gabarit->getTable() . '[]" value="' . (isset($value['id']) ? $value['id'] : '') . '" />';

            foreach ($champs as $champ) {
                $value_champ = isset($value[$champ['name']]) ? $value[$champ['name']] : '';
                $id_champ = (isset($this->_meta['id_version']) ? $this->_meta['id_version'] : '') . (isset($value['id']) ? $value['id'] : 0);
                $form .= $this->_buildChamp($champ, $value_champ, $id_champ, $upload_path, $id_gab_page);
            }

            $form .= '</div></div>'
                    . '<div>'
                    . '<a href="#" class="button bleu delBloc' . ( count($value) > 1 ? '' : ' translucide' )
                    . '" style="float:right;"><span class="bleu"><img src="img/back/supprimer.png" border="0" alt="supprimer"/></span></a>'
                    . '<a href="#" class="button bleu sort-move"><span class="bleu"><img src="img/back/deplacer.png" alt="Déplacer" /></span></a>'
                    . '</div>'
                    . '</fieldset>';
        }

        $form .= '<div class="buttonright"><a class="button bleu addBloc" href="#"><span class="bleu">Ajouter un bloc</span></a></div>';
        $form .= '</div></fieldset>';

        return $form;
    }

    /**
     *
     * @param array $champ
     * @param string $value
     * @param string $idpage
     * @param string $upload_path nom du dossier où sont uploadés les images.
     * @param int $id_gab_page nom du dossier dans lequel sont les images.
     * @return string 
     */
    protected function _buildChamp($champ, $value, $idpage, $upload_path, $id_gab_page)
    {
        
        $form = '';
        if($champ["visible"] == 0)
            return $form;
        $label = $champ['label'];
        $classes = 'form-controle form-' . $champ['oblig'] . ' form-' . strtolower($champ['typedonnee']);
        $id = 'champ' . $champ['id'] . '_' . $idpage;

        if ($champ['typedonnee'] == 'DATE')
            $value = Tools::formate_date_nombre($value, '-', '/');
        
        $type = strtolower($champ['type']);
        $classNameType = $type . "field";
        require_once "gabarit/field/$type/$classNameType.php";
        $field = new $classNameType($champ, $label, $value, $id, $classes);
        $field->start();
        $form .= $field;

        return $form;
    }

}