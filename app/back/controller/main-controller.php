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
        $this->_javascript->addLibrary("back/menu.js");
        $this->_javascript->addLibrary("back/jquery/jquery.cookie.js");
        
        $this->_view->site = Registry::get("site");
        $this->_view->action = "liste";
        
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
    }
}