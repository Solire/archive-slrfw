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

        // Add translate file PHP !
        $this->_translate->addTranslation('main');

        // Add Jquery library !
        $this->_javascript->addLibrary('jquery/jquery-1.7.min');
        $this->_javascript->addLibrary('jquery/plugins/jquery.mobile-1.0rc2.min');
        $this->_css->addLibrary('jquery.mobile-1.0rc2.min');

        // Add translate file JS !
//        $this->_translate->addTranslationJs('main');

        // Traduction of text !
//        echo $this->_view->_('Accueil');

        // Log mysql !
        $this->_log->logThis('toto');
    }

}