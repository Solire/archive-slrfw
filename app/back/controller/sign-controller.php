<?php
require_once 'main-controller.php';

class SignController extends MainController {
    public function start() {
        parent::start();
    }
    
    public function startAction() {
        $this->_view->main(false);
        
        $this->_view->action = 'page/listeprojets.html';
        
        if ($this->_utilisateur->isconnected())
            $this->simpleRedirect ("page/listeprojets.html", TRUE);
    }

    public function signoutAction() {
        $this->_view->enable(false);
        
        $this->_managers->getManagerOf("utilisateur")->disconnect();
        $this->simpleRedirect ("sign/start.html", TRUE);
    }
}