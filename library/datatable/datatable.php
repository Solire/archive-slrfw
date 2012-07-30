<?php
/**
 * Datatable Class
 *
 * Créer un tableau avancé 
 * 
 * @package Datatable
 * @author shin
 */
class Datatable {
    
    /**
     * Nom de la vue à utiliser
     *
     * @var string
     * @access protected
     */
    protected $_view = "datatable";
    /**
     * Réponse JSON des données qui sera renvoyé
     *
     * @var string
     * @access protected
     */
    protected $_response = "";
    /**
     * Chemin du répertoire contenant les vues
     *
     * @var string
     * @access protected
     */
    protected $_viewPath = "view/";
    /**
     * Chemin du répertoire contenant les feuilles de style
     *
     * @var string
     * @access protected
     */
    protected $_cssPath = "./datatable/";
    /**
     * Chemin du répertoire contenant les scripts javascript
     *
     * @var string
     * @access protected
     */
    protected $_jsPath = "./datatable/";
    /**
     * Chemin du répertoire contenant les images
     *
     * @var string
     * @access protected
     */
    protected $_imgPath = "img/datatable/";
    /**
     * Action executé
     *
     * @var string
     * @access protected
     */
    protected $_action = "datatable";
    /**
     * Chemin du répertoire contenant les fichiers de configurations
     *
     * @var string
     * @access protected
     */
    protected $_configPath = "../config/datatable/";
    /**
     * Nom du fichier de configuration qui sera utilisé
     *
     * @var string
     * @access protected
     */
    protected $_configName = "";
    /**
     * Connexion à la base de données qui sera utilisé
     *
     * @var MyPDO
     * @access protected
     */
    protected $_db;
    /**
     * Paramètres GET de l'url
     *
     * @var array
     * @access protected
     */
    protected $_get;
    /**
     * Chargeur de script Javascript
     *
     * @var JavascriptLoader
     * @access protected
     */
    protected $_javascript;
    /**
     * Chargeur de feuilles de styles
     *
     * @var CssLoader
     * @access protected
     */
    protected $_css;
    /**
     * Clause where de la requête
     *
     * @var string
     * @access protected
     */
    protected $_where;
    /**
     * 
     *
     * @var string
     * @access protected
     */
    protected $_beforeTableHTML;
    /**
     * 
     *
     * @var string
     * @access protected
     */
    protected $_afterTableHTML;
    
    /**
     * Constructeur
     *
     * Défini les chemins des ressources, la connexion à la base de données
     * ainsi que les paramètres GET de l'url et le nom du fichier de configuration
     */
    public function __construct($get, $configName, $db = null, $cssPath = "./datatable/", $jsPath = "./datatable/", $imgPath = "./img/datatable/") {        
        $this->_db = $db;
        $this->_get = $get;
        $this->_configName = $configName;

        if (isset($this->_get["json"])) {
            $this->_view = "json";
            $this->_action = "json";
        }
        
        if (isset($this->_get["editable"])) {
            $this->_view = "editable";
            $this->_action = "editable";
        }

        //Paramètrage du chemin des ressources
        $this->_cssPath     = $cssPath;
        $this->_jsPath      = $jsPath;
        $this->_imgPath     = $imgPath;

        //Création d'un chargeur JS/CSS
        $this->_javascript  = new Javascript();
        $this->_css         = new Css();
    }

    // --------------------------------------------------------------------

