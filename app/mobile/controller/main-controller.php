<?php

require "action-controller.php";
require_once "tools.php";

/**
 * Class example of MainController with always call
 * 
 * @category Application
 * @package  Controller
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 */
class MainController extends ActionController {

    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start() {


        // Set title of page !
        $this->_seo->setTitle($this->_project->getName());

        //Noindex Nofollow pour tout (À modifier en production)
        $this->_seo->disableIndex();
        $this->_seo->disableFollow();


        $this->_view->google_analytics = Registry::get("analytics");

        $this->_view->fil_ariane = null;

        $this->_gabaritManager = new gabaritManagerOptimized();

        /*
         * EXEMPLE DE RECUPERATION DES RUBRIQUE parent et leurs enfants
         */
        $this->_rubriques = $this->_gabaritManager->getList(ID_VERSION, ID_API, 0, false, TRUE);
        foreach ($this->_rubriques as $ii => $rubrique) {
            $pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $rubrique->getMeta('id'), FALSE, 1);
            $rubrique->setChildren($pages);

            foreach ($pages as $page) {
                $firstChild = $this->_gabaritManager->getFirstChild(ID_VERSION, $page->getMeta('id'));
                if ($firstChild)
                    $page->setFirstChild($firstChild);
            }
        }
        $this->_view->rubriques = $this->_rubriques;

        /*
         * EXEMPLE DE RECUPERATION DE LA LISTE DES VERSIONS
          $query = "
          SELECT *
          FROM `version`";
          $this->_view->versions = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
         */




        //Recupération des gabarits main
        $this->_view->mainPage = $this->_gabaritManager->getMain(ID_VERSION, ID_API);

        //On recupere la page elements communs qui sera disponible sur toutes les pages
        $this->_view->mainPage["element_commun"] = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_view->mainPage["element_commun"][0]->getMeta("id"), 0, FALSE, TRUE);


        //Caching des données
        header("Pragma: public");
        Header("Cache-Control: must-revalidate");
        $offset = 60; //1 minute de caching
        $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        Header($ExpStr);
    }

}