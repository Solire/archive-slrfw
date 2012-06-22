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

        switch ($this->params["VIEW"]) {
            case "autocomplete" :
                $this->view = "autocomplete";
                $this->autocomplete();
                break;
            case "checkbox" :
                $this->view = "checkbox";
                $this->checkbox();
                break;
        }
    }

    private function autocomplete()
    {
        if ($this->value > 0) {
            // on recupere la valeur label pour lafficher dans le champ
            $idField = $this->params["TABLE.FIELD.ID"];
            $labelField = $this->params["TABLE.FIELD.LABEL"];
            $table = $this->params["TABLE.NAME"];
            $lang = BACK_ID_VERSION;
            $gabPageJoin = "";
            $id = $this->value;

            $filterVersion = "`$table`.id_version = $lang";
            if (isset($_REQUEST["no_version"]) && $_REQUEST["no_version"] == 1)
                $filterVersion = 1;

            if (substr($labelField, 0, 9) == "gab_page.") {
                $gabPageJoin = "INNER JOIN gab_page ON gab_page.id = `$table`.$idField " . ($filterVersion != 1 ? "AND gab_page.id_version = $lang" : "");
                $labelField = $this->params["TABLE.FIELD.LABEL"];
            } else {
                $labelField = "`$table`.`" . $this->params["TABLE.FIELD.LABEL"] . "`";
            }

            $sql = "SELECT $labelField label
                    FROM `$table` 
                    $gabPageJoin
                    WHERE $filterVersion  AND `$table`.`$idField` = $id";
            $this->valueLabel = $this->db->query($sql)->fetch(PDO::FETCH_COLUMN);
        }
    }

    private function checkbox()
    {
            // on recupere les valeurs possibles
            $idField = $this->params["TABLE.FIELD.ID"];
            $labelField = $this->params["TABLE.FIELD.LABEL"];
            $table = $this->params["TABLE.NAME"];
            $lang = BACK_ID_VERSION;
            $gabPageJoin = "";
            $id = $this->value;

            $filterVersion = "`$table`.id_version = $lang";
            if (isset($_REQUEST["no_version"]) && $_REQUEST["no_version"] == 1)
                $filterVersion = 1;

            if (substr($labelField, 0, 9) == "gab_page.") {
                $gabPageJoin = "INNER JOIN gab_page ON gab_page.id = `$table`.$idField " . ($filterVersion != 1 ? "AND gab_page.id_version = $lang" : "");
                $labelField = $this->params["TABLE.FIELD.LABEL"];
            } else {
                $labelField = "`$table`.`" . $this->params["TABLE.FIELD.LABEL"] . "`";
            }

            $sql = "SELECT $labelField label
                    FROM `$table` 
                    $gabPageJoin
                    WHERE $filterVersion";
            $this->allValues = $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            
    }

    
    public function getValueLabel() {
        return $this->valueLabel;
    }
}

?>
