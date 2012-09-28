<?php

namespace Slrfw\App\Front\Controller;

use Slrfw\Library\Registry;


class Page extends Main
{

    private $_cache = null;

    /**
     *
     * @var page
     */
    private $_page = null;

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
        $this->_cache = Registry::get('cache');
    }

   
    
    protected function __display() {
        if (isset($_GET['rub'])) {

            if (isset($_GET['prub'])) {
                $prubid = $this->_gabaritManager->getIdByRewriting(ID_VERSION, ID_API, $_GET['prub']);
                
                if (!$prubid)
                    $this->pageNotFound();
                
                $this->_rubriqueParent = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $prubid);
                $this->_view->rubriqueParent = $this->_rubriqueParent;

                $this->_view->breadCrumbs[] = array(
                    "label" => $this->_rubriqueParent->getMeta("titre"),
                    "url" => $this->_rubriqueParent->getMeta("rewriting") . ".html",
                );
            }
            else {
                $prubid = 0;
            }
            
            $this->_rubriqueId = $this->_gabaritManager->getIdByRewriting(ID_VERSION, ID_API, $_GET['rub'], $prubid);

            if (!$this->_rubriqueId)
                $this->pageNotFound();

            $this->_rubrique = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_rubriqueId);
            $this->_view->rubrique = $this->_rubrique;

            if (isset($_GET['rew'])) {
                $this->_pageId = $this->_gabaritManager->getIdByRewriting(ID_VERSION, ID_API, $_GET['rew'], $this->_rubriqueId);

                if (!$this->_pageId)
                    $this->pageNotFound();

                $this->_page = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_pageId, 0, TRUE);

                if (isset($this->_rubriqueParent)) {
                    $firstchild = $this->_gabaritManager->getFirstChild(ID_VERSION, $this->_rubriqueParent->getMeta("id"));
                    if ($firstchild) {
                        $firstfirstchild = $this->_gabaritManager->getFirstChild(ID_VERSION, $firstchild->getMeta("id"));

                        if ($firstfirstchild)
                            $this->_view->breadCrumbs[] = array(
                                "label" => $this->_rubriqueParent->getMeta("titre"),
                                "url" => $this->_rubriqueParent->getMeta("rewriting") . "/"
                                . $firstchild->getMeta("rewriting") . "/"
                                . $firstfirstchild->getMeta("rewriting") . ".html",
                            );
                        else
                            $this->_view->breadCrumbs[] = array(
                                "label" => $this->_rubriqueParent->getMeta("titre"),
                                "url" => $this->_rubriqueParent->getMeta("rewriting") . "/"
                                . $firstchild->getMeta("rewriting") . ".html",
                            );
                    }

                    $firstchild = $this->_gabaritManager->getFirstChild(ID_VERSION, $this->_rubrique->getMeta("id"));
                    $this->_view->breadCrumbs[] = array(
                        "label" => $this->_rubrique->getMeta("titre"),
                        "url" => $this->_rubriqueParent->getMeta("rewriting") . "/"
                        . $this->_rubrique->getMeta("rewriting") . "/"
                        . $firstchild->getMeta("rewriting") . ".html",
                    );
                }
                else {
                    $firstchild = $this->_gabaritManager->getFirstChild(ID_VERSION, $this->_rubrique->getMeta("id"));
                    $this->_view->breadCrumbs[] = array(
                        "label" => $this->_rubrique->getMeta("titre"),
                        "url" => $this->_rubrique->getMeta("rewriting") . "/"
                        . $firstchild->getMeta("rewriting") . ".html",
                    );
                }


                $this->_view->breadCrumbs[] = array(
                    "label" => $this->_page->getMeta("titre"),
                    "url" => "",
                );

                $this->_seo->setTitle($this->_page->getMeta("bal_title"));
                $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
                $this->_seo->setKeywords(explode(" ", $this->_page->getMeta("bal_key")));
                $this->_view->page = $this->_page;
                $view = $this->_page->getGabarit()->getName();
            } else {
                $this->_page = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_rubriqueId, 0, false);

                if ($this->_rubrique->getGabarit()->getName() != "sous_rub_scenario") {
                    $this->_pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $this->_rubriqueId, FALSE, TRUE, "ordre", "asc");
                    foreach ($this->_pages as &$page) {
                        $gabarit = $this->_gabaritManager->getGabarit($page->getMeta("id_gabarit"));
                        $page->setGabarit($gabarit);
                        $values = $this->_gabaritManager->getValues($page);
                        $page->setValues($values);
                        $blocs = $this->_gabaritManager->getBlocs($gabarit, $page->getMeta("id"));
                        foreach ($blocs as $blocName => $bloc) {
                            $valuesBloc = $this->_gabaritManager->getBlocValues($bloc, $page->getMeta("id"), ID_VERSION);
                            if ($valuesBloc) {
                                $bloc->setValues($valuesBloc);
                            }
                        }
                        $page->setBlocs($blocs);
                    }
                    $this->_view->pages = $this->_pages;
                }







                $this->_view->breadCrumbs[] = array(
                    "label" => $this->_rubrique->getMeta("titre"),
                    "url" => "",
                );



