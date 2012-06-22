<?php

require_once 'main-controller.php';

class ContactController extends MainController
{

    private $_cache = null;
    private $_config = null;
    
    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
        /* Exemple config
        $this->_config = array(
            "table" => array(
                "name" => "contact",
                "title" => "Liste des contacts",
                "title_item" => "contact",
                "suffix_genre" => ""
            ),
            "columns" => array(
                array(
                    "name" => "date_crea",
                    "show" => true,
                    "filter_field" => true,
                    "title" => "Date de création",
                    "default_sorting"   => true,
                    "default_sorting_direction"   => "desc",
                ),
                array(
                    "name" => "civilite",
                    "show" => true,
                    "filter_field" => true,
                    "title" => "Civ",
                ),
                array(
                    "name" => "nom",
                    "filter_field" => true,
                    "show" => true,
                    "title" => "Nom",
                ),
                array(
                    "name" => "prenom",
                    "filter_field" => true,
                    "show" => true,
                    "title" => "Prénom",
                ),
                array(
                    "name" => "tel",
                    "filter_field" => true,
                    "show" => true,
                    "title" => "Tél",
                ),
                array(
                    "name" => "email",
                    "filter_field" => true,
                    "show" => true,
                    "title" => "Email",
                ),
                array(
                    "name" => "message",
                    "show" => true,
                    "title" => "Message",
                ),
                array(
                    "name" => "comment",
                    "show" => true,
                    "title" => "Vu sur",
                ),
                array(
                    "filter_field" => true,
                    "name" => "type",
                    "show" => true,
                    "title" => "Type",
                ),
                array(
                    "filter_field" => true,
                    "name" => "date",
                    "show" => true,
                    "title" => "Date rappel",
                ),
                array(
                    "name" => "heure",
                    "show" => true,
                    "title" => "Heure rappel",
                ),
                array(
                    "name" => "id_version",
                    "filter" => BACK_ID_VERSION,
                ),
            ),
        );
        */
    }

//end start()

    /**
     * 
     * @return void
     */
    public function listAction()
    {
        $this->_view->action = "contact";
        $this->_javascript->addLibrary("back/jquery/jquery.livequery.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery.dataTables.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery.jeditable.js");
        $this->_css->addLibrary("back/demo_table_jui.css");

        $this->_view->config = $this->_config;
        $this->_view->versions = $this->_db->query("SELECT * FROM `version`")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function editableAction()
    {
        $this->_view->enable(false);

        $columnModified = "";

        $filter = explode('|', $_POST["row_id"]);
        $i = 0;
        $j = 0;
        $where = array();
        foreach ($this->_config["columns"] as $column) {
            if (isset($column["index"]) && $column["index"]) {
                $where[] = $column["name"] . " = " .  $this->_db->quote($filter[$i]);
                if($column["name"] == "cle")
                    $where[] = $column["name"] . " LIKE BINARY " .  $this->_db->quote($filter[$i]);
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

    public function jsonAction()
    {
        $this->_view->enable(false);

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $sIndexColumn = array();
        $sFilterColumn = array();
        $aColumns = array();
        foreach ($this->_config["columns"] as $column) {
            if (isset($column["show"]) && $column["show"])
                $aColumns[] = $column["name"];
            if (isset($column["index"]) && $column["index"])
                $sIndexColumn[] = $column["name"];
            if (isset($column["filter"]))
                $sFilterColumn[] = $column["name"] . ' = ' . $this->_db->quote($column["filter"]);
        }

        /* Indexed column (used for fast and accurate table cardinality) */


        /* DB table to use */
        $sTable = $this->_config["table"]["name"];





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
                    $sOrder .= "`" . $aColumns[intval($_GET['iSortCol_' . $i])] . "` " .
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
                $sWhere .= "`" . $aColumns[$i] . "` LIKE $search OR ";
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
                $sWhere .= "`" . $aColumns[$i] . "` LIKE " . $this->_db->quote('%' . $_GET['sSearch_' . $i] . '%') . "";
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

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS `" . str_replace(" , ", " ", implode("`, `", $sColumns)) . "`
		FROM   $sTable
		$sWhere
                $generalWhere
		$sOrder
		$sLimit
		";

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
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$aColumns[$i]];
                }
            }
            for ($i = 0; $i < count($sIndexColumn); $i++) {
                $row["DT_RowId"] .= $aRow[$sIndexColumn[$i]] . "|";
            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

}

//end class