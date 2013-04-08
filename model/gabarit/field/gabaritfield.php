<?php

namespace Slrfw\Model\Gabarit\Field;

/**
 * Description of gabaritfield
 *
 * @author shin
 */
abstract class GabaritField
{
    /**
     * Nom de la vue utilisée
     *
     * @var string
     */
    protected $view = "default";

    /**
     * Données du champ (nom, label, type...)
     *
     * @var array
     */
    protected $champ;

    /**
     * Paramètre additif du champ
     * <ul>
     * <li><i>table</i> pour <b>jointure</b></li>
     * <li><i>largeur</i>, <i>hauteur</i> pour <b>image</b></li>
     * <li>...</li>
     * </ul>
     *
     * @var array
     */
    protected $params;

    /**
     * Nom du label
     *
     * @var string
     */
    protected $label;

    /**
     * Valeur du champ
     *
     * @var string
     */
    protected $value;

    /**
     * Identifiant de la page
     *
     * @var int
     */
    protected $idGabPage;

    /**
     * Identifiant du champ
     *
     * @var int
     */
    protected $id;

    /**
     * Identifiant de la version
     *
     * @var int
     */
    protected $versionId;

    /**
     * Classes html de l'attribut
     *
     * @var string
     */
    protected $classes;

    /**
     * Connection à la BDD
     *
     * @var \Slrfw\MyPDO
     */
    protected $db;

    public function __construct(
        $champ,
        $label,
        $value,
        $id,
        $classes,
        $id_gab_page,
        $versionId,
        $db = null
    ) {
        if (isset($champ["params"])) {
            $this->params = $champ["params"];
            unset($champ["params"]);
        }
        if ($db)	$this->db = $db;
        else		$this->db = \Slrfw\Registry::get("db");

        $this->idGabPage = $id_gab_page;
        $this->champ = $champ;
        $this->label = $label;
        $this->value = $value;
        $this->id = $id;
        $this->classes = $classes;
        $this->versionId = $versionId;
    }

    /**
     * Toujours exécuté au début
     *
     * @return void
     */
    public function start()
    {}

    /**
     * Renvoi le code HTML
     *
     * @return string
     */
    public function __toString()
    {
        $rc = new \ReflectionClass(get_class($this));
        $view = $this->view;
        $viewFile   = dirname($rc->getFileName()) . DIRECTORY_SEPARATOR
                    . 'view/' . $view . '.phtml';
        return $this->output($viewFile);
    }

    /**
     * Renvoi le contenu dynamisé d'une vue
     *
     * @param string $file chemin de la vue à inclure
     *
     * @return string Rendu de la vue après traitement
     */
    public function output($viewFile)
    {
        ob_start();
        include($viewFile);
        $output = ob_get_clean();

        if ($this->champ["aide"] != '') {
            $output    .= '<div class"aide" id="aide-champ' . $this->champ['id']
                        . '" style="display: none">' . $this->champ["aide"]
                        . '</div>';
        }

        return $output;
    }

}

