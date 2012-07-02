<?php

require_once 'main-controller.php';

class DashboardController extends MainController {

    private $_cache = null;
    private $_config = null;

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start() {
        parent::start();
        $configMain = Registry::get('mainconfig');


        if (isset($_GET["name"])) {

            require_once $configMain->get('list', 'dirs') . $_GET["name"] . ".cfg.php";
            $this->_view->name = str_replace(array(".", "-"), "_", $_GET["name"]) . '_' . time();
            $this->_config = $config;

            $this->_aFilterColumnAdditional = array();
            $this->_view->additionalParams = "";

            if (isset($_GET["filter"])) {
                foreach ($_GET["filter"] as $filter) {
                    list($filterColumn, $filterValue) = explode("|", $filter);
                    $this->_aFilterColumnAdditional[] = $filterColumn . ' = ' . $this->_db->quote($filterValue);
                }
                $params["filter"] = $_GET["filter"];
                $this->_view->additionalParams = http_build_query($params);
            }
        } else {
            $this->simpleRedirect($this->_url);
        }
    }

//end start()

    /**
     * 
     * @return void
     */
    public function startAction() {




        $this->_view->action = "contact";
        $this->_javascript->addLibrary("back/jquery/jquery.livequery.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery.dataTables.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery.jeditable.js");
        $this->_javascript->addLibrary("back/jquery/jquery.autogrow.js");
        $this->_javascript->addLibrary("back/jquery/jquery.jeditable.autogrow.js");

        $this->_javascript->addLibrary("back/jquery/FixedHeader.js");
        $this->_javascript->addLibrary("back/jquery/jquery.dataTables.columnFilter.js");



        if (isset($this->_config["extra"]) && isset($this->_config["extra"]["hide_columns"]) && $this->_config["extra"]["hide_columns"]) {
            $this->_javascript->addLibrary("back/jquery/ColVis.js");
        }

        $this->_javascript->addLibrary("back/jquery/ZeroClipboard.js");
        $this->_javascript->addLibrary("back/jquery/TableTools.js");





        $this->_css->addLibrary("back/demo_table_jui.css");
        $this->_css->addLibrary("back/ColVis.css");
        $this->_css->addLibrary("back/TableTools_JUI.css");


        if (isset($_GET["nomain"]) && $_GET["nomain"] == 1) {
            $this->_view->main(false);
            echo '
            <html>
            <thead>
            <meta http-equiv="Content-Type" content="text/html; text/css; charset=utf-8" />
            <base href="' . $this->_url . '" />';
            if (!isset($_GET["nojs"]) || !$_GET["nojs"]) {
                echo '
            <link type="text/css" rel="stylesheet" media="screen" href="css/back/jquery-ui-1.8.7.custom.css" />
            ' . $this->_css . '
            ' . $this->_javascript;
            }

            if (isset($this->_config["additional_script"]) && count($this->_config["additional_script"]) > 0) {
                foreach ($this->_config["additional_script"] as $script) {
                    echo '<script src="' . $script . '" type="text/javascript"></script>';
                }
            }

            echo '
            <style>
            #tableau-' . $this->_view->name . ' {
            font-size: 0.96em;
            }
            </style>
            </thead>

            ';

            $this->_config["table"]["title"] = "";
        }

        if (isset($this->_config["additional_script"]) && count($this->_config["additional_script"]) > 0) {
            foreach ($this->_config["additional_script"] as $script) {
                $this->_javascript->addLibrary($script);
            }
        }



        $sFilterColumn = array();
        $generalWhere = "";

        foreach ($this->_config["columns"] as $column) {
            if (isset($column["filter"]))
                $sFilterColumn[] = $column["name"] . ' = ' . $this->_db->quote($column["filter"]);
        }

        $generalWhere .= implode(" AND ", $sFilterColumn);


