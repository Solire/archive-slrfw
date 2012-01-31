<?php
require_once 'main-controller.php';

class SignController extends MainController {
    public function start() {
        parent::start();
    }
    
    public function startAction() {
        $this->_javascript->addLibrary("back/form.js");
        
        $this->_view->main(false);
        
        $this->_view->action = 'page/liste.html';
        
        if ($this->_utilisateur->isconnected())
            $this->simpleRedirect ("page/liste.html", TRUE);
    }

    public function signoutAction() {
        $this->_view->enable(false);
        
        $this->_utilisateurManager->disconnect();
        $this->simpleRedirect ("sign/start.html", TRUE);
    }
}