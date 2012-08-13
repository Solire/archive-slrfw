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
class MainController extends ActionController {

    /**
     *
     * @var utilisateur
     */
    protected $_utilisateur;

    /**
     *
     * @var array
     */
    protected $_api;

    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start() {

        $suffixApi = "";
        $nameApi = isset($_GET["api"]) ? $_GET["api"] : "main";
        $idApi = $this->_db->query("SELECT id FROM `gab_api` WHERE name = " . $this->_db->quote($nameApi))->fetch(PDO::FETCH_COLUMN);

        if (intval($idApi) == 0) {
            $idApi = 1;
        }

        $this->_log = new Log($this->_db, '', 0, 'back_log');


        $this->_api = $this->_db->query("SELECT * FROM `gab_api` WHERE id = $idApi")->fetch(PDO::FETCH_ASSOC);
        $this->_apis = $this->_db->query("SELECT * FROM `gab_api`")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        if ($this->_api["id"] != 1) {
            $suffixApi = $this->_api["name"] . "/";
            Registry::set('basehref', Registry::get('basehref') . $suffixApi);
            $this->_url = Registry::get('basehref');
            $this->_view->url = Registry::get('basehref');
        }
        define("BACK_ID_API", $this->_api["id"]);

        $this->_javascript->addLibrary("back/jquery/jquery-1.4.4.min.js");
        $this->_javascript->addLibrary("back/jquery/jquery-ui-1.8.9.custom.min.js");
        $this->_javascript->addLibrary("back/main.js");
        $this->_javascript->addLibrary("back/jquery/jquery.cookie.js");
        $this->_javascript->addLibrary("back/jquery/sticky.js");

        $this->_javascript->addLibrary("back/jquery/jquery.stickyPanel.min.js");

        $this->_javascript->addLibrary("back/newstyle.js");
        $this->_css->addLibrary("back/jquery-ui-1.8.7.custom.css");

        $this->_css->addLibrary("jquery-ui/custom-theme/jquery-ui-1.8.22.custom.css");



        $this->_css->addLibrary("http://www.solire.fr/style_solire_fw/css/back/newstyle.css");
        $this->_css->addLibrary("back/sticky.css");

        $this->_view->site = Registry::get("project-name");
        $this->_view->controller = isset($_GET["controller"]) ? $_GET["controller"] : "";
        $this->_view->action = isset($_GET["action"]) ? $_GET["action"] : "";

        $this->_utilisateurManager = new utilisateurManager();
        $this->_gabaritManager = new gabaritManager();
        $this->_fileManager = new fileManager();
        $this->_versions = $this->_db->query("SELECT `version`.id, `version`.* FROM `version` WHERE `version`.`id_api` = " . $this->_api["id"])->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);


        if ($_POST) {
            $this->_post = $_POST;
        }

        if (isset($_GET['id_version'])) {
            $id_version = isset($_GET['id_version']) ? $_GET['id_version'] : $_POST['id_version'];
            setcookie("id_version", $id_version, 0, "/" . Registry::get("baseroot") . "back/" . $suffixApi);
            define("BACK_ID_VERSION", $id_version);
        } elseif (isset($_COOKIE['id_version']) && isset($this->_versions[$_COOKIE['id_version']]))
            define("BACK_ID_VERSION", $_COOKIE['id_version']);
        else
            define("BACK_ID_VERSION", 1);

        $this->_utilisateur = $this->_utilisateurManager->get();


        if (isset($this->_post['log']) && isset($this->_post['pwd']) && ($this->_post['log'] == "" || $this->_post['pwd'] == "")) {
            exit(json_encode(array("success" => false, "message" => "Veuillez renseigner l'identifiant et le mot de passe")));
        }

        if (isset($this->_post['log']) && isset($this->_post['pwd']) && $this->_post['log'] && $this->_post['pwd']) {
            $success = $this->_utilisateurManager->connect($this->_utilisateur, $this->_post['log'], $this->_post['pwd']);
            if ($success) {
                $this->_log->logThis("Connexion réussie", $this->_utilisateur->get("id"));
            } else {
                $this->_log->logThis("Connexion échouée", 0, "Identifiant : " . $this->_post['log']);
            }

            exit(json_encode(array("success" => $success, "message" => $success ? "Connexion réussie, vous allez être redirigé" : "Identifiant ou mot de passe invalide")));
        }

        if (!$this->_utilisateur->isconnected() && isset($_GET['action']) && isset($_GET['controller']) && $_GET['controller'] . "/" . $_GET['action'] != "sign/start")
            $this->simpleRedirect("sign/start.html", TRUE);

        $this->_view->utilisateur = $this->_utilisateur;
        $this->_view->apis = $this->_apis;
        $this->_view->api = $this->_api;
        $this->_view->javascript = $this->_javascript;
        $this->_view->css = $this->_css;
        $this->_view->versions = $this->_versions;
        $this->_view->versions = $this->_db->query("SELECT `version`.id, `version`.* FROM `version` WHERE `version`.`id_api` = " . $this->_api["id"])->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        $this->_view->breadCrumbs = array();
        $this->_view->breadCrumbs[] = array(
            "label" => '<img src="img/back/gray_dark/home_12x12.png"> ' . $this->_view->site,
            "url" => "./",
        );

        $this->_view->appConfig = $this->_appConfig;

        //On recupere la configuration du module pages (Menu + liste)
        $configMain = Registry::get('mainconfig');
        require_once $configMain->get('back', 'dirs') . "page" . ".cfg.php";
        $this->_configPageModule = $config;
        $this->_view->menuPage = array();
        foreach ($this->_configPageModule as $configPage) {
            $this->_view->menuPage[] = array(
                "label" => $configPage["label"],
            );
        }

        $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit` WHERE `gab_gabarit`.`id_api` = " . $this->_api["id"];
        $this->_gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $this->_view->pagesNonTraduites = $this->_db->query("SELECT * FROM `gab_page` gp WHERE rewriting = '' AND gp.suppr = 0 AND id_version = " . BACK_ID_VERSION)->fetchAll(PDO::FETCH_ASSOC);
    }

}