        foreach ($this->_config["columns"] as &$column) {

            if (isset($column["filter_field"]) && $column["filter_field"] == "select") {
                /* Jointure sur autre table */
                if (isset($column["from"]) && $column["from"]) {
                    $aFilterJoin = array();

                    foreach ($column["from"]["index"] as $sColIndex => $sColIndexVal) {
                        if ($sColIndexVal == "THIS") {
                            $sVal = "";
//                            $sVal = "`" . $sTable . "`.`" . $column["name"] . "`";
                        } else {
                            $sVal = $this->_db->quote($sColIndexVal);
                        }
                        $aFilterJoin[] = "`" . $column["from"]["table"] . "`.`" . $sColIndex . "` = " . $sVal;
                    }

                    $aVal = array();
                    foreach ($column["from"]["columns"] as $sCol) {
                        $sColVal = current($sCol);
                        $sColKey = key($sCol);

                        if ($sColKey == "name") {
                            $aVal[] = "`" . $column["from"]["table"] . "`.`$sColVal`";
                        } else {
                            $aVal[] = $this->_db->quote($sColVal);
                        }
                    }

                    $columnAdvancedName = "CONCAT(" . implode(",", $aVal) . ")";
                    $column["name"] = $columnAdvancedName . " `" . $column["name"] . "`";
//                    $column["values"] = $this->_db->query("SELECT DISTINCT " . $column["name"] . " FROM `" . $column["from"]["table"] . "`  ORDER BY " . $columnAdvancedName . " ASC")->fetchAll(PDO::FETCH_COLUMN);
                    $column["values"] = $this->_db->query("SELECT DISTINCT " . $column["name"] . " FROM `" . $column["from"]["table"] . "`")->fetchAll(PDO::FETCH_COLUMN);
                } else {
                    $column["values"] = $this->_db->query("SELECT DISTINCT " . $column["name"] . " FROM " . $this->_config["table"]["name"] . " WHERE " . $column["name"] . " <> '' AND $generalWhere  ORDER BY " . $column["name"] . " ASC")->fetchAll(PDO::FETCH_COLUMN);
                }
            }
        }

        $this->_view->config = $this->_config;

