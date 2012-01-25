<?php

require_once 'main-controller.php';

class ToolsController extends MainController
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
    }

//end start()

    
    public function formSaveAction()
    {
        $this->_view->enable(false);
        if(isset($_POST['base-main'])) {
            $this->_dbProject = $this->_db;
            unset($_POST['base-main']);
        }
        $table = $_POST['tabletoinsert'];
        unset($_POST['tabletoinsert']);
        if (isset($_POST["id"])) {
            $response["status"] = $this->_dbProject->update($table, $_POST, 'id=' . $_POST["id"]);
        } else {
            $response["status"] = $this->_dbProject->insert($table, $_POST);
            $_POST["id"] = $this->_dbProject->lastInsertId();
        }

        echo json_encode($_POST);
    }

    
}

//end class
?>