    /**
     * Initialise le datatable
     *
     * Cette méthode est toujours appelée.
     * Elle permet de charger le fichier de configuration
     * Puis elle appelle l'action à executer
     *
     * @return 	void
     */
    public function start()
    {

        if ($this->_configName != "") {
            require_once($this->_configPath . $this->_configName . ".cfg.php");
            $this->name = str_replace(array(".", "-"), "_", $this->_configName) . '_' . time();
            $this->config = $config;

            $this->_aFilterColumnAdditional = array();
            $this->additionalParams = "";

            if (isset($_POST["filter"])) {
                foreach ($_POST["filter"] as $filter) {
                    list($filterColumn, $filterValue) = explode("|", $filter);
                    $this->aFilterColumnAdditional[] = $filterColumn . ' = ' . $this->_db->quote($filterValue);
                }
                $params["filter"] = $_POST["filter"];
                $this->additionalParams = http_build_query($params);
            }
        }

        $this->url = self::_selfURL();

        if (method_exists($this, $this->_action . "Action")) {
            call_user_func(array($this, $this->_action . "Action"));
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * TODO à commenter
     *
     * @return 	void
     */
    public function beforeTable($html)
    {
        $this->_beforeTableHTML = $html;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * TODO à commenter
     *
     * @return 	void
     */
    public function afterTable($html)
    {
        $this->_afterTableHTML = $html;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Action qui va généré le HTML du tableau
     *
     * @return 	void
     */
    public function datatableAction()
    {
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.livequery.min.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.dataTables.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.jeditable.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.autogrow.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.jeditable.autogrow.js");

        $this->_javascript->addLibrary($this->_jsPath . "jquery/FixedHeader.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/jquery.dataTables.columnFilter.js");

        if (isset($this->config["extra"])
                && isset($this->config["extra"]["hide_columns"]) && $this->config["extra"]["hide_columns"]) {
            $this->_javascript->addLibrary($this->_jsPath . "jquery/ColVis.js");
        }

        $this->_javascript->addLibrary($this->_jsPath . "jquery/ZeroClipboard.js");
        $this->_javascript->addLibrary($this->_jsPath . "jquery/TableTools.js");



        $this->_css->addLibrary($this->_cssPath . "demo_table_jui.css");
        $this->_css->addLibrary($this->_cssPath . "ColVis.css");
        $this->_css->addLibrary($this->_cssPath . "TableTools_JUI.css");

        if (isset($this->config["additional_script"]) && count($this->config["additional_script"]) > 0) {
            foreach ($this->config["additional_script"] as $script) {
                $this->_javascript->addLibrary($script);
            }
        }

        $sFilterColumn = array();

        foreach ($this->config["columns"] as $column) {
            if (isset($column["filter"]))
                $sFilterColumn[] = $column["name"] . ' = ' . $this->_db->quote($column["filter"]);
        }

        $generalWhere = implode(" AND ", $sFilterColumn);

        /* DB table to use */
        $sTable = $this->config["table"]["name"];

        foreach ($this->config["columns"] as $iKeyJoin => &$column) {

            if (isset($column["filter_field"]) && $column["filter_field"] == "select") {
                /* Jointure sur autre table */
                if (isset($column["from"]) && $column["from"]) {
                    $aFilterJoin = array();

                    foreach ($column["from"]["index"] as $sColIndex => $sColIndexVal) {
                        if ($sColIndexVal == "THIS") {
                            $sVal = "`" . $sTable . "`.`" . $column["name"] . "`";
                        } else {
                            $sVal = $this->_db->quote($sColIndexVal);
                        }
                        $aFilterJoin[] = "`" . $column["from"]["table"] . "_$iKeyJoin`.`" . $sColIndex . "` = " . $sVal;
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
                }
                elseif (isset($column["sql"])) {
                    $column["values"]   = $this->_db->query("SELECT DISTINCT " . $column["sql"] . ""
                                        . " FROM `" . $this->config["table"]["name"] . "` WHERE `" . $column["name"] . "` <> '' "
                                        . ($generalWhere == "" ? "" : "AND $generalWhere" )
                                        . " ORDER BY `" . $column["name"] . "` ASC")->fetchAll(PDO::FETCH_COLUMN);
                }
                else {
                    $column["values"]   = $this->_db->query("SELECT DISTINCT `" . $column["name"] . "`"
                                        . " FROM `" . $this->config["table"]["name"] . "` WHERE `" . $column["name"] . "` <> '' "
                                        . ($generalWhere == "" ? "" : "AND $generalWhere" )
                                        . " ORDER BY `" . $column["name"] . "` ASC")->fetchAll(PDO::FETCH_COLUMN);
                }
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Action qui va être appelée pour éditer une donnée
     *
     * @return 	void
     */
    public function editableAction()
    {

        $columnModified = "";

        $filter = explode('|', $_POST["row_id"]);
        $i = 0;
        $j = 0;
        $where = array();
        foreach ($this->config["columns"] as $column) {
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
        $sTable = $this->config["table"]["name"];



        $value = $this->_db->quote($_POST["value"]);


        $query = "
            UPDATE $sTable SET `$columnModified` = $value WHERE " . implode(" AND ", $where) . ";
        ";

        $update = $this->_db->prepare($query);
        $update->execute();

        $r = $update->rowCount();

        if ($r)
            exit($_POST["value"]);
    }

    // --------------------------------------------------------------------
    
    /**
     * Action qui va être appelée pour récupérer le sous forme de JSON les
     * données à charger dans le datatable.
     *
     * @return 	void
     */
    public function jsonAction()
    {
        /* = Tableau renvoyé.
        `------------------------------------------------------ */
        $output = array();

        /* = Array of database columns which should be read and sent back to DataTables. Use a space where
        |    you want to insert a non-database field (for example a counter or static image)
        `------------------------------------------------------ */
        $sIndexColumn = array();
        $sIndexColumnRaw = array();
        $sFilterColumn = array();
        $aColumns = array();
        $aColumnsFull = array();
        $aColumnsRaw = array();
        $aColumnsDetails = array();
        $aColumnsRawAll = array();
        $aColumnsAdvanced = array();
        $aColumnsContent = array();
        $aColumnsFunctions = array();
        $aColumnsTag = array();
        $aColumnsSelect = array();
        $sTableJoin = "";
        
        $realIndexes    = array();
        $aColumnsBottom = array();
        $sSearchableColumn = array();

        /* = table de la BDD utilisée.
        `---------------------------------------------------------------------- */
        $sTable = $this->config["table"]["name"];

        $realIndex = 0;
        
        /* = Si la première colonne est un '+' pour ouvrir le détail.
        `---------------------------------------------------------------------- */
        if (isset($this->config["table"]["detail"]) && $this->config["table"]["detail"]) {
            $aColumnsAdvanced[] = NULL;
            $aColumnsFull[] = NULL;
            $realIndex++;
        }
        
        /* = Traitement des definition de columns
        `---------------------------------------------------------------------- */
        foreach ($this->config["columns"] as $keyCol => $column) {
            /* = Lien entre la clé et l'index du tableau réélle.
            `---------------------------------------------------------------------- */
            if (isset($column["show"]) && $column["show"]
//                || isset($column["show_detail"]) && $column["show_detail"]
            ) {
                $realIndexes[$keyCol] = $realIndex;
//                $realIndexes2[$column['title'] . "|" . $column['name']] = $realIndex;
                
//                $aColumnsAdvanced[] = $columnAdvancedName;
//                $aColumnsFull[] = $column;
                
                if (!isset($column["searchable"]) || $column["searchable"])
                    $sSearchableColumn[$realIndex] = TRUE;

                $realIndex++;
            }
            
            $aColumnsFunctions[$keyCol] = false;
            if (isset($column["php_function"])) {
                $aColumnsFunctions[$keyCol] = isset($column["php_function"]) ? $column["php_function"] : false;
            }
            if (isset($column["special"])) {
                $aColumnsRaw[$keyCol] = $column["special"];
                $aColumnsFunctions[$keyCol] = $column["special"];
                if (!isset($column["name"]))
                    continue;
            }
            
            /* = Contenu statique
            `---------------------------------------------------------------------- */
            if (isset($column["content"]) && !isset($column["name"])) {
                $columnRawName = "content_$keyCol";
                $columnAdvancedName = $this->_db->quote($column["content"]);
                $column["name"] = $columnAdvancedName . " `content_$keyCol`";
                $columnSelect = $column["name"];
            }
            /* Jointure sur autre table
            `---------------------------------------------------------------------- */
            elseif (isset($column["from"]) && $column["from"]) {
                $aFilterJoin = array();

                foreach ($column["from"]["index"] as $sColIndex => $sColIndexVal) {
                    if ($sColIndexVal == "THIS") {
                        $sVal = "`" . $sTable . "`.`" . $column["name"] . "`";
                    }
                    else {
                        if (!is_array($sColIndexVal)) {
                            $sColIndexVal = array($sColIndexVal);
                        }
                        
                        foreach ($sColIndexVal as $ii => $v) {
                            $sColIndexVal[$ii] = $this->_db->quote($v);
                        }
                        
                        $sVal = implode(",", $sColIndexVal);
                    }

                    $aFilterJoin[]  = "`" . $column["from"]["table"] . "_$keyCol`.`" . $sColIndex . "`"
                                    . " IN (" . $sVal . ")";
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
                
                $joinType = isset($column["from"]["type"])
                    ? $column["from"]["type"]
                    : "INNER";
                if (isset($column["from"]["groupby"])) {
                    $sTableJoin .= " $joinType JOIN (SELECT *, $columnSelect FROM `" . $column["from"]["table"] . "` GROUP BY " . $column["from"]["groupby"] . "";
                    $columnSelect = $columnRawName;
                    if (isset($column["from"]["having"])) {
                        $sTableJoin .= " HAVING " . $column["from"]["having"];
                    }

                    $sTableJoin .= ") `" . $column["from"]["table"] . "_$keyCol`  ON " . implode(" AND ", $aFilterJoin);
                }
                else {
                    $sTableJoin .= " $joinType JOIN `" . $column["from"]["table"] . "` `" . $column["from"]["table"] . "_$keyCol`  ON " . implode(" AND ", $aFilterJoin);
                }
            }
            /* = Cas par défaut : pas de jointure et pas de contenu statique.
            `---------------------------------------------------------------------- */
            else {
                $columnRawName = $column["name"];
                for ($index = 1; $index < 10; $index++) {

                    if (in_array($columnRawName, $aColumnsRawAll) === false)
                        break;

                    $columnRawName = $column["name"] . "_" . $index;
                }

                if (isset($column["sql"])) {
                    /* = Si la colone est du sql avec des fonctions
                    `---------------------------------------------------------------------- */
                    $columnAdvancedName = $column["sql"];
                    $column["name"] = $columnAdvancedName;
                }
                else {
                    $columnAdvancedName = "`" . $sTable . "`.`" . $column["name"] . "`";
                    $column["name"] = "`" . $sTable . "`.`" . $column["name"] . "`";
                }

                $columnSelect = $column["name"] . " AS " . $columnRawName;
            }

//            if (isset($column["show"]) && $column["show"]) {
//                $aColumnsAdvanced[] = $columnAdvancedName;
//                $aColumnsFull[] = $column;
//            }
            
            if (!isset($column["searchable"]) || $column["searchable"]) {
                $aColumnsSearchable[] = $columnAdvancedName;
            }
            
            if (isset($column["show_detail"]) && $column["show_detail"]) {
                $aColumnsDetails[$keyCol] = true;
            }
            
            if (isset($column["show"]) && $column["show"]
                || isset($column["show_detail"]) && $column["show_detail"]
            ) {                
                $aColumns[$keyCol] = $column["name"];
                $aColumnsRaw[$keyCol] = $columnRawName;
                $aColumnsFull[] = $column;
                $aColumnsAdvanced[] = $columnAdvancedName;
                $aColumnsContent[$keyCol] = isset($column["content"]) && isset($column["name"]) ? $column["content"] : false;
                if (!$aColumnsFunctions[$keyCol])
                    $aColumnsFunctions[$keyCol] = isset($column["nl2br"]) ? array("nl2br") : false;
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
            
            if (isset($column["bottom"]) && $column["bottom"])
                $aColumnsBottom[$keyCol] = $column["bottom"];
        }
        
        /* = Si on a des filtres addionnal (exemple en param de lurl)
        `-------------------------------------------------------- */
        $sFilterColumn = array_merge($this->_aFilterColumnAdditional, $sFilterColumn);


        /* = Construction du "LIMIT" de la requête.
        `-------------------------------------------------------- */
        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . intval($_POST['iDisplayStart']) . ", " .
                    intval($_POST['iDisplayLength']);
        }

        /* = Construction du "ORDER BY" de la requête.
        `-------------------------------------------------------- */
        $sOrder = array();

        if (isset($_POST['iSortCol_0'])) {
            for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
                if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
                    $indexColumn    = intval($_POST['iSortCol_' . $i]);
//                    $sOrder[]      .= "" . $aColumnsAdvanced[$indexColumn] . " " . $_POST['sSortDir_' . $i];
                    
                    $keyCol         = array_search($indexColumn, $realIndexes);
                    $sOrder[]      .= "" . $aColumnsRawAll[$keyCol] . " " . $_POST['sSortDir_' . $i];
                }
            }            
        }
        if (count($sOrder) > 0)
            $sOrder = "ORDER BY " . implode(",", $sOrder);
        else
            $sOrder = "";

        /* = Filtre sur toutes les colonnes individuelles.
        `-------------------------------------------------------- */
        if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
            $sWhere     = array();//"WHERE (";
            $search     = $this->_db->quote('%' . $_POST['sSearch'] . '%');
        }
        else
            $search     = FALSE;
        
        $sWhere2 = "";
        foreach ($realIndexes as $indexColumn => $realIndex) {
            /* = Filtre sur toutes les colonnes individuelles.
            `-------------------------------------------------------- */
            if ($search
                && $aColumnsAdvanced[$realIndex]
                && isset($sSearchableColumn[$realIndex])
            ) {
                $sWhere[] = $aColumnsAdvanced[$realIndex] . " LIKE $search";
            }            

            /* = Filtre sur les colonnes individuelles
            `-------------------------------------------------------- */
            if (isset($_POST['bSearchable_' . $realIndex])
                && $_POST['bSearchable_' . $realIndex] == "true"
                && $_POST['sSearch_' . $realIndex] != ''
                && $_POST['sSearch_' . $realIndex] != '~'
            ) {    
                /* = Filtre sur les dates (date-range)
                `-------------------------------------------------------- */
                if (isset($aColumnsFull[$realIndex]["filter_field"]) && $aColumnsFull[$realIndex]["filter_field"] == "date-range") {
                    $dateRange = explode("~", $_POST['sSearch_' . $realIndex]);
                    $dateRange[0] = Tools::formate_date_nombre($dateRange[0], "/", "-");
                    $sWhere2 .= "" . $aColumnsAdvanced[$realIndex] . " >= " . $this->_db->quote('' . $dateRange[0] . ' 00:00:00') . "";
                    if ($dateRange[1] != "") {
                        $dateRange[1] = Tools::formate_date_nombre($dateRange[1], "/", "-");
                        $sWhere2 .= " AND " . $aColumnsAdvanced[$realIndex] . " <= " . $this->_db->quote('' . $dateRange[1] . ' 23:59:59') . "";
                    }
                }
                /* = Autres Filtres
                `-------------------------------------------------------- */
                else {
                    $sWhere2 .= "" . $aColumnsAdvanced[$realIndex] . " LIKE " . $this->_db->quote('%' . $_POST['sSearch_' . $realIndex] . '%') . "";
                }
            }
        }
        
        if ($search) 
            $sWhere = " WHERE"
                    . ($sWhere ? " (" . implode(" OR ", $sWhere) . ")" : " 1")
                    . ($sWhere2 ? " AND $sWhere2" : "");
        elseif ($sWhere2)
            $sWhere = " WHERE " . $sWhere2;
        
        if (isset($this->config['where']) && count($this->config['where']))
            $sFilterColumn[] .= implode(" AND ", $this->config['where']);

        if(!isset($sWhere))
            $sWhere = "";
        
        $generalWhere = "";
        if (count($sFilterColumn) > 0) {
            if ($sWhere == "")
                $generalWhere = "WHERE ";
            else {
                $generalWhere = " AND ";
            }
            $generalWhere .= implode(" AND ", $sFilterColumn);
        }

        $sColumnsSelect = array_unique(array_merge($aColumnsSelect, $sIndexColumn));

        
        
        $bottomsQuery = array();
        $bottomsValue = array();
        foreach ($aColumnsBottom as $keyCol => $value) {
            $sQuery         = "SELECT SQL_CALC_FOUND_ROWS $value(" . $aColumns[$keyCol] . ")"
                            . " FROM $sTable"
                            . " $sTableJoin"
                            . " $sWhere"
                            . " $generalWhere";
//            exit("$sWhere | $generalWhere | $sQuery");
            $bottomsValue[$realIndexes[$keyCol]] = $this->_db->query($sQuery)->fetch(PDO::FETCH_COLUMN);
            $bottomsQuery[$realIndexes[$keyCol]] = $sQuery;
            
        }
        
        
        /* = Requête SQL récupérant les données à afficher.
        `-------------------------------------------------------- */
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $sColumnsSelect))
                . " FROM $sTable"
                . " $sTableJoin"
                . " $sWhere"
                . " $generalWhere"
                . " $sOrder"
                . " $sLimit";
        $rResult = $this->_db->query($sQuery);

        /* = Data set length after filtering.
        `-------------------------------------------------------- */
        $sQuery2 = "SELECT FOUND_ROWS()";
        $rResultFilterTotal = $this->_db->query($sQuery2);
        $aResultFilterTotal = $rResultFilterTotal->fetch(PDO::FETCH_NUM);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* = Total data set length.
        `-------------------------------------------------------- */
        $sQuery2 = "SELECT COUNT(*)"
                . " FROM   $sTable"
                . (substr($generalWhere, 0, 5) == "WHERE" ? " " : " WHERE 1 " )
                . " $generalWhere";

        $rResultTotal = $this->_db->query($sQuery2);
        $aResultTotal = $rResultTotal->fetch(PDO::FETCH_NUM);
        $iTotal = $aResultTotal[0];


        /* = On remplit le tableau renvoyé.
        `-------------------------------------------------------- */
        $output['sEcho']                = intval($_POST['sEcho']);
        $output['iTotalRecords']        = $iTotal;
        $output['iTotalDisplayRecords'] = $iFilteredTotal;
        $output['aaData']               = array();
        $output['bottomsValue']         = $bottomsValue;
        
        /* = Différents debug.
        `-------------------------------------------------------- */
        $output['query']                = $sQuery;
        $output['bottomsQuery']         = $bottomsQuery;
        $output['realIndexes']          = $realIndexes;
        $output['aColumnsAdvanced']     = $aColumnsAdvanced;
//        $output['aColumnsFull']         = $aColumnsFull;
        
        while ($aRow = $rResult->fetch(PDO::FETCH_ASSOC)) {
            $row = array();
            
            $row["DT_RowId"] = "";
            if (isset($this->config["table"]["detail"]) && $this->config["table"]["detail"]) {
                $row[] = '';
            }
            
            foreach ($aColumnsRaw as $aColRawKey => $aColRaw) {
                if ($aColumnsRaw[$aColRawKey] != ' ') {

                    
                    if ($aColumnsFunctions[$aColRawKey] !== false && is_array($aColumnsFunctions[$aColRawKey]) === false) {
                        $row[] = $this->$aColumnsFunctions[$aColRawKey]($aRow);
                    } else {
                        
                        if ($aColumnsContent[$aColRawKey] !== false) {
                            $searchTag = array_merge($aColumnsTag, array("[#THIS#]"));
                            $replaceTag = array_merge($aRow, array($aRow[$aColumnsRaw[$aColRawKey]]));
                            $row[] = $aRow[$aColumnsRaw[$aColRawKey]] = str_replace($searchTag, $replaceTag, $aColumnsContent[$aColRawKey]);
                        } else {
                            if (isset($this->config["extra"]["highlightedSearch"]) && $this->config["extra"]["highlightedSearch"] && $_POST["sSearch"] != "" && $aColumnsFunctions[$aColRawKey] === false) {
                                $_POST["sSearch"] = trim($_POST["sSearch"]);
                                $words = strpos($_POST["sSearch"], " ") !== false ? explode(" ", $_POST["sSearch"]) : array($_POST["sSearch"]);
                                $row[] = Tools::highlightedSearch($aRow[$aColumnsRaw[$aColRawKey]], $words);
                            } else {
                                $row[] = $aRow[$aColumnsRaw[$aColRawKey]];
                            }
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

            if (isset($this->config["table"]["detail"]) && $this->config["table"]["detail"]) {
                $currentId = 1;
                foreach ($aColumnsRaw as $aColRawKey => $aColRaw) {
                    
                    if ($aColumnsRaw[$aColRawKey] != ' ') {
                        
                        if (isset($aColumnsDetails[$aColRawKey]) && $aColumnsDetails[$aColRawKey]) {
                            if ($row[$currentId] != "") {
                                $row[0] = '<img class="detail" src="' . $this->_imgPath . 'details_open.png">';
                                break;
                            }
                        }
                        $currentId++;
                    }
                }
            }



            for ($i = 0; $i < count($sIndexColumnRaw); $i++) {
                $row["DT_RowId"] .= $aRow[$sIndexColumnRaw[$i]] . "|";
            }
            $row["DT_RowId"] = substr($row["DT_RowId"], 0, -1);
            $output['aaData'][] = $row;
        }
        
        $this->_view = "";
        $this->_response = json_encode($output);
    }
    
    // --------------------------------------------------------------------

    /**
     * Renvoi soit la vue générée, soit la reponse JSON
     *
     * @return 	string
     */
    public function __toString()
    {
        $rc = new ReflectionClass(__CLASS__);
        $view = $this->_view;
        if ($this->_view == "" && $this->_response != "")
            return $this->_response;
        else {
            return $this->output(dirname($rc->getFileName()) . DIRECTORY_SEPARATOR . $this->_viewPath . $view . ".phtml");
        }
    }
    
    // --------------------------------------------------------------------

    /**
     * Génère la vue
     * 
     * @param string $file chemin de la vue à inclure
     * @return string Rendu de la vue après traitement 
     */
    public function output($file)
    {
        ob_start();
        include($file);
        $output = ob_get_clean();
        return $output;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Renvoi l'url de la page
     * 
     * @return string url complète
     */
    private function _selfURL()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = self::_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Récupérer la partie gauche d'une chaine à partir 
     * d'un caractère ou d'une chaine
     * 
     * @param string $s1 Chaine à rechercher pour couper
     * @param string $s2 Chaine à couper
     * @return string chaine coupée
     */
    private function _strleft($s1, $s2)
    {
        return substr($s1, 0, strpos($s1, $s2));
    }

}
