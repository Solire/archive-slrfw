<?php
class authManager extends manager {
	/**
	 *
	 * @var string 
	 */
	protected $_cookieName;
	
	/**
	 *
	 * @var string
	 */
	protected $_query;
	
	/**
	 *
	 * @var string
	 */
	protected $_colonneLogin;
	
	/**
	 *
	 * @var string
	 */
	protected $_colonnePassword;
	
	/**
	 *
	 * @param MyPDO $_db
	 * @param string $cookieName
	 * @param string $query requete sql avec ':id' à la place de l'identifiant.
	 */
	public function get($cookieName, $query, $colonneLogin = "email", $colonnePassword = "pass") {
		$this->_db = Registry::get("db");
		$this->_cookieName = $cookieName;
		$this->_colonneLogin = $colonneLogin;
		$this->_colonnePassword = $colonnePassword;
		$this->_query = $query;
		
		if (isset($_COOKIE[$this->_cookieName]) && $_COOKIE[$this->_cookieName] != '') {
			$Foo = explode("_", $_COOKIE[$this->_cookieName]);
			
			if (count($Foo) == 2) {
				$stmt = $this->_db->prepare($this->_query);// . $Foo[1];
				$stmt->bindValue(':id', $Foo[1], PDO::PARAM_INT);
				$stmt->execute();
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$Token = md5($user[$this->_colonneLogin] . date("Y-m-d") . $user[$this->_colonnePassword]);
				
				if ($Token == $Foo[0])
					return $user;
			}
		}
		
		return FALSE;
	}
		
	/**
	 *
	 * @param string $query
	 * @param string $login
	 * @param string $password
	 * @param bool $hash
	 * @return bool 
	 */
	public function connect($query, $login, $password, $hash = TRUE) {
		if (!is_string($login) || !is_string($password))
			return FALSE;

		$query = $this->_db->prepare($query);// . $Foo[1];
		$query->bindValue(':login', $login, PDO::PARAM_STR);
		$query->execute();
		$user = $query->fetch(PDO::FETCH_ASSOC);

		if ($hash) {
			if($user[$this->_colonnePassword] != sha1($password))
				return FALSE;
		}
		else {
			if($user[$this->_colonnePassword] != $password)
				return FALSE;
		}

		$Token = md5($user[$this->_colonneLogin] . date("Y-m-d") . $user[$this->_colonnePassword]);
		$Cookie = $Token . "_" . $user["id"];

		setcookie($this->_cookieName, $Cookie, time() + 60 * 60 * 24, '/');
		
		return $user;
	}
	
	/**
	 * 
	 */
	public function disconnect() {
		setcookie($this->_cookieName, "", time() - 60 * 60 * 24, '/');
	}
}
?>
