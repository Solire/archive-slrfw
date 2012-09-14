<?php

require_once 'gabarit/field/gabaritfield.php';

/**
 * Description of text
 *
 * @author shin
 */
class TextField extends GabaritField
{
    public function start() {
        parent::start();
        if(isset($this->params["LINK"]) && $this->params["LINK"])
            $this->classes .= " autocomplete-link";
    }
}

?>
