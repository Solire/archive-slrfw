<?php
/**
 * Description of gabaritfield
 *
 * @author shin
 */

namespace Slrfw\Model\Gabarit\FieldSet;

/**
 * Description of gabaritfield
 *
 * @author shin
 */
abstract class GabaritFieldSet
{

    protected $view = 'default';

    protected $gabarit;
    protected $values;
    protected $valueLabel;
    protected $champsHTML;
    protected $uploadPath;
    protected $idGabPage;
    protected $champs;
    protected $versionId;

    /**
     * Constructeur
     *
     * @param \Slrfw\Model\gabaritBloc $bloc        bloc pour lequel on désire
     * contruire le formulaire
     * @param string                   $upload_path chemin où se situe les fichiers
     * téléchargés
     * @param int                      $id_gab_page identifiant de la page
     * contenant le bloc
     * @param int                      $versionId   identifiant de la version
     *
     * @return void
     */
    public function __construct($bloc, $upload_path, $id_gab_page, $versionId)
    {
        $this->gabarit    = $bloc->getGabarit();
        $this->values     = $bloc->getValues();
        $this->champs     = $bloc->getGabarit()->getChamps();
        $this->idGabPage  = $id_gab_page;
        $this->uploadPath = $upload_path;
        $this->versionId  = $versionId;
    }

    /**
     * Initialisation
     *
     * @return void
     */
    public function start()
    {
        if (count($this->values) == 0) {
            $this->values[] = array();
        }
    }

    /**
     * Retourne le formulaire pour le champ
     *
     * @return string
     */
    public function __toString()
    {
        $rc = new \ReflectionClass(get_class($this));
        $view = $this->view;
        $fileName   = dirname($rc->getFileName()) . DIRECTORY_SEPARATOR
                    . 'view/' . $view . '.phtml';
        return $this->output($fileName);
    }

    /**
     * Renvoi le formulaire du bloc
     *
     * @param string $file chemin de la vue à inclure
     *
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
     * Contruit l'élément de formulaire correspondant à un champ
     *
     * @param array  $champ       tableau d'info sur le champ
     * @param string $value       valeur du champ
     * @param string $idpage      identifiant à concatainer à l'attribut 'id' du
     * champ
     * @param string $upload_path nom du dossier où sont uploadés les images.
     * @param int    $id_gab_page nom du dossier dans lequel sont les images.
     *
     * @return string
     */
    protected function _buildChamp(
        $champ, $value, $idpage, $upload_path, $id_gab_page, $gabarit = null
    ) {

        $form = '';
        if($champ['visible'] == 0) {
            return $form;
        }

        $label      = $champ['label'];
        $classes    = 'form-controle '
                    . 'form-' . $champ['oblig'] . ' '
                    . 'form-' . strtolower($champ['typedonnee']);
        $id         = 'champ' . $champ['id']
                    . '_' . $idpage
                    . '_' . $this->versionId;

        if ($champ['typedonnee'] == 'DATE') {
            $value = \Slrfw\Tools::formate_date_nombre($value, '-', '/');
        }

        $type = strtolower($champ['type']);
        $classNameType = '\Slrfw\Model\Gabarit\Field\\' . $type . '\\' . $type . 'field';
        $field = new $classNameType($champ, $label, $value, $id, $classes,
            $upload_path, $id_gab_page, $this->versionId);

        /**
         * Cas pour les bloc dyn de champ join avec un seul champs et de type
         * simple
         */
        if($gabarit != null) {
            $field->start($gabarit);
        } else {
            $field->start();
        }

        $form .= $field;
        if ($type == 'join') {
            $valueLabel = $field->getValueLabel();
        } else {
            if($value != '') {
                if (\mb_strlen($value, 'UTF-8') > 50) {
                    $valueLabel = \mb_substr($value, 0, 50, 'UTF-8') . '&hellip;';
                } else {
                    $valueLabel = $value;
                }
            } else {
                $valueLabel =  'Nouvel élément';
            }
        }

        return array(
            'html'  => $form,
            'label' => $valueLabel,
        );
    }

    /**
     * Construits le formulaire des champs du bloc
     *
     * @param array $value tableau associatif des valeurs des champs
     *
     * @return void
     */
    protected function _buildChamps($value)
    {
        $champHTML = '';
        $first = TRUE;
        foreach ($this->champs as $champ) {
            if (isset($value[$champ['name']])) {
                $value_champ = $value[$champ['name']];
            } else {
                $value_champ = '';
            }

            $id_champ = '';
            if (isset($value['id_version'])) {
                $id_champ = $value['id_version'];
            }

            if (isset($value['id'])) {
                $id_champ .= $value['id'];
            } else {
                $id_champ .= 0;
            }

            $champArray = $this->_buildChamp($champ, $value_champ, $id_champ,
                $this->uploadPath, $this->idGabPage);
            $champHTML .= $champArray['html'];

            if ($first) {
                $first = FALSE;
                $this->valueLabel = $champArray['label'];
            }
        }

        $this->champsHTML = $champHTML;
    }
}

