<?php
require_once 'main-controller.php';

class PageController extends MainController
{

    private $_cache = null;
    
    /**
     *
     * @var gabarit_page
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
    public function listeAction() {
		$this->_javascript->addLibrary("back/liste.js");
        
        if ($this->_utilisateur->get("niveau") == "solire")
            $query = "SELECT * FROM `gab_gabarit`";
        else
            $query = "SELECT * FROM `gab_gabarit` WHERE `id` > 1";
        $this->_view->gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $this->_pages = $this->_gabaritManager->getList(BACK_ID_VERSION, 0);
        $this->_view->pages = $this->_pages;
    }
    
    /**
     * 
     * @return void
     */
    public function childrenAction() {
        $this->_view->main(FALSE);
        $this->_pages = $this->_gabaritManager->getList(BACK_ID_VERSION, $_REQUEST['id_parent']);
        if (count($this->_pages) == 0)
            exit();
        $this->_view->pages = $this->_pages;
    }
    
    /**
     * 
     * @return void
     */
    public function displayAction() {
        $upload_path = $this->_mainConfig->get("path", "upload");
        
        $id_gab_page = isset($_GET['id_gab_page']) ? $_GET['id_gab_page'] : 0;
        $id_gabarit  = isset($_GET['id_gabarit']) ? $_GET['id_gabarit'] : 1;

        $this->_view->action = "liste";
            
        $this->_javascript->addLibrary("back/tiny_mce/tiny_mce.js");
        $this->_javascript->addLibrary("back/jquery/jquery.livequery.min.js");
        $this->_javascript->addLibrary("back/autocomplete.js");
        $this->_javascript->addLibrary("back/plupload/plupload.full.min.js");
        $this->_javascript->addLibrary("back/formgabarit.js");
        $this->_javascript->addLibrary("back/affichegabarit.js");
        
        $this->_form = '';
        
        if ($id_gab_page) {
            $versions = $this->_db->query("SELECT * FROM `version`")->fetchAll(PDO::FETCH_ASSOC);
            $form = '';
            $devant = '';
            foreach ($versions as $version) {
                $page = $this->_gabaritManager->getPage($version['id'], $id_gab_page);
                
                $devant .= '<a href="#" class="button bleu openlang' . ($version['id'] == BACK_ID_VERSION ? ' active' : ' translucide')
                         . '"><span class="bleu">Langue : <img src="img/flags/png/' . strtolower($version['suf']) . '.png" alt="'
                         . $version['nom'] . '" /></span></a>';
                
                $form .= '<div class="langue" style="clear:both;' . ($version['id'] == BACK_ID_VERSION ? '' : ' display:none;')
                       . '"><div class="clearin"></div>'
                       . $page->getForm("page/save.html", "page/liste.html", $upload_path)
                       . '</div>';
            }
            
            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, $id_gab_page);
            
            $this->_form .= '<div>' . $devant . '</div>' . $form;
        }
        else {        
            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, 0, $id_gabarit);
            
            $form = $this->_page->getForm("page/save.html", "page/liste.html", $upload_path);
            $this->_form = $form;        
        }
        
        $this->_view->page = $this->_page;
        $this->_view->form = $this->_form;
    }
    
    /**
     * 
     * @return void
     */
    public function saveAction() {
        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $this->_page = $this->_gabaritManager->save($_POST);
        
        $contenu = '<a href="' . Registry::get("basehref") . 'page/display.html?id_gab_page='
                 . $this->_page->getMeta("id") . '">'
                 . $this->_page->getMeta("titre") . '</a>';
        
        $headers = "From: ". Registry::get("mail-contact") . "\r\n"
                 . "Reply-To: ". Registry::get("mail-contact") . "\r\n"
                 . "Bcc: contact@solire.fr \r\n"
                 . "X-Mailer: PHP/" . phpversion();
        
        Tools::mail_utf8("Modif site <modif@solire.fr>", "Modification de contenu sur " . Registry::get("site"), $contenu, $headers);
        
        $json = array(
            "status" => $this->_page ? "success" : "error",
            "search" => "?id_gab_page=" . $this->_page->getMeta("id"),
            "id_gab_page" => $this->_page->getMeta("id"),
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
        
        $pages = $this->_gabaritManager->getSearch(BACK_ID_VERSION, $_GET['term'], $_REQUEST['id_gabarit']);
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
    public function autocompleteJoinAction()
    {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $json = array();
        $term = $_REQUEST["term"];
        $idField = $_REQUEST["id_field"];
        $labelField = $_REQUEST["label_field"];
        $idGabPage = $_REQUEST["id_gab_page"];
        $queryFilter = str_replace("[ID]", $idGabPage, $_REQUEST["query_filter"]);
        $table = $_REQUEST["table"];
        $lang = BACK_ID_VERSION;

        $sql = "SELECT `$table`.$idField id, `$table`.$labelField label
                    FROM `$table` 
                    WHERE id_version = $lang " . ($queryFilter != "" ? "AND (" . $queryFilter . ")" : "") . " AND `$table`.`$labelField`  LIKE '%$term%'";
        
        $json = $this->_db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        exit(json_encode($json));
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
    public function deleteAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");

        if (is_numeric($_POST['id_gab_page'])) {
            $query = "UPDATE `gab_page` SET `suppr` = 1, `date_modif` = NOW() WHERE `id` = " . $_POST['id_gab_page'];
            $json['query'] = $query;
            if ($this->_db->exec($query)) {
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