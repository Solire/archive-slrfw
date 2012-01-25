<?php

/**
 * Manage SEO
 */
class Seo
{

    /**
     *
     * @var string  Marker title
     */
    private $_title;

    /**
     *
     * @var array  keywords of the page
     */
    private $_keywords = array();

    /**
     *
     * @var string  description of the page
     */
    private $_description = '';
    
    /**
     *
     * @var bool  indexation of the page
     */
    private $_index = true;
    
    /**
     *
     * @var bool  follow of the page
     */
    private $_follow = true;


    /**
     * Get Marker title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;

    }//end getTitle()


    /**
     * Set Marker title
     *
     * @param string $_title Marker title
     *
     * @return void
     */
    public function setTitle($_title)
    {
        $this->_title = $_title;

    }//end setTitle()


    /**
     * Get Prefix index
     *
     * @return string
     */
    public function getIndex()
    {
        if ($this->_index === true) {
            return '';
        } else {
            return 'no';
        }

    }//end getIndex()


    /**
     * Get Prefix follow
     *
     * @return string
     */
    public function getFollow()
    {
        if ($this->_follow === true) {
            return '';
        } else {
            return 'no';
        }

    }//end getFollow()


    /**
     * Enable indexation of the page
     *
     * @return void
     */
    public function enableIndex()
    {
        $this->_index = true;

    }//end enableIndex()


    /**
     * Enable follow of the page
     *
     * @return void
     */
    public function enableFollow()
    {
        $this->_follow = true;

    }//end enableFollow()


    /**
     * Disable indexation of the page
     *
     * @return void
     */
    public function disableIndex()
    {
        $this->_index = false;

    }//end disableIndex()


    /**
     * Disable follow of the page
     *
     * @return void
     */
    public function disableFollow()
    {
        $this->_follow = false;

    }//end disableFollow()


    /**
     * Get the array of keywords of the page
     * 
     * @return array
     */
    public function getKeywords()
    {
        return $this->_keywords;

    }//end getKeywords()


    /**
     * Set the array of keywords of the page
     *
     * @param array $_keywords array of keywords
     *
     * @return void
     */
    public function setKeywords($_keywords)
    {
        $this->_keywords = $_keywords;

    }//end setKeywords()


    /**
     * Get description of the page
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;

    }//end getDescription()


    /**
     * Set description of the page
     *
     * @param string $_description description of the page
     *
     * @return void
     */
    public function setDescription($_description)
    {
        $this->_description = $_description;

    }//end setDescription()


    /**
     * Add a keywords
     *
     * @param string $_keyword a keyword
     *
     * @return void
     */
    public function addKeyword($_keyword)
    {
        $this->_keywords[] = $_keyword;

    }//end addKeyword()


    /**
     * Get keywords in string
     *
     * @return string
     */
    public function showKeywords()
    {
        return implode(' ', $this->_keywords);

    }//end showKeywords()


}//end class

?>