        $this->_view->breadCrumbs[] = array(
            "label" => $this->_config["table"]["title"],
            "url" => "",
        );
    }

    public function editableAction() {
        $this->_view->enable(false);

        $columnModified = "";

        $filter = explode('|', $_POST["row_id"]);
        $i = 0;
        $j = 0;
        $where = array();
        foreach ($this->_config["columns"] as $column) {
            if (isset($column["index"]) && $column["index"]) {
                $where[] = $column["name"] . " = " . $this->_db->quote($filter[$i]);
                if ($column["name"] == "cle")
                    $where[] = $column["name"] . " LIKE BINARY " . $this->_db->quote($filter[$i]);
                $i++;
            }
            if (isset($column["show"]) && $column["show"]) {
                if ($j == intval($_POST["column"]))
                    $columnModified = $column["name"];
                $j++;
            }
        }




        /* DB table to use */
        $sTable = $this->_config["table"]["name"];



        $value = $this->_db->quote($_POST["value"]);


        $query = "
            UPDATE $sTable SET `$columnModified` = $value WHERE " . implode(" AND ", $where) . ";
        ";

        $update = $this->_db->prepare($query);
        $update->execute();

        $r = $update->rowCount();

        if ($r)
            echo $_POST["value"];
    }

    public function jsonAction() {
        $this->_view->enable(false);

//        ini_set("display_errors", 1);
        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $sIndexColumn = array();
        $sIndexColumnRaw = array();
        $sFilterColumn = array();
        $aColumns = array();
        $aColumnsRaw = array();
        $aColumnsRawAll = array();
        $aColumnsAdvanced = array();
        $aColumnsContent = array();
        $aColumnsFunctions = array();
        $aColumnsTag = array();
        $aColumnsSelect = array();
        $sTableJoin = "";






        /* Indexed column (used for fast and accurate table cardinality) */


        /* DB table to use */
        $sTable = $this->_config["table"]["name"];

        /* Traitement des definition de columns */
        foreach ($this->_config["columns"] as $keyCol => $column) {
            if (!isset($column["name"]) && isset($column["special"])) {
                $aColumnsRaw[$keyCol] = $column["special"];
                continue;
            }

            /* Contenu statique */
            if (isset($column["content"]) && !isset($column["name"])) {

                $columnRawName = "content_$keyCol";
                $columnAdvancedName = $this->_db->quote($column["content"]);
                $column["name"] = $columnAdvancedName . " `content_$keyCol`";
                $columnSelect = $column["name"];

                /* Jointure sur autre table */
            } else if (isset($column["from"]) && $column["from"]) {
                $aFilterJoin = array();

                foreach ($column["from"]["index"] as $sColIndex => $sColIndexVal) {
                    if ($sColIndexVal == "THIS") {
                        $sVal = "`" . $sTable . "`.`" . $column["name"] . "`";
                    } else {
                        $sVal = $this->_db->quote($sColIndexVal);
                    }
                    $aFilterJoin[] = "`" . $column["from"]["table"] . "_$keyCol`.`" . $sColIndex . "` = " . $sVal;
                }

                $aVal = array();
                foreach ($column["from"]["columns"] as $sCol) {
                    $sColVal = current($sCol);
                    $sColKey = key($sCol);
                    if ($sColKey == "name") {
                        $sColVal2 = next($sCol);
                        $sColKey2 = key($sCol);
                        if ($sColKey2 == "sql") {
                            $aVal[] = "$sColVal2";
                        } else
                            $aVal[] = "`" . $column["from"]["table"] . "_$keyCol`.`$sColVal`";
                    } else {
                        $aVal[] = $this->_db->quote($sColVal);
                    }
                }



                $columnRawName = $column["name"];
                for ($index = 1; $index < 10; $index++) {

                    if (in_array($columnRawName, $aColumnsRawAll) === false)
                        break;

                    $columnRawName = $column["name"] . "_" . $index;
                }

                $columnAdvancedName = "CONCAT(" . implode(",", $aVal) . ")";

                $column["name"] = $columnAdvancedName . " `" . $columnRawName . "`";


                $columnSelect = $column["name"];


                if (isset($column["from"]["groupby"])) {
                    $sTableJoin .= " LEFT JOIN (SELECT *, $columnSelect FROM `" . $column["from"]["table"] . "` GROUP BY " . $column["from"]["groupby"] . "";
                    $columnSelect = $columnRawName;
                    if (isset($column["from"]["having"])) {
                        $sTableJoin .= " HAVING " . $column["from"]["having"];
                    }

                    $sTableJoin .= ") `" . $column["from"]["table"] . "_$keyCol`  ON " . implode(" AND ", $aFilterJoin);
                } else {
                    $sTableJoin .= " LEFT JOIN `" . $column["from"]["table"] . "` `" . $column["from"]["table"] . "_$keyCol`  ON " . implode(" AND ", $aFilterJoin);
                }
            } else {
                $columnRawName = $column["name"];
                for ($index = 1; $index < 10; $index++) {

                    if (in_array($columnRawName, $aColumnsRawAll) === false)
                        break;

                    $columnRawName = $column["name"] . "_" . $index;
                }

                if (isset($column["sql"])) {
                    //Si la colone est du sql avec des fonctions
                    $columnAdvancedName = $column["sql"];
                    $column["name"] = $columnAdvancedName;
                } else {
                    $columnAdvancedName = "`" . $sTable . "`.`" . $column["name"] . "`";
                    $column["name"] = "`" . $sTable . "`.`" . $column["name"] . "`";
                }
                $columnSelect = $column["name"] . " " . $columnRawName;
            }


//            echo '<pre>', print_r($aColumns, true), '</pre>';
//            echo '<pre>', print_r($aColumnsRaw, true), '</pre>';
//            echo '<pre>', print_r($aColumnsAdvanced["name"], true), '</pre>';


            if (isset($column["show"]) && $column["show"] || isset($column["show_detail"]) && $column["show_detail"]) {

                $aColumns[$keyCol] = $column["name"];
                $aColumnsRaw[$keyCol] = $columnRawName;
                $aColumnsAdvanced[] = $columnAdvancedName;
                $aColumnsContent[$keyCol] = isset($column["content"]) && isset($column["name"]) ? $column["content"] : false;
                $aColumnsFunctions[$keyCol] = isset($column["nl2br"]) ? array("nl2br") : false;
                if (!$aColumnsFunctions[$keyCol])
                    $aColumnsFunctions[$keyCol] = isset($column["php_function"]) ? $column["php_function"] : false;
            }

            if (isset($column["name"])) {
                $aColumnsRawAll[$keyCol] = $columnRawName;
                $aColumnsSelect[$keyCol] = $columnSelect;
                $aColumnsTag[$keyCol] = "[#$columnRawName#]";
            }

            if (isset($column["index"]) && $column["index"]) {
                $sIndexColumnRaw[] = $columnRawName;
                $sIndexColumn[] = $column["name"] . " $columnRawName";
            }

            if (isset($column["filter"]))
                $sFilterColumn[$keyCol] = $column["name"] . ' = ' . $this->_db->quote($column["filter"]);
        }

        //Si on a des filtres addionnal (exemple en param de lurl)
        $sFilterColumn = array_merge($this->_aFilterColumnAdditional, $sFilterColumn);





        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP server-side, there is
         * no need to edit below this line
         */




        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " .
                    intval($_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        $sOrder = "";

        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {

                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    if (isset($this->_config["table"]["detail"]) && $this->_config["table"]["detail"])
                        $indexColumn = intval($_GET['iSortCol_' . $i]) - 1;
                    else {
                        $indexColumn = intval($_GET['iSortCol_' . $i]);
                    }
                    $sOrder .= "" . $aColumnsAdvanced[$indexColumn] . " " .
                            $_GET['sSortDir_' . $i] . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $search = $this->_db->quote('%' . $_GET['sSearch'] . '%');
                $sWhere .= "" . $aColumnsAdvanced[$i] . " LIKE $search OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {

            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                if (isset($this->_config["table"]["detail"]) && $this->_config["table"]["detail"])
                    $indexColumn = $i - 1;
                else
                    $indexColumn = $i;
                $sWhere .= "" . $aColumnsAdvanced[$indexColumn] . " LIKE " . $this->_db->quote('%' . $_GET['sSearch_' . $i] . '%') . "";
            }
        }


        $generalWhere = "";
        if (count($sFilterColumn) > 0) {
            if ($sWhere == "")
                $generalWhere = "WHERE ";
            else {
                $generalWhere = " AND ";
            }
            $generalWhere .= implode(" AND ", $sFilterColumn);
        }


        $sColumns = array_unique(array_merge($aColumns, $sIndexColumn));
        $sColumnsSelect = array_unique(array_merge($aColumnsSelect, $sIndexColumn));

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $sColumnsSelect)) . "
		FROM   $sTable
                $sTableJoin
		$sWhere
                $generalWhere
		$sOrder
		$sLimit
		";
