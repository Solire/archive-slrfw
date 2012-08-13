<?php

/**
 * @package Controller
 */

/**
 * @package Controller
 * @api
 */
class FrontController {

    /**
     * Configuration principale du site
     * @var Config
     */
    public static $mainConfig;

    /**
     * Configuration de l'environnement utilisé
     * @var Config
     */
    public static $envConfig;
    private static $_singleton = null;
    private $_dirs = null;
    private $_default = null;
    private $_format = null;
    private $_debug = null;
    protected $_applicationConfig = null;

    const CONTROLLER_FILE_NOT_EXISTS = 0;
    const CONTROLLER_CLASS_NOT_EXISTS = 1;
    const CONTROLLER_ACTION_NOT_EXISTS = 2;
    const VIEW_FILE_NOT_EXISTS = 3;

    private function __construct($application) {
        $this->_dirs = self::$mainConfig->get("dirs");
        $this->_applicationConfig = self::$mainConfig->get('app_' . $application);
        $this->_format = self::$mainConfig->get("format");
        $this->_debug = self::$mainConfig->get("debug");
    }

    /**
     * Renvois une instance du FrontController
     * @return FrontController
     */
    public static function getInstance($application) {
        if (!self::$_singleton)
            self::$_singleton = new self($application);
        return self::$_singleton;
    }

