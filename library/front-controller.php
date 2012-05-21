<?php

class FrontController
{

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

    private function __construct($mainIni, $envIni, $application)
    {
        $this->_dirs = $mainIni->get("dirs");
        $this->_applicationConfig = $mainIni->get('app_' . $application);
        $this->_format = $mainIni->get("format");
        $this->_debug = $envIni->get("debug");
    }

    public static function getInstance($mainIni, $envIni, $application)
    {
        if (!self::$_singleton)
            self::$_singleton = new self($mainIni, $envIni, $application);
        return self::$_singleton;
    }

    public static function run($mainIni, $envIni, $application = "front")
    {
        $front = self::getInstance($mainIni, $envIni, $application);
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

    public function getDefault($key)
    {
        return (isset($this->_applicationConfig[$key . '-default']) ? $this->_applicationConfig[$key . '-default'] : '');
    }

    public function getDir($key)
    {
        return (isset($this->_dirs[$key]) ? $this->_dirs[$key] : '');
    }

    public function getFormat($key)
    {
        return (isset($this->_format[$key]) ? $this->_format[$key] : '');
    }

    public function debug($idx, $params)
    {
        if ($this->_debug["enable"]) {
            $errors = array(
                self::CONTROLLER_FILE_NOT_EXISTS => "Le fichier de contr&ocirc;leur <strong>%s</strong> n'existe pas.",
                self::CONTROLLER_CLASS_NOT_EXISTS => "La classe de contr&ocirc;leur <strong>%s</strong> n'existe pas.",
                self::CONTROLLER_ACTION_NOT_EXISTS => "Impossible de trouver l'action <strong>%s</strong> pour le contr&ocirc;leur <strong>%s</strong> dans le fichier <strong>%s</strong>.",
                self::VIEW_FILE_NOT_EXISTS => "Le fichier de vue <strong>%s</strong> n'existe pas.",
            );

            $error = date("[d-m-Y][h:i:s]") . " " . vsprintf($errors[$idx], $params);

            $fp = fopen($this->_debug["framework"], "a+");
            fputs($fp, date("[d-m-Y][h:i:s]") . " " . vsprintf($errors[$idx], $params) . PHP_EOL);
            fclose($fp);

            if ($this->_debug["display"])
                echo $error;
        }
    }

}