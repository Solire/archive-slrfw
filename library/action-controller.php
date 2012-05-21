<?php

require_once 'view.php';
require_once 'translate-mysql.php';
require_once 'log.php';
require_once 'auth.php';
require_once 'seo.php';
require_once 'project.php';
require_once 'loader/javascript.php';
require_once 'loader/css.php';

class ActionController
{

    protected $_request = null;
    
    protected $_url = null;
    protected $_root = null;
    
    /**
     *
     * @var Config
     */
    protected $_mainConfig = null;
    
    /**
     *
     * @var Config
     */
    protected $_appConfig = null;

    /**
     *
     * @var Config 
     */
    protected $_envConfig = null;
    
    /**
     *
     * @var View
     */
    protected $_view = null;

    /**
     *
     * @var MyPDO
     */
    protected $_db = null;

    /**
     *
     * @var bool
     */
    protected $_ajax = FALSE;

    /**
     *
     * @var Seo
     */
    protected $_seo;

    /**
     *
     * @var Javascript
     */
    protected $_javascript;

    /**
     *
     * @var Css
     */
    protected $_css;

    /**
     *
     * @var Project
     */
    protected $_project;

    /**
     *
     * @var Translate 
     */
    protected $_translate = null;

    /**
     *
     * @var Log
     */
    protected $_log = null;

    public function start()
    {
        
    }

//end start()

    public function shutdown()
    {
        

        $this->_view->Url = $this->_url;


        if ($this->_mainConfig->get('js_combined', 'optimization') == true) {
            $this->_view->jsComponents = $this->_javascript->getCombined();
        } else {
            if ($this->_mainConfig->get('js_min', 'optimization') == true) {
                $this->_view->jsComponents = $this->_javascript->getMinified();
            } else {
                $this->_view->jsComponents = $this->_javascript;
            }
        }

        if ($this->_mainConfig->get('css_combined', 'optimization') == true) {
            $this->_view->cssComponents = $this->_css->getCombined();
        } else {
            if ($this->_mainConfig->get('css_min', 'optimization') == true) {
                $this->_view->cssComponents = $this->_css->getMinified();
            } else {
                $this->_view->cssComponents = $this->_css;
            }
        }
    }

//end shutdown()

    public function __construct()
    {
               
        $this->_mainConfig = Registry::get('mainconfig');
        $this->_appConfig = Registry::get('appconfig');
        $this->_envConfig = Registry::get('envconfig');
        
        $this->_request = $_REQUEST;
        $this->_url = Registry::get('basehref');
        $this->_root = Registry::get('baseroot');
        $this->_db = Registry::get('db');
        $this->_log = Registry::get('log');
        $this->_css = new Css();
        $this->_javascript = new Javascript();
        $this->_translate = new TranslateMysql(ID_VERSION, $this->_db);
        $this->_seo = new Seo();
        $this->_project = new Project($this->_mainConfig->get('name', 'project'));
        $this->_view = new View($this->_translate);
        $this->_view->mainConfig = Registry::get('mainconfig');
        $this->_view->appConfig = Registry::get('appconfig');
        $this->_view->envConfig = Registry::get('envconfig');
        
        $this->_view->seo = $this->_seo;

        if (isset($this->_option["mobile.enable"])) {
            $mobile = new Mobile(Registry::get("base"), $_SERVER['HTTP_USER_AGENT'], "mobile", "mobile");
            $this->_version = $mobile->currentVersion();
            $this->_view->version = $this->_version;
            Registry::set("base", $mobile->baseHref());
        }
        //Fin Gestion version pc/mobile
        //Version mobile 
        //Caching des données
//        Header("Cache-Control: must-revalidate");
//        $offset = 60; //1 minute de caching
//        $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
//        Header($ExpStr);
    }

//end __construct()

    public function getView()
    {
        return $this->_view;
    }

//end getView()

    /**
     * Redirection
     * 
     * @param string $controller
     * @param string $action
     * @param array $params
     * @param <type> $teardown
     */
    public function redirect($controller, $action, $params = null, $teardown = true)
    {
        if (!$params)
            $params = array();

        $redirect = $controller . "/" . $action . ".html?" . http_build_query($teardown ? $params : array_merge($this->_request, $params));
        header("Location:" . $this->_root . $redirect);
        exit();
    }

    /**
     *
     * @param string $url
     * @param bool $relative 
     */
    public function simpleRedirect($url, $relative = false)
    {
        if ($relative)
            $url = Registry::get("basehref") . $url;
        
        header("Location: $url");
        
        exit();
    }

    /**
     *
     * @param array $inputs
     * @return bool 
     */
    public function issetAndNotEmpty($inputs)
    {
        foreach ($inputs as $input) {
            if (!isset($this->_request[$input]) || empty($this->_request[$input]))
                return false;
        }

        return true;
    }
    
    public function _($string) {
        return $this->_translate->_($string);
    }


}