<?php

namespace Slrfw\Model;

/**
 * Description of page
 *
 * @author thomas
 */
class gabaritPage extends gabaritBloc
{
    /**
     * Est-ce que l'utilisateur est connecté
     *
     * @var bool
     */
    private $_connected = false;

    /**
     * Tableau des données meta de la page
     *
     * @var array
     */
    protected $_meta = array();

    /**
     * Tableau des données de la version de la page
     *
     * @var array
     */
    protected $_version = array();

    /**
     * Tableau des blocs dynamiques de la page
     *
     * @var array
     */
    protected $_blocs = array();

    /**
     * Tableau des pages parentes
     *
     * @var array
     */
    protected $_parents = array();

    /**
     * Tableau des pages enfants
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Première page enfant
     *
     * @var gabaritPage
     */
    protected $_firstChild = null;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->_values = array();
    }

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
        foreach ($this->_blocs as $bloc) {
            $bloc->setConnected($connected);
        }
    }

    /**
     * Setter des métas
     *
     * @param array $meta
     *
     * @return void
     */
    public function setMeta($meta)
    {
        $this->_meta = $meta;
        if (isset($meta['id'])) {
            $this->_id = $meta['id'];
        }
    }

    /**
     * Setter de la version
     *
     * @param array $data
     *
     * @return void
     */
    public function setVersion($data)
    {
        $this->_version = $data;
    }

    /**
     * Setter des valeurs
     *
     * @param array $values
     *
     * @return void
     */
    public function setValues($values)
    {
        $this->_values = $values;
    }

    /**
     * Setter d'une valeur
     *
     * @param array $values
     *
     * @return void
     */
    public function setValue($key, $value)
    {
        $this->_values[$key] = $value;
    }

    /**
     * Setter des blocs de la page
     *
     * @param gabaritBloc[] $blocs tableau de page
     *
     * @return void
     */
    public function setBlocs($blocs)
    {
        $this->_blocs = $blocs;
        foreach ($this->_blocs as $bloc) {
            $bloc->setConnected($this->_connected);
        }
    }

    /**
     * Setter des pages parentes
     *
     * @param gabaritPage[] $parents
     *
     * @return void
     */
    public function setParents($parents)
    {
        $this->_parents = $parents;
    }

    /**
     * Setter des pages enfants
     *
     * @param gabaritPage[] $children
     *
     * @return void
     */
    public function setChildren($children)
    {
        if (count($children) >  0) {
            $this->_firstChild = $children[0];
        }
        $this->_children= $children;
    }

    /**
     * Getter des pages enfants
     *
     * @param gabaritPage $child
     *
     * @return gabaritPage[]
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Setter de la premiere page enfant
     *
     * @param gabaritPage $firstChild
     *
     * @return void
     */
    public function setFirstChild($firstChild)
    {
        $this->_firstChild = $firstChild;
    }

    /**
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getMeta($key = null)
    {
        if ($key != null) {
            if (is_array($this->_meta)
                && isset($this->_meta[$key])
            ) {
                return $this->_meta[$key];
            }

            return null;
        }

        return $this->_meta;
    }

    /**
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getVersion($key = null)
    {
        if ($key != null) {
            if (is_array($this->_version)
                && isset($this->_version[$key])
            ) {
                return $this->_version[$key];
            }

            return null;
        }

        return $this->_version;
    }

    /**
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValues($key = null)
    {
        if ($key != null) {
            if (is_array($this->_values)
                && isset($this->_values[$key])
            ) {
                return $this->_values[$key];
            }

            return '';
        }

        return $this->_values;
    }

    /**
     *
     * @param string $key
     *
     * @return string
     */
    public function getEditableAttributes($key)
    {
        if (!$this->_connected) {
            return '';
        }

        $field = $this->getGabarit()->getChamp($key);
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
            return ' data-mercury="' . $type . '" id="champ' . $field["id"] . '" ';
        }

        return '';
    }

    /**
     *
     * @return type
     */
    public function getBlocs($name = null)
    {
        if ($name == null || !isset ($this->_blocs[$name]))
            return $this->_blocs;

        return $this->_blocs[$name];
    }

    /**
     *
     * @param int $id_gabarit
     * @return gabaritPage
     */
    public function getParent($i)
    {
        if (array_key_exists($i, $this->_parents))
            return $this->_parents[$i];

        return FALSE;
    }

    /**
     *
     * @param int $id_gabarit
     * @return gabaritPage
     */
    public function getParents()
    {
        return $this->_parents;
    }

    /**
     * Retourne la première page enfant
     *
     * @return gabaritPage
     */
    public function getFirstChild()
    {
        return $this->_firstChild;
    }

    /**
     * Retourne le formulaire de création/d'édition de la page
     *
     * @param string $action        adresse de l'action du formulaire
     * @param string $retour        adresse de retour
     * @param array  $redirections  tableau des redirections
     *
     * @return string formulaire au format HTML
     */
    public function getForm(
        $action,
        $retour,
        $redirections = array(),
        $authors = array()
    ) {
        $versionId          = $this->_version['id'];

        $metaId             = isset($this->_meta['id'])
                            ? $this->_meta['id']
                            : 0;
        $metaLang           = isset($this->_meta['id_version'])
                            ? $this->_meta['id_version']
                            : BACK_ID_VERSION;
        $noMeta             = !$this->_gabarit->getMeta() || !$metaId
                            ? ' style="display: none;" '
                            : '';
        $noMetaTitre        = !$this->_gabarit->getMeta_titre()
                            ? ' style="display: none;" '
                            : '';
        $noRedirections301  = !$this->_gabarit->get301_editable()
                            ? ';display: none'
                            : '';
        $parentSelect       = '';

        $api = $this->_gabarit->getApi();

        $redirections   = count($redirections) == 0
                        ? array("")
                        : $redirections;

        $allchamps = $this->_gabarit->getChamps();

        ob_start();
        $customForm = \Slrfw\FrontController::search('model/gabarit/form/default/default.phtml', false);

        if ($customForm !== false) {
            include $customForm;
        } else {
            include __DIR__ . '/gabarit/form/default/default.phtml';
        }

        $form = ob_get_clean();

		return $form;
	}

    /**
     * Inclut le sélecteur des parents
     *
     * @return void
     */
    public function selectParents()
    {
        $path = '/gabarit/form/default/selectparents.phtml';

        $customForm = \Slrfw\FrontController::search('model' . $path, false);

        if ($customForm !== false) {
            include $customForm;
        } else {
            include __DIR__ . $path;
        }
    }

    /**
     *
     *
     * @return type
     */
    public function buildForm()
    {
        $form   = '<input type="hidden" name="id_' . $this->_gabarit->getTable()
                . '" value="' . (isset($this->_values['id']) ? $this->_values['id'] : '')
                . '" />';

        $allchamps = $this->_gabarit->getChamps();

        $id_gab_page = isset($this->_meta['id']) ? $this->_meta['id'] : 0;

        foreach ($allchamps as $name_group => $champs) {
            $form  .= '<fieldset><legend>' . $name_group . '</legend>'
                    . '<div ' . ($id_gab_page ? 'style="display:none;"' : '') . '>';
            foreach ($champs as $champ) {
                $value = isset($this->_values[$champ['name']]) ? $this->_values[$champ['name']] : '';
                $id = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : '';
                $form .= $this->_buildChamp($champ, $value, $id, $id_gab_page, $id);
            }
            $form .= '</div></fieldset>';
        }

        foreach ($this->_blocs as $blocName => $bloc) {
            $form .=  $bloc->buildForm($id_gab_page,
                $this->_version['id']);
        }

		return $form;
	}
}
