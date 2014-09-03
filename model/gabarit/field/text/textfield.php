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

        // Prise en compte de la valeur par défaut paramétrée
        if(isset($this->params["VALUE.DEFAULT"]) 
            && $this->params["VALUE.DEFAULT"]
            && $this->idGabPage == 0
        ) {
            $this->value = $this->params["VALUE.DEFAULT"];
        }

        if(isset($this->params["LINK"]) && $this->params["LINK"])
            $this->classes .= " autocomplete-link";
    }
}

