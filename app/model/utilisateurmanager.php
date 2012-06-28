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
        
        public function changePassword(&$utilisateur, $login, $password, $newPassword) {
		$query = "SELECT * FROM `utilisateur` WHERE `email` = :login";
		$data = parent::connect($query, $login, $password);
                
		if($data) {			
                        $id = intval($data["id"]);
                        $pass = $this->_db->quote(sha1($newPassword));
                        $query = "UPDATE `utilisateur` SET `$this->_colonnePassword` = $pass WHERE `id` = $id";
                        $this->_db->exec($query);
                        $this->connect($utilisateur, $data["id"], $newPassword);
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
