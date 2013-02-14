<?php

namespace Slrfw\Model;

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
    protected $_values = array();

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
     * @return string élément de formulaire en HTML
     */
    
    /**
     * Retourne l'élément d'un formulaire en HTML correspondant à ce bloc dynamique
     * 
     * @param string $upload_path
     * @param string $id_gab_page
     * @param int    $versionId
     * 
     * @return string élément de formulaire en HTML
     */
    public function buildForm($upload_path, $id_gab_page, $versionId)
    {
        $form = '';

        $champs = $this->_gabarit->getChamps();

        $type = strtolower('default');

        if (count($champs) == 1 && $champs[0]['type'] == 'JOIN'
            && $champs[0]['params']['VIEW'] == 'simple') {
            $type = strtolower('simple');
        }

        $classNameType = $type . 'fieldset';

        require_once 'gabarit/fieldset/' . $type . '/' . $classNameType . '.php';        
        $fieldset = new $classNameType($this, $upload_path, $id_gab_page, $versionId);
        $fieldset->start();
        $form .= $fieldset;

        return $form;
    }

    /**
     * Retourne l'élément d'un formulaire en HTML correspondant à un champ
     * 
     * @param array  $champ       données du champ (ligne en BDD dans la table
     * 'gab_champ')
     * @param string $value       valeur du champ
     * @param string $idpage      chaîne à concatainer à l'attribut 'id' de
     * l'élément du formulaire
     * @param string $upload_path nom du dossier où sont uploadés les images
     * @param int    $id_gab_page nom du dossier dans lequel sont les images
     * 
     * @return string élément de formulaire en HTML
     */
    protected function _buildChamp($champ, $value, $idpage, $upload_path, $id_gab_page)
    {
        $form = '';

        if($champ["visible"] == 0) {
            return $form;
        }

        $label = $champ['label'];
        $classes = 'form-controle form-' . $champ['oblig'] . ' form-' . strtolower($champ['typedonnee']);
        $id = 'champ' . $champ['id'] . '_' . $idpage;

        if ($champ['typedonnee'] == 'DATE') {
            $value = \Slrfw\Tools::formate_date_nombre($value, '-', '/');
        }
        
        $type = strtolower($champ['type']);
        $classNameType = $type . "field";
        require_once "gabarit/field/$type/$classNameType.php";
        $field = new $classNameType($champ, $label, $value, $id, $classes, $upload_path, $id_gab_page, 0);
        $field->start();
        $form .= $field;

        return $form;
    }
}

