<?php

require_once 'main-controller.php';

class UserController extends MainController {

    public function start() {
        parent::start();
    }

    public function startAction() {
        $this->_javascript->addLibrary("back/formgabarit.js");
        
        $this->_view->breadCrumbs[] = array(
            "label" => "Mon profil",
            "url" => "",
        );
    }

    public function changePasswordAction() {
        $this->_view->enable(false);

        $errors = array();

        $response = array(
            "status" => false
        );

        //Nouveau mot de passe et sa confirmation différent
        if ($_POST["new_password"] != $_POST["new_password_c"]) {
            $errors[] = "Le nouveau mot de passe et sa confirmation sont différents";
        }


        //Test longueur password
        if (count($errors) == 0 && strlen($_POST["new_password"]) < 6) {
            $errors[] = "Votre nouveau mot de passe doit contenir au moins 6 caractères";
        }
        
        //Si aucune erreur on essaie de modifier le mot de passe
        if (count($errors) == 0) {
            $response["status"] = $this->_utilisateurManager->changePassword($this->_utilisateur, $this->_utilisateur->get("email"), $_POST["old_password"], $_POST["new_password"]);
            if(!$response["status"]) {
                $errors[] = "Mot de passe actuel incorrect";
            }
        }
        
        if($response["status"]) {
            $response["status"] = "success";
            $response["message"] = 'Votre mot de passe a été mis à jour';
            $response["javascript"] = 'window.location.reload()';
        } else {
            $response["message"] = implode("<br />", $errors);
        }

        echo json_encode($response);
    }

}