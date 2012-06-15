<?php
/**
 * Description of client
 *
 * @author Dev
 */
class utilisateurManager extends authManager {
	public function get() {
		$query = "SELECT * FROM `utilisateur` WHERE `id` = :id";
		$data = parent::get("utilisateur", $query, "email", "pass");
        
        $utilisateur = new utilisateur ();
        $utilisateur->hydrateData($data);
        if ($data)
            $utilisateur->connect();
        
        return $utilisateur;
	}
	
	public function connect(&$utilisateur, $login, $password) {
		$query = "SELECT * FROM `utilisateur` WHERE `email` = :login";
		$data = parent::connect($query, $login, $password);
		if($data) {			
			$utilisateur->hydrateData($data);
			$utilisateur->connect();
			return TRUE;
		}
		
		return FALSE;
	}
//	public function connect($utilisateur, $login, $password) {
//		$query = "SELECT * FROM `utilisateur` WHERE `email` = :login";
//		return parent::connect($query, $login, $password);	
//    }
}

?>
