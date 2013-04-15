<?php

namespace Slrfw\Model\Gabarit\Field\Text;

/**
 * Description of text
 *
 * @author shin
 */
class TextField extends \Slrfw\Model\Gabarit\Field\GabaritField
{
    public function start() {
        parent::start();
        if(isset($this->params["LINK"]) && $this->params["LINK"])
            $this->classes .= " autocomplete-link";
    }
}