//                $this->_seo->disableIndex();
                $this->_view->page = $this->_page;

                $view = $this->_rubrique->getGabarit()->getName();
            }
        }
        // Page d'accueil
        else {
            $this->_pageId = 1;


            $this->_page = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_pageId, 0, true, true);

            $this->_seo->setTitle($this->_page->getMeta("bal_title"));
            $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
            $this->_seo->setKeywords(explode(" ", $this->_page->getMeta("bal_key")));


            $this->_view->page = $this->_page;

            
        }



        //Balise META
        $this->_seo->setTitle($this->_page->getMeta("bal_title"));
        $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
        $this->_seo->addKeyword($this->_page->getMeta("bal_key"));
        $this->_seo->setUrlCanonical($this->_page->getMeta("canonical"));
        if ($this->_page->getMeta("no_index"))
            $this->_seo->disableIndex();

        
    }
    
    
    
    public function startAction()
    {
        $this->_view->enable(false);

        /** En cas de prévisualisation. */
        if ($this->_utilisateurAdmin->isConnected() && isset($_POST['id_gabarit'])) {
            $this->_previsu();
        }
        else {
            $this->_display();
        }

        if (isset($this->_parents[1])) {
            $firstChild = $this->_gabaritManager->getFirstChild(
                ID_VERSION, $this->_parents[1]->getMeta('id')
            );
            $this->_parents[1]->setFirstChild($firstChild);
        }
        
        $this->_siblings = $this->_gabaritManager->getList(
            ID_VERSION, $this->_page->getMeta("id_parent"), 0, true
        );

        //Balise META
        $this->_seo->setTitle($this->_page->getMeta("bal_title"));
        $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
        $this->_seo->addKeyword($this->_page->getMeta("bal_key"));
        $this->_seo->setUrlCanonical($this->_page->getMeta("canonical"));
        if ($this->_page->getMeta("no_index"))
            $this->_seo->disableIndex();
                
        $this->_view->page      = $this->_page;
        $this->_view->parents   = $this->_parents;
        $this->_view->pages     = $this->_pages;
        $this->_view->siblings  = $this->_siblings;
        
        $view = $this->_page->getGabarit()->getName();
        if (method_exists($this, "_" . $view . "Gabarit"))
            $this->{"_" . $view . "Gabarit"}();

        $this->shutdown();
        $this->_view->display("page", $view);
    }

    
    private function _previsu()
    {
        $first = TRUE;
        $this->_pages = array();

        $this->_page = $this->_gabaritManager->previsu($_POST);

        if (count($this->_pages) == 0) {
            $this->_pages = $this->_gabaritManager->getList(
                $_POST['id_version'], $_POST['id_api'],
                $this->_page->getMeta("id"), false, true, "ordre", "asc"
            );            
        }


        $this->_parents = array_reverse($this->_page->getParents());
        $fullrewriting = "";
        foreach ($this->_parents as $ii => $parent) {
            $this->_parents[$ii] = $this->_gabaritManager->getPage(
                $_POST['id_version'], $_POST['id_api'], $parent->getMeta("id"),
                0, false, false
            );
            
            $this->_fullRewriting[] = $parent->getMeta("rewriting") . "/";
            
            $this->_view->breadCrumbs[] = array(
                "label" => $parent->getMeta("titre"),
                "url"   => implode("/", $this->_fullRewriting) . "/",
            );
            
        }
    }
    
    private function _display()
    {
        if (!isset($_GET['rew']) || !is_array($_GET['rew'])) {
            $this->pageNotFound();
        }

        $this->_parents         = array();
        $this->_fullRewriting   = array();

        $id_parent = 0 ;

        foreach ($_GET['rew'] as $ii => $rewriting) {
            if (!$rewriting) {
                $this->pageNotFound();
            }

            $last = ($ii == count($_GET['rew']) - 1);

            $id_gab_page    = $this->_gabaritManager->getIdByRewriting(
                ID_VERSION, $rewriting, $id_parent
            );
            if (!$id_gab_page) {
                $this->pageNotFound();
            }

            $page           = $this->_gabaritManager->getPage(
                ID_VERSION, $id_gab_page, 0, $last, true
            );
            if (!$page) {
                $this->pageNotFound();
            }

            $this->_fullRewriting[]     = $rewriting;

            $this->_view->breadCrumbs[]  = array(
                "label"    => $page->getMeta("titre"),
                "url"      => implode("/", $this->_fullRewriting) . "/",
            );

            if ($last) {
                $this->_page        = $page;
            } else {
                $this->_parents[]   = $page;
            }

            $id_parent      = $id_gab_page;
        }
        
        $this->_pages = $this->_gabaritManager->getList(
            ID_VERSION, $this->_page->getMeta("id"), false, true, "ordre", "asc"
        );

        if ($this->_page->getGabarit()->getName() == "produits_page"
            || $this->_page->getGabarit()->getName() == "produits_sous_sous_rub"
        ) {
            foreach ($this->_pages as $ii => $page) {
                $this->_pages[$ii] = $this->_gabaritManager->getPage(
                    ID_VERSION, $page->getMeta("id"), 0, true, true
                );
            }
        }
    }



}

// end class
