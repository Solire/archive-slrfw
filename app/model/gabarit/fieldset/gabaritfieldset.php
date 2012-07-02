<?php

/**
 * Description of gabaritfield
 *
 * @author shin
 */
abstract class GabaritFieldSet
{

    protected $view = "default";
    
    protected $gabarit;
    protected $values;
    protected $valueLabel;
    protected $champsHTML;
    protected $uploadPath;
    protected $idGabPage;
    protected $champs;
    protected $meta;
    protected $versionId;
    protected $db;

    public function __construct($gabarit,  $champs, $values, $upload_path, $id_gab_page, $meta, $versionId,  $db = null)
    {
        $this->gabarit = $gabarit;
        if(count($values) == 0)
            $values[0] = array();
        $this->values = $values;
        $this->champs = $champs;
        $this->idGabPage = $id_gab_page;
        $this->uploadPath = $upload_path;
        $this->meta = $meta;
        $this->versionId = $versionId;
    }

    public function start()
    {
        
    }

    public function __toString()
    {
        $rc = new ReflectionClass(get_class($this));
        $view = $this->view;
        return $this->output(dirname($rc->getFileName()) . DIRECTORY_SEPARATOR . "view/$view.phtml");
    }

    /**
     *
     * @param type $file chemin de la vue à inclure
     * @return string Rendu de la vue après traitement 
     */
    public function output($file)
    {
        ob_start();
        include($file);
        $output = ob_get_clean();
        return $output;
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
    protected function _buildChamp($champ, $value, $idpage, $upload_path, $id_gab_page, $gabarit = null)
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
        $field = new $classNameType($champ, $label, $value, $id, $classes, $upload_path, $id_gab_page, $this->versionId);
        //Cas pour les bloc dyn de champ join avec un seul champs et de type simple
        if($gabarit != null) {
            $field->start($gabarit);
        } else {
            $field->start();
        }
        
        $form .= $field;

        if ($type == "join")
            $valueLabel = $field->getValueLabel();
        else
            $valueLabel = $value;
        
        return array(
            'html'  => $form,
            'label' => $valueLabel,
        );
    }
    
    protected function _buildChamps($value)
    {
        $champHTML = '';
        $first = TRUE;
        foreach ($this->champs as $champ) {
            $value_champ = isset($value[$champ['name']]) ? $value[$champ['name']] : '';
            $id_champ = (isset($value['id_version']) ? $value['id_version'] : '') . (isset($value['id']) ? $value['id'] : 0);
            $champArray = $this->_buildChamp($champ, $value_champ, $id_champ, $this->uploadPath, $this->idGabPage);
            $champHTML .= $champArray['html'];
            
            if ($first) {
                $first = FALSE;
                $this->valueLabel = $champArray['label'];
            }
        }
        
        $this->champsHTML = $champHTML;
    }
    
    

}

?>
