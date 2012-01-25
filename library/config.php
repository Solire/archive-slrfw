<?php

class Config
{
    private $_ini = null;

    public function __construct($iniFile) {
     $this->_ini = parse_ini_file($iniFile, true);
    }

    public function get($key, $section = null) {
        if ($section)
            return (isset($this->_ini[$section][$key]) ? $this->_ini[$section][$key] : null);
        else
            return (isset($this->_ini[$key]) ? $this->_ini[$key] : null);
    }
}