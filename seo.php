<?php

namespace Slrfw;

/**
 * Manage SEO.
 */
class Seo
{
    /**
     * @var string Marker title
     */
    private $title;

    /**
     * @var array keywords of the page
     */
    private $keywords = [];

    /**
     * @var string description of the page
     */
    private $description = '';

    /**
     * @var string url canonical of the page
     */
    private $urlCanonical = '';

    /**
     * @var string url pagination précédente
     */
    private $prev = null;

    /**
     * @var string url pagination suivante
     */
    private $next = null;

    /**
     * @var string Author of page
     */
    private $author;

    /**
     * @var string Authorname of page
     */
    private $authorName;

    /**
     * @var bool indexation of the page
     */
    private $index = true;

    /**
     * @var bool follow of the page
     */
    private $follow = true;

    /**
     * Get Marker title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set Marker title.
     *
     * @param string $_title Marker title
     *
     * @return void
     */
    public function setTitle($_title)
    {
        $this->title = $_title;
    }

    /**
     * Get Prefix index.
     *
     * @return string
     */
    public function getIndex()
    {
        if ($this->index === true) {
            return '';
        } else {
            return 'no';
        }
    }

    /**
     * Get Prefix follow.
     *
     * @return string
     */
    public function getFollow()
    {
        if ($this->follow === true) {
            return '';
        } else {
            return 'no';
        }
    }

    /**
     * Enable indexation of the page.
     *
     * @return void
     */
    public function enableIndex()
    {
        $this->index = true;
    }

    /**
     * Enable follow of the page.
     *
     * @return void
     */
    public function enableFollow()
    {
        $this->follow = true;
    }

    /**
     * Disable indexation of the page.
     *
     * @return void
     */
    public function disableIndex()
    {
        $this->index = false;
    }

    /**
     * Disable follow of the page.
     *
     * @return void
     */
    public function disableFollow()
    {
        $this->follow = false;
    }

    /**
     * Get the array of keywords of the page.
     *
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set the array of keywords of the page.
     *
     * @param array $_keywords array of keywords
     *
     * @return void
     */
    public function setKeywords($_keywords)
    {
        $this->keywords = $_keywords;
    }

    /**
     * Get description of the page.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get url canonical of the page.
     *
     * @return string
     */
    public function getUrlCanonical()
    {
        return $this->urlCanonical;
    }

    /**
     * Get url previous page.
     *
     * @return string
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Get url next page.
     *
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Get author of the page.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get authorName of the page.
     *
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Set description of the page.
     *
     * @param string $_description description of the page
     *
     * @return void
     */
    public function setDescription($_description)
    {
        $this->description = $_description;
    }

    /**
     * Set url canonical of the page.
     *
     * @param string $_urlCanonical url canonical of the page
     *
     * @return void
     */
    public function setUrlCanonical($_urlCanonical)
    {
        $this->urlCanonical = $_urlCanonical;
    }

    /**
     * Get url previous page.
     *
     * @return string
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;
    }

    /**
     * Get url next page.
     *
     * @return string
     */
    public function setNext($next)
    {
        $this->next = $next;
    }

    /**
     * Set author of the page.
     *
     * @param string $_author author of the page
     *
     * @return void
     */
    public function setAuthor($_author)
    {
        $this->author = $_author;
    }

    /**
     * Set authorName of the page.
     *
     * @param string $_authorName authorName of the page
     *
     * @return void
     */
    public function setAuthorName($_authorName)
    {
        $this->authorName = $_authorName;
    }

    /**
     * Add a keywords.
     *
     * @param string $_keyword a keyword
     *
     * @return void
     */
    public function addKeyword($_keyword)
    {
        $this->keywords[] = $_keyword;
    }

    /**
     * Get keywords in string.
     *
     * @return string
     */
    public function showKeywords()
    {
        return implode(', ', $this->keywords);
    }
}
