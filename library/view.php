<?php

class View
{

    private $_enable = true;
    private $_main = true;
    private $_dir = null;
    private $_format = null;
    private $_translate = null;
    private $_controller;
    private $_action;

    /**
     * @var template
     */
    private $_Template = null;

    /**
     * @var String Nom du template
     */
    private $_TemplateName = null;

    public function __construct($translate)
    {
        $this->_translate = $translate;
    }

    public function _($string)
    {
        return $this->_translate->_($string);
    }

    public function enable($enable)
    {
        $this->_enable = $enable;
    }

    public function main($enable)
    {
        $this->_main = $enable;
    }

    public function isEnabled()
    {
        return $this->_enable;
    }

    public function isIncludeMain()
    {
        return $this->_main;
    }

    public function setDir($dir)
    {
        $this->_dir = $dir;
    }

    public function setFormat($format)
    {
        $this->_format = $format;
    }

    public function setTemplate($Name)
    {
        $this->_TemplateName = $Name;
    }

    public function content()
    {
        include(($this->_dir . $this->_controller . "/" . sprintf($this->_format, $this->_action)));
    }

    public function exists($controller, $action)
    {
        return file_exists($this->_dir . $controller . "/" . sprintf($this->_format, $action));
    }

    public function display($controller, $action, $custom = true)
    {
        $this->_controller = $controller;
        $this->_action = $action;

        if (($this->isEnabled() || $custom) && $this->isIncludeMain())
            include($this->_dir . "main.phtml");
        else
            $this->content();
    }

    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    public function add($File)
    {
        include $this->_dir . "/" . $File;
    }

    /**
     * Inclut un fichier phtml du dossier template.
     * @param <string> $File Nom du fichier template à inclure
     * @return void
     */
    public function template($File)
    {
        include($this->_dir . "template" . "/" . sprintf($this->_format, $File));
    }

}