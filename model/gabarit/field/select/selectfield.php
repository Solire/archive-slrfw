<?php

namespace Slrfw\Model\Gabarit\Field\Select;

/**
 * Description of text
 *
 * @author shin
 */
class SelectField extends \Slrfw\Model\Gabarit\Field\GabaritField
{
    public function start()
    {
        if($this->params["VALUES"] != "")
            $this->values =  explode("|+|", $this->params["VALUES"]);
    }
}

