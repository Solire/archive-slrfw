<?php

/**
 * Description of gabaritfield
 *
 * @author shin
 */
abstract class GabaritField
{

    protected $view = "default";
    protected $champ;

    protected $params;
    protected $label;
    protected $value;
    protected $idGabPage;
    protected $uploadPath;
    protected $id;
    protected $versionId;
    protected $classes;
    protected $db;

    public function __construct($champ, $label, $value, $id, $classes, $upload_path, $id_gab_page, $versionId, $db = null)
    {
        if (isset($champ["params"])) {
            $this->params = $champ["params"];
            unset($champ["params"]);
        }
        if ($db)	$this->db = $db;
        else		$this->db = Slrfw\Library\Registry::get("db");

        ;
        ;
        $this->idGabPage = $id_gab_page;
        $this->uploadPath = $upload_path;
        $this->champ = $champ;
        $this->label = $label;
        $this->value = $value;
        $this->id = $id;
        $this->classes = $classes;
        $this->versionId = $versionId;
    }

    public function start()
    {

    }

    public function __toString()
    {
        $rc = new \ReflectionClass(get_class($this));
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
        if($this->champ["aide"]!= "")
            $output .= '<div class"aide" id="aide-champ' . $this->champ['id'] . '" style="display: none">'
                    . $this->champ["aide"]
                    . '</div>';
        return $output;
    }

}

?>
