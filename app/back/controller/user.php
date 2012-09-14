<?php
/**
 * Gestion du profile utilisateur
 *
 * @package    Controller
 * @subpackage Back
 * @author     Dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\App\Back\Controller;

/**
 * Gestion du profile utilisateur
 *
 * @package    Controller
 * @subpackage Back
 * @author     Dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class User extends Main
{

    /**
     * Affichage du formulaire d'édition du profile
     *
     * @return void
     */
    public function startAction()
    {
        $this->_javascript->addLibrary('back/formgabarit.js');

        $this->_view->breadCrumbs[] = array(
            'label' => 'Mon profil',
            'url' => '',
        );
    }

    /**
     * Change le mot de passe de l'utilisateur
     *
     * @return void
     */
    public function changePasswordAction()
    {
        $this->_view->enable(false);

        $errors = array();

        $response = array(
            'status' => false
        );

        /** Nouveau mot de passe et sa confirmation différent */
        if ($_POST['new_password'] != $_POST['new_password_c']) {
            $errors[] = 'Le nouveau mot de passe et sa confirmation sont différents';
        }


         /** Test longueur password */
        if (count($errors) == 0 && strlen($_POST['new_password']) < 6) {
            $errors[] = 'Votre nouveau mot de passe doit contenir au moins 6 caractères';
        }

        //Si aucune erreur on essaie de modifier le mot de passe
        if (count($errors) == 0) {
            $response['status'] = $this->_utilisateurManager->changePassword(
                $this->_utilisateur, $this->_utilisateur->get('email'),
                $_POST['old_password'], $_POST['new_password']
            );
            if (!$response['status']) {
                $errors[] = 'Mot de passe actuel incorrect';
            }
        }

        if ($response['status']) {
            $response['status'] = 'success';
            $response['message'] = 'Votre mot de passe a été mis à jour';
            $response['javascript'] = 'window.location.reload()';
        } else {
            $response['message'] = implode('<br />', $errors);
        }

        echo json_encode($response);
    }
}