    /**
     * Initialise les données nécessaires pour FrontController
     */
    public function init() {

        /* = Chargement de la configuration
          ------------------------------- */
        self::$mainConfig = new Config('../config/main.ini');

        /* = Detection de l'environnement
          ------------------------------- */
        $localHostnames = explode(',', self::$mainConfig->get('detect', 'development'));
        if (in_array($_SERVER['SERVER_NAME'], $localHostnames) === true)
            $env = 'local';
        else
            $env = 'online';

        /* = Detection de l'application à charger (Defaut : front)
          ------------------------------- */
        $application = isset($_REQUEST['application']) ? $_REQUEST['application'] : 'front';

        self::$envConfig = new Config("../config/$env.ini");


        /* = Fichiers de configuration
          ------------------------------- */
        Registry::set('mainconfig', self::$mainConfig);
        Registry::set('envconfig', self::$envConfig);
        $appConfig = null;
        if (file_exists("../config/app_$application.ini"))
            $appConfig = new Config("../config/app_$application.ini");
        Registry::set("appconfig", $appConfig);

        /* = base de données
          ------------------------------- */
        $db = DB::factory(self::$envConfig->get('database'));
        Registry::set('db', $db);

        /* = url
          ------------------------------- */
        $baseHrefSuffix = isset($_REQUEST['application']) ? $_REQUEST['application'] . '/' : '';
        Registry::set('base', self::$envConfig->get('url', 'base'));
        Registry::set('basehref', self::$envConfig->get('url', 'base') . $baseHrefSuffix);
        Registry::set('baseroot', self::$envConfig->get('root', 'base'));

        /* = Informations générales sur le site
          ------------------------------- */
        $baseHrefSuffix = isset($_REQUEST['application']) ? $_REQUEST['application'] . '/' : '';
        Registry::set('project-name', self::$mainConfig->get('name', 'project'));
        $emails = self::$envConfig->get("email");

        /* = Permet de forcer une version (utile en dev ou recette)
          ------------------------------- */
        if (isset($_GET['version-force'])) {
            $_SESSION['version-force'] = $_GET['version-force'];
        }
        if (isset($_SESSION['version-force'])) {
            $sufVersion = $_SESSION['version-force'];
        } else {
            $sufVersion = isset($_GET['version']) ? $_GET['version'] : 'FR';
        }

        if ($env != 'local') {
            $serverUrl = str_replace('www.', '', $_SERVER['SERVER_NAME']);
            Registry::set("url", "http://www." . $serverUrl . '/');
            Registry::set("basehref", "http://www." . $serverUrl . '/');
            
            
            Registry::set("email", $emails);
            
        } else {
            $serverUrl = str_replace("solire-02", $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'], Registry::get("basehref"));
            Registry::set("url", $serverUrl);
            Registry::set("basehref", $serverUrl);
            //Ajout d'un prefix au mail
            if (isset($emails["prefix"]) && $emails["prefix"] != "") {
                $prefix = $emails["prefix"];
                unset($emails["prefix"]);
                foreach ($emails as &$email) {
                    $email = $prefix . $email;
                }
            }
            Registry::set("email", $emails);
        }

        if(isset($_GET["application"])) {
            $query = "SELECT * FROM `gab_api` WHERE `name` = " . $db->quote($_GET["application"]);
            $api = $db->query($query)->fetch(PDO::FETCH_ASSOC);
        }
        

        $query = "SELECT * FROM `version` WHERE `domaine` = '$serverUrl'";
        $version = $db->query($query)->fetch(PDO::FETCH_ASSOC);

        if (!isset($version["id"])) {
            $query = "SELECT * FROM `version` WHERE `suf` LIKE " . $db->quote($sufVersion);
            $version = $db->query($query)->fetch(PDO::FETCH_ASSOC);
        }


        Registry::set("analytics", $version['analytics']);
        define("ID_VERSION", $version['id']);
        define("SUF_VERSION", $version['suf']);
        define("ID_API", isset($api['id']) ? $api['id'] : 1);
    }

    public static function run() {
        if (isset($_REQUEST['application']) && !empty($_REQUEST['application'])) {
            $application = $_REQUEST['application'];
        } else {
            $application = 'front';
        }
        $front = self::getInstance($application);
        $applicationPath = '../' . $front->_applicationConfig['path'];

        $controller = str_replace("-", "", strtolower(isset($_GET["controller"]) ? $_GET["controller"] : $front->getDefault("controller")));
        $action = str_replace("-", "", strtolower(isset($_GET["action"]) ? $_GET["action"] : $front->getDefault("action")));
        $file = sprintf($front->getDir("controllers"), $applicationPath) . sprintf($front->getFormat("controller-file"), $controller);
        $class = sprintf($front->getFormat("controller-class"), $controller);
        $method = sprintf($front->getFormat("controller-action"), $action);

        if (!file_exists($file)) {
            $front->debug(self::CONTROLLER_FILE_NOT_EXISTS, array($file));
            return false;
        }

        require_once ($file);

        if (!class_exists($class)) {
            $front->debug(self::CONTROLLER_CLASS_NOT_EXISTS, array($class));
            return false;
        }

        if (!method_exists($class, $method)) {
            $front->debug(self::CONTROLLER_ACTION_NOT_EXISTS, array($action, $controller, $file));
            return false;
        }

        $instance = new $class();

        $view = $instance->getView();
        $view->setDir(sprintf($front->getDir("views"), $applicationPath));
        $view->setTemplate("main");
        $view->setFormat($front->getFormat("view-file"));
        $view->base = $front->getDir("base");
        $instance->start();
        $instance->$method();
        $instance->shutdown();

        if ($view->isEnabled()) {
            if (!$view->exists($controller, $action)) {
                $front->debug(self::VIEW_FILE_NOT_EXISTS, array($view));
                return false;
            }

            $view->display($controller, $action, false);
        }

        return true;
    }

    public function getDefault($key) {
        return (isset($this->_applicationConfig[$key . '-default']) ? $this->_applicationConfig[$key . '-default'] : '');
    }

    public function getDir($key) {
        return (isset($this->_dirs[$key]) ? $this->_dirs[$key] : '');
    }

    public function getFormat($key) {
        return (isset($this->_format[$key]) ? $this->_format[$key] : '');
    }

    public function debug($idx, $params) {
        if ($this->_debug["enable"]) {
            $errors = array(
                self::CONTROLLER_FILE_NOT_EXISTS => "Le fichier de contr&ocirc;leur <strong>%s</strong> n'existe pas.",
                self::CONTROLLER_CLASS_NOT_EXISTS => "La classe de contr&ocirc;leur <strong>%s</strong> n'existe pas.",
                self::CONTROLLER_ACTION_NOT_EXISTS => "Impossible de trouver l'action <strong>%s</strong> pour le contr&ocirc;leur <strong>%s</strong> dans le fichier <strong>%s</strong>.",
                self::VIEW_FILE_NOT_EXISTS => "Le fichier de vue <strong>%s</strong> n'existe pas.",
            );
            $message = $errors[$idx];
            throw new MarvinException(new Exception($message));
        } else {
            $error = new HttpException('');
            $error->http(404);
            throw $error;
        }
    }

}