//        echo '<pre>', print_r($sQuery, true), '</pre>';
        $rResult = $this->_db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = $this->_db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->fetch(PDO::FETCH_NUM);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(*)
		FROM   $sTable
                " . (substr($generalWhere, 0, 5) == "WHERE" ? "" : "WHERE 1 " ) . " $generalWhere
	";

        $rResultTotal = $this->_db->query($sQuery);
        $aResultTotal = $rResultTotal->fetch(PDO::FETCH_NUM);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );


        while ($aRow = $rResult->fetch(PDO::FETCH_ASSOC)) {
            $row = array();
            $row["DT_RowId"] = "";
            if (isset($this->_config["table"]["detail"]) && $this->_config["table"]["detail"])
                $row[] = '<img class="detail" src="img/back/details_open.png">';
            foreach ($aColumnsRaw as $aColRawKey => $aColRaw) {
                if ($aColumnsRaw[$aColRawKey] != ' ') {
                    /* General output */
                    if (!isset($aColumnsContent[$aColRawKey])) {
                        $row[] = $this->$aColRaw($aRow);
                    } else {
                        if ($aColumnsContent[$aColRawKey] !== false) {
                            $searchTag = array_merge($aColumnsTag, array("[#THIS#]"));
                            $replaceTag = array_merge($aRow, array($aRow[$aColumnsRaw[$aColRawKey]]));
                            $row[] = $aRow[$aColumnsRaw[$aColRawKey]] = str_replace($searchTag, $replaceTag, $aColumnsContent[$aColRawKey]);
                        } else {
                            $row[] = $aRow[$aColumnsRaw[$aColRawKey]];
                        }
                        if ($aColumnsFunctions[$aColRawKey] !== false) {
                            foreach ($aColumnsFunctions[$aColRawKey] as $function) {
                                $row[count($row) - 2] = call_user_func($function, $row[count($row) - 2]);
                                $row[count($row) - 2] = preg_replace("/(\r\n|\n|\r)/", "", $row[count($row) - 2]);
                            }
                        }
                    }
                }
            }

            for ($i = 0; $i < count($sIndexColumnRaw); $i++) {
                $row["DT_RowId"] .= $aRow[$sIndexColumnRaw[$i]] . "|";
            }
            $row["DT_RowId"] = substr($row["DT_RowId"], 0, -1);
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function buildAction($data) {
        $actionHtml = '<div style="width:145px">';
        
        if ($this->_utilisateur->get("niveau") == "solire" || $this->_gabarits[$data["id_gabarit"]]["editable"]) {
            $actionHtml .= '<div class="btn btn-mini gradient-blue fl" ><a title="Modifier" href="page/display.html?id_gab_page=' . $data["id"] . '"><img alt="Modifier" src="img/back/white/pen_alt_stroke_12x12.png" /></a></div>';
        }
        if (($this->_utilisateur->get("niveau") == "solire" || $this->_gabarits[$data["id_gabarit"]]["make_hidden"] || $data["visible"] == 0) && $data["rewriting"] != "") {
            $actionHtml .= '<div class="btn btn-mini gradient-blue fl" ><a title="Rendre visible \'' . $data["titre"] . '\'" style="padding: 3px 7px 3px;"><input type="checkbox" value="' . $data["id"] . '-' . $data["id_version"] . '" class="visible-lang visible-lang-' . $data["id"] . '-' . $data["id_version"] . '" ' . ($data["visible"] > 0 ? ' checked="checked"' : '') . '/></a></div>';
        }
        if ($data["rewriting"] == "") {
            $actionHtml .= '<div class="btn btn-mini gradient-red fl"><a style="color:white;line-height: 12px;" href="page/display.html?id_gab_page=' . $data["id"] . '">Non traduit</a></div>';
        }
        $actionHtml .= '</div>';
        return $actionHtml;
    }

}

//end class