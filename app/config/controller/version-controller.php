<?php

require_once 'main-controller.php';
require_once 'ini.php';


class VersionController extends MainController
{

    private $_cache = null;


    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
        $this->_cache = Registry::get('cache');

    }//end start()


    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function generalAction()
    {
        $this->_javascript->addLibrary("jquery/plugins/jquery.formtotable.js");
        
    }//end editAction()
    
    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function loadAction()
    {
        $this->_view->enable(false);
        $response = array();
        $response['version'] = array();
        $response['version']['data'] = $this->_dbProject->query("SELECT *, CONCAT('flags/png/', LOWER(suf), '.png') icon  FROM version ORDER BY id;")->fetchAll();
        echo json_encode($response);
    }
    
    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function autoCompleteAction()
    {
        $this->_view->enable(false);
        $table = $_REQUEST['type'];
        $term = $_REQUEST["term"];
        $response = array();
        $response['items'] = $this->_dbProject->query("SELECT id_pays id, nom_pays label, code_pays, CONCAT('flags/png/', LOWER(code_pays), '.png') icon  FROM pays WHERE nom_pays LIKE  '%$term%' ORDER BY id;")->fetchAll();

        echo json_encode($response);
    }



}//end class

?>