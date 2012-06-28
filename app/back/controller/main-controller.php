<?php

require "action-controller.php";

/**
 * Class example of MainController with always call
 * 
 * @category Application
 * @package  Controller
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 */
class MainController extends ActionController
{
    /**
     *
     * @var utilisateur
     */
    protected $_utilisateur;


    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start() {
        $this->_javascript->addLibrary("back/jquery/jquery-1.4.4.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery-ui-1.8.9.custom.min.js");
        $this->_javascript->addLibrary("back/main.js");
        $this->_javascript->addLibrary("back/jquery/jquery.cookie.js");
        $this->_javascript->addLibrary("back/jquery/sticky.js");
        
        $this->_javascript->addLibrary("back/jquery/jquery.stickyPanel.min.js");
        
        $this->_javascript->addLibrary("back/newstyle.js");
        $this->_css->addLibrary("back/jquery-ui-1.8.7.custom.css");
        $this->_css->addLibrary("http://www.solire.fr/style_solire_fw/css/back/newstyle.css");
        $this->_css->addLibrary("back/sticky.css");
        
        $this->_view->site = Registry::get("project-name");
        $this->_view->controller = isset($_GET["controller"]) ? $_GET["controller"] : "";
        
        $this->_utilisateurManager = new utilisateurManager();
        $this->_gabaritManager     = new gabaritManager();
        $this->_fileManager        = new fileManager();
        
		if ($_POST) {
			$this->_post = $_POST;
		}
        
        if (isset ($_GET['id_version'])) {
            $id_version = isset ($_GET['id_version']) ? $_GET['id_version'] : $_POST['id_version'];
            setcookie ("id_version", $id_version, 0, "/" . Registry::get("baseroot") . "back/");
            define("BACK_ID_VERSION", $id_version);
        }
        elseif (isset ($_COOKIE['id_version']))
            define("BACK_ID_VERSION", $_COOKIE['id_version']);
        else
            define("BACK_ID_VERSION", 1);
        
        $this->_utilisateur = $this->_utilisateurManager->get();
        
        if (isset($this->_post['log']) && isset($this->_post['pwd']) && $this->_post['log'] && $this->_post['pwd'])
            $this->_utilisateurManager->connect($this->_utilisateur, $this->_post['log'], $this->_post['pwd']);
        
        if (!$this->_utilisateur->isconnected() && isset($_GET['action']) && $_GET['action'] != "start")
            $this->simpleRedirect ("sign/start.html", TRUE);
        
        $this->_view->utilisateur = $this->_utilisateur;
        
        $this->_view->javascript = $this->_javascript;
        $this->_view->css = $this->_css;
        $this->_view->versions = $this->_db->query("SELECT `version`.id, `version`.* FROM `version`")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        
        $this->_view->breadCrumbs = array();
        $this->_view->breadCrumbs[] = array(
            "label" => '<img src="img/back/gray_dark/home_12x12.png"> ' . $this->_view->site,
            "url" => "./",
        );
        
        $this->_view->pagesNonTraduites = $this->_db->query("SELECT * FROM `gab_page` gp WHERE rewriting = '' AND gp.suppr = 0 AND id_gabarit != 2 AND id_version = " . BACK_ID_VERSION)->fetchAll(PDO::FETCH_ASSOC);
    }
}