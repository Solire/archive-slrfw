<?php


namespace Slrfw\Model;

/*
 * Author: MONNOT Stéphane
 *
 * Create Date: 03-02-2011
 *
 *
 *
 *
 */

Class Search {

    private $_results;
    private $_nbResults;
    private $_sort = Array("prix_hc+prix_cc");
    private $_location;
    private $_budgetMin;
    private $_budgetMax;
    private $_surfaceMin;
    private $_surfaceMax;
    private $_type;
    private $_etage;
    private $_nature;
    private $_typeVente;
    private $_secteur;
    private $_typeSocial;
    private $_budgetSlice;

    public function __construct() {

    }

    public function get_location() {
        return $this->_location;
    }

    public function get_nbResults() {
        return $this->_nbResults;
    }

    public function set_location($_location) {
        $this->_location = filter_var(trim($_location), FILTER_SANITIZE_STRING);
    }

    public function get_budgetMin() {
        return intval($this->_budgetMin) > 0 ? intval($this->_budgetMin) : null;
    }

    public function set_budgetMin($_budgetMin) {
        $this->_budgetMin = filter_var($_budgetMin, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_budgetMax() {
        return intval($this->_budgetMax) > 0 ? intval($this->_budgetMax) : null;
    }

    public function set_budgetMax($_budgetMax) {
        $this->_budgetMax = filter_var($_budgetMax, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_surfaceMin() {
        return intval($this->_surfaceMin) > 0 ? intval($this->_surfaceMin) > 0 : null;
    }

    public function set_surfaceMin($_surfaceMin) {
        $this->_surfaceMin = filter_var($_surfaceMin, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_surfaceMax() {
        return intval($this->_surfaceMax) > 0 ? intval($this->_surfaceMax) : null;
    }

    public function set_surfaceMax($_surfaceMax) {
        $this->_surfaceMax = filter_var($_surfaceMax, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_type() {
        return $this->_type;
    }

    public function set_type($_type) {
        if (is_array($_type))
            $this->_type = filter_var_array($_type, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_type[] = filter_var($_type, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_etage() {
        return $this->_etage;
    }

    public function set_etage($_etage) {
        if (is_array($_etage))
            $this->_etage = filter_var_array($_etage, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_etage[] = filter_var($_etage, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_nature() {
        return $this->_nature;
    }

    public function set_nature($_nature) {
        if (is_array($_nature))
            $this->_nature = filter_var_array($_nature, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_nature[] = filter_var($_nature, FILTER_SANITIZE_NUMBER_INT);
    }

    public function set_sort($fieldName) {
            $this->_sort = array($fieldName);
    }

    public function get_typeVente() {
        return $this->_typeVente;
    }

    public function set_typeVente($_typeVente) {
        if (is_array($_typeVente))
            $this->_typeVente = filter_var_array($_typeVente, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_typeVente[] = filter_var($_typeVente, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_secteur() {
        return $this->_secteur;
    }

    public function set_secteur($_secteur) {
        if (is_array($_secteur))
            $this->_secteur = filter_var_array($_secteur, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_secteur[] = filter_var($_secteur, FILTER_SANITIZE_NUMBER_INT);
    }

    public function get_typeSocial() {
        return $this->_typeSocial;
    }

    public function set_typeSocial($_typeSocial) {
        if (is_array($_typeSocial))
            $this->_typeSocial = filter_var_array($_typeSocial, FILTER_SANITIZE_NUMBER_INT);
        else
            $this->_typeSocial[] = filter_var($_typeSocial, FILTER_SANITIZE_NUMBER_INT);
    }

    public function set_budgetSlice($_budgetSlice) {
        $this->_budgetSlice = filter_var_array($_budgetSlice, array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => "/^[0-9]{1,}-[0-9]{1,}$/"))) ? $_budgetSlice : null;
    }

    public function get_budgetSlice() {
        return $this->_budgetSlice;
    }

    public function set_limit($first, $nb) {
            $this->_limit = array($first, $nb);
    }

    public function getItemsForm($dataTable, $fieldNameLabel, $fieldNameValue, $prefixField = true, $otherFieldsSelect = null, $groupBy = null) {
        $this->_buildElemQuery();
        $this->_from2String = "";

        $this->_whereArray = array_slice($this->_buildWhere(), 1);
        $noWhere = false;

        $this->_whereString = "";
        foreach ($this->_fromArray as $key => $value) {

            if($key > 0) {
                if($value["table"] == $dataTable) {
                    $noWhere = true;
                    $this->_from2String .= " RIGHT JOIN " . $value["table"] . " " . $value["on"] ;
                }
                else {
                    $this->_from2String .= " LEFT JOIN " . $value["table"] . " " . $value["on"] ;
                }

                if(isset($this->_whereArray[$value["table"]])) {
                    if(!$noWhere)
                        $this->_from2String .= " AND " . $this->_whereArray[$value["table"]];
                    unset($this->_whereArray[$value["table"]]);

                }
            }

            else {
                $this->_from1String = $value["table"];
            }
        }

        $this->_whereArray[] = "1=1";
        $this->_from2String = "empty RIGHT JOIN " . $this->_from1String . $this->_from2String . " ON 1=1";
//        $this->_from2String = "empty RIGHT JOIN " . $this->_from1String . (count($this->_whereArray)>0 ? " ON " . implode(" AND ", $this->_whereArray) : "" ) . $this->_from2String;


        $this->_whereString = implode(' AND ', $this->_whereArray);

        $query = "SELECT DISTINCT "
                . (!is_null($otherFieldsSelect) ? implode(", ", $otherFieldsSelect) . "," : "")
                . "$dataTable.$fieldNameValue AS value, "
                . "CONCAT( " . ($prefixField ? $dataTable . '.' : '') . "$fieldNameLabel"
                . ", '(', count(logement.id), ')'"
                . ") AS label, "
                . "CONCAT( " . ($prefixField ? $dataTable . '.' : '') . "$fieldNameLabel"
                . ") AS label_raw"
                . " FROM "
                . $this->_from2String
//                . " WHERE "
//                . $this->_whereString
                . " GROUP BY "
                . ( $groupBy == null ? "$dataTable.$fieldNameValue" : "$groupBy");

        $highlightSql = new highlightSQL();
//        echo $highlightSql->highlight($query);
        $results = Registry::get("db")->query($query);



        $items = $results->fetchAll(\PDO::FETCH_ASSOC);
        return $items;
    }

    private function _buildElemQuery() {

        $this->_selectString = "DISTINCT floor(prix_cc+prix_hc/100)*100 as minslice,
            logement.*,
            etage_logement.libelle as etage_libelle,
            nature_logement.libelle as nature_libelle,
            secteur_logement.libelle as secteur_libelle,
            type_logement.libelle as type_libelle,
            type_social_logement.libelle as type_social_libelle,
            type_vente_logement.libelle as type_vente_libelle,
            commune.libelle as commune
            ";
        $this->_fromString = "logement"
                . " LEFT JOIN etage_logement ON etage_logement.id = etage_id"
                . " LEFT JOIN commune ON commune.id = commune_id"
                . " LEFT JOIN nature_logement ON nature_logement.id = nature_id"
                . " LEFT JOIN secteur_logement ON secteur_logement.id = secteur_id"
                . " LEFT JOIN type_logement ON type_logement.id = type_id"
                . " LEFT JOIN type_social_logement ON type_social_logement.id = type_social_id"
                . " LEFT JOIN type_vente_logement ON type_vente_logement.id = type_vente_id";

        $this->_fromArray = Array(
            Array("table" => "logement"),

            Array("table" => "nature_logement", "on" => "ON nature_logement.id = nature_id"),
            Array("table" => "type_social_logement", "on" => "ON type_social_logement.id = type_social_id"),
            Array("table" => "type_vente_logement", "on" => "ON type_vente_logement.id = type_vente_id"),
            Array("table" => "secteur_logement", "on" => "ON secteur_logement.id = secteur_id"),
            Array("table" => "commune", "on" => "ON commune.id = commune_id"),
            Array("table" => "etage_logement", "on" => "ON etage_logement.id = etage_id"),
            Array("table" => "type_logement", "on" => "ON type_logement.id = type_id"),


        );

        $this->_whereString = implode(' AND ', $this->_buildWhere());

        $this->_orderByString = implode(', ', $this->_sort);
    }

    public function run() {

        $this->_buildElemQuery();

        $query = "SELECT "
                . $this->_selectString
                . " FROM "
                . $this->_fromString
                . " WHERE "
                . $this->_whereString
                . " ORDER BY "
                . $this->_orderByString
                . (is_array($this->_limit) ?  " LIMIT " . implode(", ", $this->_limit) : "");





        $results = Registry::get("db")->query($query);
        $this->_results = $results->fetchall(\PDO::FETCH_ASSOC);


        $this->_minMaxBudget();
    }

    public function getTotalNumRows() {
        $this->_selectString = "count(logement.id)";
        $this->_fromString = "logement"
                . " LEFT JOIN etage_logement ON etage_logement.id = etage_id"
                . " LEFT JOIN commune ON commune.id = commune_id"
                . " LEFT JOIN nature_logement ON nature_logement.id = nature_id"
                . " LEFT JOIN secteur_logement ON secteur_logement.id = secteur_id"
                . " LEFT JOIN type_logement ON type_logement.id = type_id"
                . " LEFT JOIN type_social_logement ON type_social_logement.id = type_social_id"
                . " LEFT JOIN type_vente_logement ON type_vente_logement.id = type_vente_id";

        $this->_whereString = implode(' AND ', $this->_buildWhere());

        $query = "SELECT "
                . $this->_selectString
                . " FROM "
                . $this->_fromString
                . " WHERE "
                . $this->_whereString;


        $results = Registry::get("db")->query($query);
        $this->_nbResults = $results->fetch(\PDO::FETCH_COLUMN);

        return $this->_nbResults;
    }

    private function _minMaxBudget() {
        foreach ($this->_results as $value) {
            if ($this->get_budgetMin() == null || $value["prix_cc"]+$value["prix_hc"] < $this->get_budgetMin())
                $this->set_budgetMin($value["prix_cc"]+$value["prix_hc"]);
            if ($this->get_budgetMax() == null || $value["prix_cc"]+$value["prix_hc"] > $this->get_budgetMax())
                $this->set_budgetMax($value["prix_cc"]+$value["prix_hc"]);
        }
    }

    private function _buildWhere() {
        $where = Array("1=1");

        //Filtre location
        if (!is_null($this->_location) && $this->_location != "") {
            $words = explode(" ", $this->_location);

            foreach ($words as $word) {
                $word = trim($word);
                if($word != "")
                    $temp []= "commune.libelle LIKE \"%$word%\" OR commune.code_postal LIKE \"%$word%\"";
            }

            $where[] = implode(" OR ", $temp);
        }

//        //Filtre budget
//        if (!is_null($this->get_budgetMin()) && !is_null($this->get_budgetMax())) {
//            $where[] = "prix_cc BETWEEN $this->_budgetMin AND $this->_budgetMax";
//        } else {
//            if (!is_null($this->get_budgetMin()))
//                $where[] = "prix_cc >= $this->_budgetMin";
//            if (!is_null($this->get_budgetMax()))
//                $where[] = "prix_cc <= $this->_budgetMax";
//        }
        //Filtre budget Slice
        if (!is_null($this->get_budgetSlice())) {
            foreach ($this->get_budgetSlice() as $value) {
                $sliceWhere[] = "(prix_cc+prix_hc BETWEEN " . str_replace("-", " AND ", $value) . ")";
            }
            $where[] = "(" . implode(" OR ", $sliceWhere) . ")";
        }


        //Filtre surface
        if (!is_null($this->get_surfaceMin()) && !is_null($this->get_surfaceMax())) {
            $where[] = "superficie BETWEEN $this->_surfaceMin AND $this->_surfaceMax";
        } else {
            if (!is_null($this->get_surfaceMin()))
                $where[] = "superficie >= $this->_surfaceMin";
            if (!is_null($this->get_surfaceMax()))
                $where[] = "superficie <= $this->_surfaceMax";
        }

        //Filtre type
        if (!is_null($this->_type)) {
            $where["type_logement"] = "type_id IN (" . implode(", ", $this->_type) . ")";
        }

        //Filtre etage
        if (!is_null($this->_etage)) {
            $where["etage_logement"] = "etage_id IN (" . implode(", ", $this->_etage) . ")";
        }

        //Filtre nature
        if (!is_null($this->_nature)) {
            $where["nature_logement"] = "nature_id IN (" . implode(", ", $this->_nature) . ")";
        }

        //Filtre type_vente
        if (!is_null($this->_typeVente)) {
            $where["type_vente_logement"] = "type_vente_id IN (" . implode(", ", $this->_typeVente) . ")";
        }

        //Filtre type_social
        if (!is_null($this->_typeSocial)) {
            $where["type_social_logement"] = "type_social_id IN (" . implode(", ", $this->_typeSocial) . ")";
        }

        //Filtre secteur
        if (!is_null($this->_secteur)) {
            $where["secteur_logement"] = "secteur_id IN (" . implode(", ", $this->_secteur) . ")";
        }

        return $where;
    }


    public function getResults() {
        return $this->_results;
    }

}