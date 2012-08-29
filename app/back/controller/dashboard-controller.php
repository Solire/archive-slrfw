<?php

require_once 'main-controller.php';
require_once 'datatable/datatable.php';

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
    }

//end start()

    /**
     * 
     * @return void
     */
    public function startAction() {
        if (isset($_GET["name"])) {
            if (!is_array($_GET["name"])) {
                $configsName[] = $_GET["name"];
            } else {
                $configsName = $_GET["name"];
            }
            
            $this->_view->datatableRender = "";

            foreach ($configsName as $configName) {
                if (file_exists("../app/datatable/" . ucfirst($configName) . "Datatable.php")) {
                    require_once "../app/datatable/" . ucfirst($configName) . "Datatable.php";
                    $datatableClassName = ucfirst($configName) . "Datatable";
                } else {
                    $datatableClassName = "Datatable";
                }
                $datatable = new $datatableClassName($_GET, $configName, $this->_db, "./datatable/", "./datatable/", "img/datatable/");

                $datatable->start();
                $datatableString = $datatable;
                $data = $datatableString;

                if (isset($_GET["json"]) || (isset($_GET["nomain"]) && $_GET["nomain"] == 1)) {
                    echo $data;
                    exit();
                }
                $datatable = $data;
                $this->_view->datatableRender .= $datatable;
                if (count($configsName) > 1) {
                    $this->_view->datatableRender .= '<hr />';
                }
            }
        }
    }

}

//end class