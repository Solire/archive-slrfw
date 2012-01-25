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

        // Add translate file PHP !
        $this->_translate->addTranslation('main');

        // Add Jquery library !
        $this->_javascript->addLibrary('jquery/jquery-1.7.min.js');
        $this->_javascript->addLibrary('jquery/plugins/jquery.translate.js');
        

        // Add translate file JS !
//        $this->_translate->addTranslationJs('main');

        // Traduction of text !
        echo $this->_view->_('Accueil');

        // Log mysql !
        $this->_log->logThis('toto');
    }

}