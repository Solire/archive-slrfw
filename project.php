<?php

namespace Slrfw\Library;

/** @todo faire la présentation du code */

/**
 * Manage Project
 */
class Project
{

    /**
     *
     * @var string  Project name
     */
    private $_name;


    /**
     * Get Project name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;

    }//end getName()


    public function __construct($name = "")
    {
        $this->_name = $name;

    }//end __construct()


    /**
     * Set Project name
     *
     * @param string $_name Project name
     *
     * @return void
     */
    protected function _setName($_name)
    {
        $this->_name = $_name;

    }//end _setName()


}//end class

?>