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
     * Est-ce que l'utilisateur est connecté
     *
     * @var bool
     */
    private $_connected = false;

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

    /**
     * Constructeur
     */
    public function __construct()
    {}

    /**
     * Défini si l'utilisateur est connecté (utile en cas de middleoffice)
     *
     * @param bool $connected
     *
     * @return void
     */
    public function setConnected($connected)
    {
        $this->_connected = $connected;
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
    public function setValue($i, $value, $key = null)
    {
        if ($i < 0 || $i >= count($this->_values)) {
            return false;
        }

        if ($key == null) {
            return $this->_values[$i] = $value;
        }

        return $this->_values[$i][$key] = $value;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function deleteValue($i)
    {
        unset($this->_values[$i]);
        $this->_values = array_values($this->_values);
        return ;
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
    public function getEditableAttributes($key, $id)
    {
        if (!$this->_connected) {
            return '';
        }

        $field = $this->getGabarit()->getChamp($key, true);
        if (!$field) {
            return '';
        }

        $type = '';
        switch ($field['type']) {
            case 'WYSIWYG':
                $type = 'full';
                break;

            case 'FILE':
                $type = 'image';
                break;

            case 'TEXT':
                $type = 'simple';
                break;

            case 'TEXTAREA':
                $type = 'textarea';
                break;
        }

        if ($type != '') {
            $string = ' data-mercury="' . $type . '" id="champ' . $field["id"]
                    . '-' . $id . '-' . $this->getGabarit()->getTable() . '" ';
            return $string;
        }

        return '';
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($i, $key = null)
    {
        if ($i < 0 || $i >= count($this->_values)) {
            return null;
        }

        $row = $this->_values[$i];

        if ($key == null) {
            return $row;
        }

        if (!isset($row[$key])) {
            return null;
        }

        return $row[$key];
    }

    /**
     * @return string élément de formulaire en HTML
     */

    /**
     * Retourne l'élément d'un formulaire en HTML correspondant à ce bloc dynamique
     *
     * @param string $idGabPage
     * @param int    $versionId
     *
     * @return string élément de formulaire en HTML
     */
    public function buildForm($idGabPage, $versionId)
    {
        $form = '';

        $champs = $this->_gabarit->getChamps();

        $type = 'Defaut';

        /** Récupération du type.phtml bloc si présent **/
        $blocType = $this->_gabarit->getData('type');
        if (!empty($blocType)) {
            $type = $blocType;
        }

        if (count($champs) == 1 && $champs[0]['type'] == 'JOIN'
            && $champs[0]['params']['VIEW'] == 'simple') {
            $type = strtolower('simple');
        }

        $className = 'Model\\Gabarit\\Fieldset\\' . ucfirst($type) . '\\' . ucfirst($type);
        $className = \Slrfw\FrontController::searchClass($className);


        if ($className === false) {
            $className = '\Slrfw\Model\Gabarit\Fieldset\\' . $type . '\\' . $type . 'fieldset';
        }

        $fieldset = new $className($this, $idGabPage, $versionId);
        $fieldset->start();
        $form .= $fieldset->toString();

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
     * @param int    $id_gab_page nom du dossier dans lequel sont les images
     *
     * @return string élément de formulaire en HTML
     */
    protected function _buildChamp($champ, $value, $idpage, $id_gab_page, $id_version = 1)
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
        $classNameType = '\Slrfw\Model\Gabarit\Field\\' . $type . '\\' . $type . 'field';
        $field = new $classNameType($champ, $label, $value, $id, $classes, $id_gab_page, $id_version);
        $field->start();
        $form .= $field;

        return $form;
    }
}

