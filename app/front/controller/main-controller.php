<?php

require "action-controller.php";

/**
 * Class example of MainController with always call
 * 
 * @category Application
 * @package  Controller
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 */
class MainController extends ActionController
{

    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start()
    {
        // Set title of page !
        $this->_seo->setTitle($this->_project->getName());
        $this->_seo->disableIndex();
        $this->_seo->disableFollow();
        
        $this->_view->fil_ariane = null;

        // Add translate file PHP !
        $this->_translate->addTranslation('main');
        
        // Add Jquery library !
        $this->_css->addLibrary('style.css');

        // Add Jquery library !
        $this->_javascript->addLibrary('jquery/jquery-1.7.min.js');
        $this->_javascript->addLibrary('jquery/plugins/jquery.translate.js');
        $this->_javascript->addLibrary('main.js');
        
        
        $this->_gabaritManager = new gabaritManager();        
        
        $this->_rubriques = $this->_gabaritManager->getList(1, 0, 2, TRUE);
        
        foreach ($this->_rubriques as $ii => $rubrique) {
            $pages = $this->_gabaritManager->getList(1, $rubrique->getMeta('id'), FALSE, 1);
//            $this->_rubriques[$ii]->setChildren($pages);
            $rubrique->setChildren($pages);
            
            foreach ($pages as $page) {
                $firstChild = $this->_gabaritManager->getFirstChild(1, $page->getMeta('id'));
                if ($firstChild)
                    $page->setFirstChild ($firstChild);             
            }
            
            
            
        }

        $this->_view->rubriques = $this->_rubriques;
        // Add translate file JS !
//        $this->_translate->addTranslationJs('main');

        // Traduction of text !
//        echo $this->_view->_('Accueil');

        // Log mysql !
//        $this->_log->logThis('toto');
    }

}