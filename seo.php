<?php

namespace Slrfw;

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
     * @var string  url canonical of the page
     */
    private $_urlCanonical = '';
    
    /**
     *
     * @var string  Author of page
     */
    private $_author;
    
    /**
     *
     * @var string  Authorname of page
     */
    private $_authorName;

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
     * Get url canonical of the page
     *
     * @return string
     */
    public function getUrlCanonical()
    {
        return $this->_urlCanonical;

    }//end getDescription()
    
    
    /**
     * Get author of the page
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->_author;

    }//end getAuthor()
    
    /**
     * Get authorName of the page
     *
     * @return string
     */
    public function getAuthorName()
    {
        return $this->_authorName;

    }


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
     * Set url canonical of the page
     *
     * @param string $_urlCanonical url canonical of the page
     *
     * @return void
     */
    public function setUrlCanonical($_urlCanonical)
    {
        $this->_urlCanonical = $_urlCanonical;

    }//end setUrlCanonical()
    
    
    /**
     * Set author of the page
     *
     * @param string $_author author of the page
     *
     * @return void
     */
    public function setAuthor($_author)
    {
        $this->_author = $_author;

    }//end setAuthor()
    
    /**
     * Set authorName of the page
     *
     * @param string $_authorName authorName of the page
     *
     * @return void
     */
    public function setAuthorName($_authorName)
    {
        $this->_authorName = $_authorName;

    }


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

