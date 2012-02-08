<?php

/**
 * Description of gabaritfield
 *
 * @author shin
 */
abstract class GabaritField
{

    protected $champ;
    protected $params;
    protected $label;
    protected $value;
    protected $id;
    protected $classes;

    public function __construct($champ, $label, $value, $id, $classes)
    {
        if (isset($champ["params"])) {
            $this->params = $champ["params"];
            unset($champ["params"]);
        }

        $this->champ = $champ;
        $this->label = $label;
        $this->value = $value;
        $this->id = $id;
        $this->classes = $classes;
    }

    public function start()
    {
        
    }

    public function __toString()
    {
        $rc = new ReflectionClass(get_class($this));
        return $this->output(dirname($rc->getFileName()) . DIRECTORY_SEPARATOR . "view/default.phtml");
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

}

?>
