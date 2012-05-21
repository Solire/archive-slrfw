<?php

require_once 'gabarit/field/gabaritfield.php';

/**
 * Description of text
 *
 * @author shin
 */
class SelectField extends GabaritField
{
    public function start()
    {
        if($this->params["VALUES"] != "")
            $this->values =  explode("|+|", $this->params["VALUES"]);
    }
}

?>
