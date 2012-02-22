<?php

require_once 'main-controller.php';

class PageController extends MainController
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

// end start()

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function startAction() {
        $this->_view->enable(FALSE);
                
        if (isset($_GET['rub'])) {

            if (isset ($_GET['prub'])) {
                $prubid = $this->_gabaritManager->getIdByRewriting(1, $_GET['prub']);
                if (!$prubid)
                    $this->pageNotFound ();
                                
                $this->_rubriqueParent = $this->_gabaritManager->getPage(ID_VERSION, $prubid);
                $this->_view->rubriqueParent = $this->_rubriqueParent;
                
            }
            else
                $prubid = 0;
            
            $this->_rubriqueId = $this->_gabaritManager->getIdByRewriting(1, $_GET['rub'], $prubid);
            
            if (!$this->_rubriqueId)
                $this->pageNotFound ();
            
            $this->_rubrique = $this->_gabaritManager->getPage(1, $this->_rubriqueId);
            $this->_view->rubrique = $this->_rubrique;
            
            if (isset ($_GET['rew'])) {
                $this->_pageId = $this->_gabaritManager->getIdByRewriting(1, $_GET['rew'], $this->_rubriqueId);
                
                if (!$this->_pageId)
                    $this->pageNotFound ();
                
                $this->_page = $this->_gabaritManager->getPage(ID_VERSION, $this->_pageId, 0, TRUE);
                
                if (isset($this->_rubriqueParent)) {
                    $firstchild = $this->_gabaritManager->getFirstChild(1, $this->_rubriqueParent->getMeta("id"));
                    if ($firstchild) {
                        $firstfirstchild = $this->_gabaritManager->getFirstChild(1, $firstchild->getMeta("id"));
                        
                        if ($firstfirstchild)
                            $this->_view->fil_ariane[] = array(
                                "label"    => $this->_rubriqueParent->getMeta("titre"),
                                "url"      => $this->_rubriqueParent->getMeta("rewriting") . "/"
                                            . $firstchild->getMeta("rewriting") . "/"
                                            . $firstfirstchild->getMeta("rewriting") . ".html",
                            );
                        else
                            $this->_view->fil_ariane[] = array(
                                "label"    => $this->_rubriqueParent->getMeta("titre"),
                                "url"      => $this->_rubriqueParent->getMeta("rewriting") . "/"
                                            . $firstchild->getMeta("rewriting") . ".html",
                            );
                    }
                    
                    $firstchild = $this->_gabaritManager->getFirstChild(1, $this->_rubrique->getMeta("id"));
                    $this->_view->fil_ariane[] = array(
                        "label"    => $this->_rubrique->getMeta("titre"),
                        "url"      => $this->_rubriqueParent->getMeta("rewriting") . "/"
                                    . $this->_rubrique->getMeta("rewriting") . "/"
                                    . $firstchild->getMeta("rewriting") . ".html",
                    );
                }
                else {
                    $firstchild = $this->_gabaritManager->getFirstChild(1, $this->_rubrique->getMeta("id"));
                    $this->_view->fil_ariane[] = array(
                        "label"    => $this->_rubrique->getMeta("titre"),
                        "url"      => $this->_rubrique->getMeta("rewriting") . "/"
                                    . $firstchild->getMeta("rewriting") . ".html",
                    );               
                }
                
                
                $this->_view->fil_ariane[] = array(
                    "label"    => $this->_page->getMeta("titre"),
                    "url"      => "",
                );
                
                $this->_seo->setTitle($this->_page->getMeta("bal_title"));
                $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
                $this->_seo->setKeywords(explode(" ", $this->_page->getMeta("bal_key")));
                $this->_view->page = $this->_page;
                $view = $this->_page->getGabarit()->getName();
            }
            else {        
                $this->_page = $this->_gabaritManager->getPage(ID_VERSION, $this->_rubriqueId, 0, TRUE);
                $this->_pages = $this->_gabaritManager->getList(1, $this->_rubriqueId, FALSE, TRUE, "ordre", "asc");
                
//                if (!$this->_pages)
//                    $this->pageNotFound ();
                
                $this->_view->fil_ariane[] = array(
                    "label"    => $this->_rubrique->getMeta("titre"),
                    "url"      => "",//$this->_rubrique->getMeta("rewriting") . '/',
                );
                
                foreach ($this->_pages as $page) {
                    $gabarit = $this->_gabaritManager->getGabarit($page->getMeta("id_gabarit"));
                    $page->setGabarit($gabarit);
                    $values = $this->_gabaritManager->getValues($page);
                    $page->setValues($values);                    
                }
                
                $this->_seo->disableIndex();
                $this->_view->page = $this->_page;
                $this->_view->pages = $this->_pages;
                $view = $this->_rubrique->getGabarit()->getName();
            }
        }
        // Page d'accueil
        else {
            $this->_pageId = 1;

            
            $this->_page = $this->_gabaritManager->getPage(ID_VERSION, $this->_pageId, 0, TRUE);
            
            $this->_seo->setTitle($this->_page->getMeta("bal_title"));
            $this->_seo->setDescription($this->_page->getMeta("bal_descr"));
            $this->_seo->setKeywords(explode(" ", $this->_page->getMeta("bal_key")));
            $this->_view->page = $this->_page;
            $view = $this->_page->getGabarit()->getName();
        }

        $this->shutdown();
        $this->_view->display("page", $view);
    } // end startAction

}

// end class
