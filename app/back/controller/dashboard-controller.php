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
        if(file_exists("../app/datatable/" . ucfirst($_GET["name"]) . "Datatable.php")) {
            require_once "../app/datatable/" . ucfirst($_GET["name"]) . "Datatable.php";
            $datatableClassName = ucfirst($_GET["name"]) . "Datatable";
        } else {
            $datatableClassName = "Datatable";
        }
        $datatable = new $datatableClassName($_GET, $_GET["name"], $this->_db, "./datatable/", "./datatable/", "images/datatable/");
        
        $datatable->start();
        $datatableString = $datatable;
        $data = $datatableString;

        if (isset($_GET["json"]) || (isset($_GET["nomain"]) && $_GET["nomain"] == 1)) {
            echo $data;
            exit();
        }
        $datatable = $data;
        $this->_view->datatableRender = $datatable;
        
    }

    

}

//end class