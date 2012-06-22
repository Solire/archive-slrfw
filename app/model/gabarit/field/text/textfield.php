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
        if($this->params["LINK"])
            $this->classes .= " autocomplete-link";
    }
}

?>
