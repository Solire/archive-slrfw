<?php

require_once 'main-controller.php';
require_once 'ini.php';
require_once 'ftp.php';

class ProjectController extends MainController
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

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function changeAction()
    {

        $id = $this->_db->quote($_GET['id']);
        $_SESSION['project'] = $this->_db->query("SELECT *  FROM projet WHERE id = $id;")->fetch();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

//end changeAction()

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function createAction()
    {

//        $_SESSION['project'] = null;
        $this->_javascript->addLibrary("jquery/plugins/jquery.formtotable.js");
        $this->_javascript->addLibrary("jquery/plugins/jquery.selectload.js");
    }

//end createAction()

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function loadAction()
    {
        $this->_view->enable(false);
        $project = $this->_db->quote($_REQUEST['project']);
        $response = array();
        $response['serveur'] = array();
        $response['serveur']['data'] = $this->_db->query("SELECT *  FROM serveur WHERE id_projet = $project;")->fetchAll();
        echo json_encode($response);
    }

    public function paramAction()
    {
        $this->_javascript->addLibrary("jquery/plugins/jquery.formtotable.js");
        $this->_javascript->addLibrary("jquery/plugins/jquery.selectload.js");
//        $_SESSION['project'] = null;
    }

//end createAction()

    public function ftpTestAction()
    {
        $this->_view->enable(false);
        $oFTP = @new FTP($_REQUEST['hote'], $_REQUEST['utilisateur'], $_REQUEST['mot_de_passe']);
        $oFTP->enablePassive();
        $files = $oFTP->ls($_REQUEST['repertoire'], true);
        echo json_encode(array('files' => $files));
    }
    
    public function copyAction()
    {
        $this->_view->enable(false);
        $filename = $_GET['dir'];
        if(!file_exists($filename))
            copy_directory
        echo json_encode(true);
    }
    
    public function copy_directory( $source, $destination ) {
	if ( is_dir( $source ) ) {
		@mkdir( $destination );
		$directory = dir( $source );
		while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
			if ( $readdirectory == '.' || $readdirectory == '..' ) {
				continue;
			}
			$PathDir = $source . '/' . $readdirectory; 
			if ( is_dir( $PathDir ) ) {
				$this->copy_directory( $PathDir, $destination . '/' . $readdirectory );
				continue;
			}
			copy( $PathDir, $destination . '/' . $readdirectory );
		}
 
		$directory->close();
	}else {
		copy( $source, $destination );
	}
}

//end createAction()

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
        $this->_gabProjects = Tools::listDir("../../*$term*", true, false, false);
        foreach ($this->_gabProjects as $project) {
            $response['items'][] = array('id' => $project, 'label' => $project);
        }

        echo json_encode($response);
    }

}

//end class
?>