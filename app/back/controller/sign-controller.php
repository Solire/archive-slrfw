<?php
require_once 'main-controller.php';

class SignController extends MainController {
    public function start() {
        parent::start();
    }
    
    public function startAction() {
        $this->_javascript->addLibrary("back/form.js");
        
        $this->_view->main(false);
        
        $this->_view->action = $this->_appConfig->get("page-default", "general");
        
        if ($this->_utilisateur->isconnected())
            $this->simpleRedirect ($this->_appConfig->get("page-default", "general"), TRUE);
    }

    public function signoutAction() {
        $this->_view->enable(false);
        
        $this->_utilisateurManager->disconnect();
        $this->simpleRedirect ("sign/start.html", TRUE);
    }
}