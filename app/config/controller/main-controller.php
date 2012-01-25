<?php

require "action-controller.php";
require_once "tools.php";

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
     * @var MyPDO
     */
    protected $_dbProject = null;

    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start()
    {
        // Set title of page !
        $this->_seo->setTitle($this->_project->getName());

        // Add Jquery library !
        $this->_javascript->addLibrary('jquery/jquery-1.7.min.js');
        $this->_javascript->addLibrary('jquery-ui/jquery-ui-1.8.16.custom.min.js');
        $this->_javascript->addLibrary("jquery/plugins/jquery.tmpl.min.js");
//        $this->_javascript->addLibrary('http://jqueryui.com/themeroller/themeswitchertool/');
        $this->_javascript->addLibrary('jquery-ui/themeswitcher.js');

        $this->_javascript->addLibrary("jquery/plugins/dropdown/fg.menu.js");
        $this->_javascript->addLibrary("jquery/plugins/jquery.illuminate.0.7.min.js");
        $this->_javascript->addLibrary("jquery/plugins/dropdown/ui.selectmenu.js");
        $this->_javascript->addLibrary("jquery/plugins/jquery.ui-form.js");

        $this->_css->addLibrary("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css");
        $this->_css->addLibrary("jquery/plugins/dropdown/fg.menu.css");
        $this->_css->addLibrary("jquery/plugins/dropdown/ui.selectmenu.css");

//        $this->_view->gabProjects = Tools::listDir('../../*', true, false, false);
        $this->_view->projectName = '';


//        foreach ($this->_view->gabProjects as $keyProject => $project) {
//            if (!file_exists($project . 'app/'))
//                unset($this->_view->gabProjects[$keyProject]);
//        }

        $this->_view->gabProjects = $this->_db->query('SELECT * FROM projet;')->fetchAll();
        if (isset($_SESSION['project']) && $_SESSION['project'] != null) {
            $this->connectDb();
            $this->listConfig();
            $this->_seo->setTitle($this->_seo->getTitle() . ' [' . substr(str_replace('../', '', $_SESSION['project']['nom']), 0, -1) . ']');
        } else {
            if (isset($_REQUEST['controller']) && $_REQUEST['controller'] != 'project' && $_REQUEST['controller'] != 'change' && $_REQUEST['controller'] != 'tools')
                $this->simpleRedirect("config/", true);
        }


        // Add translate file JS !
//        $this->_translate->addTranslationJs('main');
    }

    public function connectDb()
    {
        // On recupere la config de l'appli selon l'environnement !
        if (file_exists($_SESSION['project']['chemin'])) {
            $projectConfig = new Config($_SESSION['project']['chemin'] . "config/local.ini");
            // Connexion à la base de donnée !
            $this->_dbProject = DB::factory($projectConfig->get('database'), $projectConfig->get('dbname', 'database'));
        }
    }

    public function listConfig()
    {
        // On recupere la config de l'appli selon l'environnement !

        $this->_view->myInis = Tools::listDir($_SESSION['project']['chemin'] . "config/*.ini", false, true, false, false, false, false);
        $this->_view->projectName = $_SESSION['project']['nom'];
    }

}