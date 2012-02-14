<?php

require_once 'gabarit/field/gabaritfield.php';

/**
 * Description of JoinField
 *
 * @author shin
 */
class JoinField extends GabaritField
{

    protected $valueLabel;

    public function start()
    {
        if ($this->value > 0) {
            // on recupere la valeur label pour lafficher dans le champ
            $idField = $this->params["TABLE.FIELD.ID"];
            $labelField = $this->params["TABLE.FIELD.LABEL"];
            $table = $this->params["TABLE.NAME"];
            ;
            $lang = BACK_ID_VERSION;
            $id = $this->value;

            $sql = "SELECT `$table`.$labelField
                    FROM `$table` 
                    WHERE id_version = $lang AND `$table`.`$idField` = $id";
            $this->valueLabel = $this->db->query($sql)->fetch(PDO::FETCH_COLUMN);
        }
    }

}

?>
