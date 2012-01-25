<?php
require_once 'main-controller.php';

class PageController extends MainController
{

    private $_cache = null;
    
    /**
     *
     * @var page
     */
    private $_page = null;
    
    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start() {
        parent::start();
    }//end start()

    /**
     * 
     * @return void
     */
    public function listeprojetsAction() {
		$this->_javascript->addLibrary("back/liste.js");
        
        $this->_view->action = "projet";
        $this->_pages = $this->_managers->getManagerOf("gabarit")->getList(0, 2);
        $this->_view->pages = $this->_pages;
    }
    
    /**
     * 
     * @return void
     */
    public function childrenAction() {
        $this->_view->main(FALSE);
        $this->_pages = $this->_managers->getManagerOf("gabarit")->getList($_REQUEST['id_parent']);
        if (count($this->_pages) == 0)
            exit();
        $this->_view->pages = $this->_pages;
    }
    
    /**
     * 
     * @return void
     */
    public function displayAction() {
//        header('Content-type: text/html; charset=UTF-8');
//        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
//        header("Cache-Control: no-store, no-cache, must-revalidate");
//        header("Cache-Control: post-check=0, pre-check=0", false);
//        header("Pragma: no-cache");
        
        $id_gab_page = isset($_GET['id_gab_page']) ? $_GET['id_gab_page'] : 0;
        $id_gabarit = isset($_GET['id_gabarit']) ? $_GET['id_gabarit'] : 1;

        if ($id_gab_page == 1)
            $this->_view->action = "biographie";
        elseif ($id_gab_page == 2)
            $this->_view->action = "contact";
        else
            $this->_view->action = "projet";
            
        $this->_javascript->addLibrary("back/tiny_mce/tiny_mce.js");
        $this->_javascript->addLibrary("back/plupload/plupload.full.min.js");
        $this->_javascript->addLibrary("back/formgabarit.js");
        $this->_javascript->addLibrary("back/affichegabarit.js");
        
        $this->_form = '';
        
        if ($id_gab_page) {
            $versions = $this->_db->query("SELECT * FROM `version`")->fetchAll(PDO::FETCH_ASSOC);
            $form = '';
            $devant = '';
            foreach ($versions as $version) {
                $page = $this->_managers->getManagerOf("gabarit")->getPage($id_gab_page, 0, $version['id']);
                
//                $devant .= '<!--' . print_r($page, true) . '-->';
                $devant .= '<a href="#" class="button bleu openlang' . ($version['id'] == BACK_ID_VERSION ? ' active' : ' translucide')
                         . '"><span class="bleu">Langue : <img src="img/back/flag-' . $version['suf'] . '.gif" alt="'
                         . $version['nom'] . '" /></span></a>';
                
                $form .= '<div class="langue" style="clear:both;' . ($version['id'] == BACK_ID_VERSION ? '' : ' display:none;')
                       . '"><div class="clearin"></div>'
                       . $page->getForm($this->_getFormRetour($page->getGabarit()->getName()))
                       . '</div>';
            }
            
            $this->_page = $this->_managers->getManagerOf("gabarit")->getPage($id_gab_page, 0);
            
            $this->_form .= '<div>' . $devant . '</div>' . $form;
        }
        else {        
            $this->_managers->getManagerOf("gabarit")->createTables($id_gabarit);
            $this->_page = $this->_managers->getManagerOf("gabarit")->getPage(0, $id_gabarit);
            
//            $parents = $this->_managers->getManagerOf("gabarit")->getList($this->_page->getGabarit()->getIdParent());
//            echo '<pre>' . print_r($parents, true) . '</pre>';
            
            $form = $this->_page->getForm($this->_getFormRetour($this->_page->getGabarit()->getName()));
            $this->_form = $form;        
        }
        
        $gab_name = $this->_page->getGabarit()->getName();
        $this->_view->page = $this->_page;
        $this->_view->form = $this->_form;
    }
    
