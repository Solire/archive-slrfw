<?php

namespace Slrfw;

/** @todo faire la présentation du code */

class Pagination
{

    private $_currentPage = 1;
    private $_queryWithoutSelect;
    private $_queryCount;
    private $_queryGetField;
    private $_countResults;
    private $_results;
    private $_nbElemsByPage;
    private $_nbPages;
    private $_limit;
    private $_binds;

    /**
     * @var MyPDO
     */
    private $_db = null;

    /**
     *
     * @param MyPDO $myPdo Connection MyPDO
     * @param string $queryWithoutSelect Query without SELECT clause
     * @param string $queryGetField
     * @param int $nbElemsByPage number of elements by page
     * @param int $currentPage number of current page
     * @param array $binds binds for prepareQuery
     */
    public function __construct(MyPDO $myPdo, $queryWithoutSelect, $queryGetField, $nbElemsByPage, $currentPage, $binds = null, $queryCount = null)
    {
        $this->_db = $myPdo;
        $this->_queryWithoutSelect = $queryWithoutSelect;
        $this->_queryCount = $queryCount;
        $this->_queryGetField = $queryGetField;
        $this->_nbElemsByPage = intval($nbElemsByPage);
        $this->_binds = $binds;
        $this->_executeCountQuery();
        if ($this->_countResults == 0)
            return;
        $this->_calculNbPages();
        $currentPage = $this->setCurrentPage($currentPage);
        $this->_calculLimit();
        $this->_executeQuery();
//        $this->_debug();
    }

    public function getCurrentPage()
    {
        return $this->_currentPage;
    }

    public function getNbPage()
    {
        return $this->_nbPages;
    }

    public function setCurrentPage($currentPage)
    {

        $currentPage = intval($currentPage) == 0 ? 1 : intval($currentPage);
        if ($currentPage > $this->_nbPages) { // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
            $this->_currentPage = $this->_nbPages;
        }
        else
            $this->_currentPage = $currentPage;
    }

    private function _cleanHref($excludeParams = null)
    {
        $myGetVars = parse_url($_SERVER ['REQUEST_URI']);
        if (isset($myGetVars['query'])) {
            $myGetVarsArray = $this->_convertUrlQuery(urldecode(parse_url($_SERVER ['REQUEST_URI'], PHP_URL_QUERY)));
        } else {
            return $myGetVars["path"] . '?';
        }
        $offset = array_search('page', array_keys($myGetVarsArray));

        if ($offset !== false)
            array_splice($myGetVarsArray, $offset, 1);

        if ($excludeParams != null)
            foreach ($excludeParams as $excludeParam) {
                $offset = array_search($excludeParam, array_keys($myGetVarsArray));
                if ($offset !== false)
                    array_splice($myGetVarsArray, $offset, 1);
            }


        $params = http_build_query($myGetVarsArray);
        return $myGetVars["path"] . '?' . $params;
    }

    private function _convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            if (!isset($item[1]))
                continue;
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    public function getPaginationArray($patternPagination = null, $excludeParams = null)
    {
        $href = $this->_cleanHref($excludeParams);
        if ($patternPagination != null)
            $hrefRaw = preg_replace("#$patternPagination#", "", $href);
        else
            $hrefRaw = $href;
        $pageRequest = "page=";
        if (substr($href, -1) != "?")
            $pageRequest = "&" . $pageRequest;
        else {
            $hrefRaw = substr($hrefRaw, 0, -1);
        }

        $pages = array();
        for ($i = 1; $i <= $this->_nbPages; $i++) { //On fait notre boucle
            //On va faire notre condition
            if ($i == $this->_currentPage) { //Si il s'agit de la page actuelle...
                $pages[] = array("num" => $i, "href" => $href . $pageRequest . $i, "current" => true);
            } else { //Sinon...
                $pages[] = array("num" => $i, "href_raw" => $hrefRaw, "href" => $href . $pageRequest . $i, "current" => false);
            }
        }
        return $pages;
    }

    public function getResults()
    {
        return $this->_results;
    }

    private function _executeCountQuery()
    {

        if ($this->_queryCount == null)
            $query = "SELECT count(*) " . $this->_queryWithoutSelect;
        else
            $query = $this->_queryCount;
        $prepareQuery = $this->_db->prepare($query);
//        echo '<pre>', print_r($query, true), '</pre>';
        if ($this->_binds != null) {
            foreach ($this->_binds as $key => $value) {
                $prepareQuery->bindParam(":" . $key, $value[0], $value[1]);
            }
        }


        $prepareQuery->execute();
//        echo  $prepareQuery->getBuiltQuery();
        $this->_countResults = $prepareQuery->fetch(\PDO::FETCH_COLUMN);
    }

    private function _executeQuery()
    {
        $query = "SELECT "
                . $this->_queryGetField . " "
                . $this->_queryWithoutSelect . " "
                . $this->_getLimit();
        $prepareQuery = $this->_db->prepare($query);
//        echo '<pre>', print_r($query, true), '</pre>';
        if ($this->_binds != null) {
            foreach ($this->_binds as $key => $value) {
                $prepareQuery->bindParam(":" . $key, $value[0], $value[1]);
            }
        }
        $prepareQuery->execute();
//        echo  $prepareQuery->getBuiltQuery();
        $this->_results = $prepareQuery->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function _calculLimit()
    {
        $this->_limit[] = intval(($this->_currentPage - 1) * $this->_nbElemsByPage); // On calcul la première entrée à lire
        $this->_limit[] = $this->_nbElemsByPage; // On calcul la première entrée à lire
    }

    private function _getLimit()
    {
        return (is_null($this->_limit) || $this->_limit == "" || $this->_limit[1] == 0 ? "" : "LIMIT " . (is_array($this->_limit) ? implode($this->_limit, ",") : $this->_limit));
    }

    private function _calculNbPages()
    {
        if (intval($this->_nbElemsByPage) == 0)
            $this->_nbPages = 1;
        else
            $this->_nbPages = ceil($this->_countResults / $this->_nbElemsByPage);
    }

    private function _debug()
    {
        echo '<pre>', print_r($this, true), '</pre>';
        exit();
    }

    public function setBinds($binds)
    {
        $this->_binds = $binds;
    }

}