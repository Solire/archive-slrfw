<?php

namespace Slrfw\Model\Gabarit\Field\Autocomplete_multi;

/**
 * Description of JoinField
 *
 * @author shin
 */
class Autocomplete_multiField extends \Slrfw\Model\Gabarit\Field\GabaritField
{

    protected $values;

    public function start()
    {
        if ($this->value != "") {
            /** on recupere les valeurs labels pour les afficher dans le champ */
            $idField = $this->params["TABLE.FIELD.ID"];
            $labelField = $this->params["TABLE.FIELD.LABEL"];
            $table = $this->params["TABLE.NAME"];

            $sql = "SELECT `$table`.`$idField`, `$table`.`$idField` id, `$table`.$labelField label
                    FROM `$table`
                    WHERE  `$table`.`$idField` IN (" . $this->value . ")
                    ORDER BY FIND_IN_SET(id, '" . $this->value . "')";

            $this->valuesUnique = $this->db->query($sql)->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
            $valuesArray = explode(",", $this->value);
            $this->values = array();
            foreach ($valuesArray as $v) {
                $this->values[] = $this->valuesUnique[$v];
            }
            $this->values = htmlentities(json_encode($this->values));

        }
    }

}