    /**
     *
     * @param string $gab_name
     * @return string 
     */
    private function _getFormRetour($gab_name) {
        switch($gab_name) {
            case "dossier" :
            case "page" :
                $retour = "page/listedossiers.html";
                break;
            
            case "rubrique" :
            case "sous_rubrique" :
                $retour = "page/listerubriques.html";
                break;
            
            default :
                $retour = "page/display.html";
        }
        
        return $retour;
    }
    
    
    /**
     * 
     * @return void
     */
    public function saveAction() {
        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $this->_page = $this->_managers->getManagerOf("gabarit")->save($_POST);

//        if ($this->_page->getGabarit()->getName() == 'rubrique' || $this->_page->getGabarit()->getName() == 'sous_rubrique')
//            $this->_managers->getManagerOf("file")->createFolder($this->_page->getMeta("id"), self::$_dirs['media']); // addFolder($this->_page->getMeta("id"));
        
        $json = array(
            "status" => $this->_page ? "success" : "error",
            "search" => "?id_gab_page=" . $this->_page->getMeta("id"),
            "id_gab_page" => $this->_page->getMeta("id"),
//            "debug"     => $this->_page->getValues(),
//            "request" => $_REQUEST,
//            "post" => $_POST,
//            "get" => $_GET,
//            "cookie" => $_COOKIE,
//            "id_version" => ID_VERSION,
        );
        
        exit(json_encode($json));
    }
    
    
    /**
     * 
     * @return void
     */
    public function autocompleteAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);
        
        $json = array();
        $dejaLiees = is_array($_REQUEST['deja']) ? $_REQUEST['deja'] : array();
        
        if (!isset ($_REQUEST['id_gabarit']) || !is_numeric($_REQUEST['id_gabarit']))
            exit(json_encode ($json));
        
        $pages = $this->_managers->getManagerOf("gabarit")->getSearch($_GET['term'], $_REQUEST['id_gabarit']);
        foreach ($pages as $page) {
            if (!in_array($page->getMeta('id'), $dejaLiees))
                $json[] = array("value" => $page->getMeta('id'), "label" => $page->getMeta('titre'), "visible" => $page->getMeta('titre'));
        }
        
        exit(json_encode ($json));
    }

    
    /**
     * 
     * @return void
     */
    public function visibleAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");
        
        if (is_numeric($_POST['id_gab_page']) && is_numeric($_POST['visible'])) {
            $query = "UPDATE `gab_page` SET `visible` = " . $_POST['visible'] . " WHERE `id` = " . $_POST['id_gab_page'];
            if ($this->_db->query($query)) {
                $json['status'] = "success";
                $json['debug'] = $query;
            }
        }
        
        
        exit (json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function enavantAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");
        
        if (is_numeric($_POST['id_gab_page']) && is_numeric($_POST['en_avant'])) {
            $query = "UPDATE `gab_page` SET `en_avant` = " . $_POST['en_avant'] . " WHERE `id` = " . $_POST['id_gab_page'];
            if ($this->_db->query($query)) {
                $json['status'] = "success";
                $json['debug'] = $query;
            }
        }
        
        exit (json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function deleteAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");

        if (is_numeric($_REQUEST['id_gab_page'])) {
            $prepStmt = $this->_db->prepare("UPDATE `gab_page` SET `suppr` = 1 AND `date_suppr` = NOW() WHERE `id` = :id");
            $prepStmt->bindValue(":id", $_REQUEST['id_gab_page'], PDO::PARAM_INT);
            if ($prepStmt->execute()) {
                $json['status'] = "success";
            }
        }

        exit (json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function orderAction() {
        $ok = true;

        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $prepStmt = $this->_db->prepare("UPDATE `gab_page` SET `ordre` = :ordre WHERE `id` = :id");
        foreach ($_POST['positions'] as $id => $ordre) {
            $prepStmt->bindValue(":ordre", $ordre, PDO::PARAM_INT);
            $prepStmt->bindValue(":id", $id, PDO::PARAM_INT);
            $tmp = $prepStmt->execute();
            if ($ok)
                $ok = $tmp;
        }

        echo $ok ? 'Succès' : 'Echec';

        return FALSE;
    }

}//end class