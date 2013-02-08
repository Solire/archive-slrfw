<?php

namespace Slrfw\Library;

/** @todo faire la présentation du code */

class Auth {
	/**
	 *
	 * @var MyPDO
	 */
	private $_db;

	/**
	 *
	 * @var array
	 */
	private $_user;

	/**
	 *
	 * @var string
	 */
	private $_cookieName;

	/**
	 *
	 * @var bool
	 */
	private $_connected = FALSE;

	/**
	 *
	 * @param MyPDO $_db
	 * @param string $cookieName
	 * @param string $query requete sql avec ':id' à la place de l'identifiant.
	 */
	public function __construct($_db, $cookieName, $query) {
		$this->_db = $_db;
		$this->_cookieName = $cookieName;

		if (isset($_COOKIE[$this->_cookieName]) && $_COOKIE[$this->_cookieName] != '') {
			$Foo = explode("_", $_COOKIE[$this->_cookieName]);

			if (count($Foo) == 2) {
				$query = $this->_db->prepare($query);// . $Foo[1];
				$query->bindValue(':id', $Foo[1], \PDO::PARAM_INT);
				$query->execute();
				$user = $query->fetch(\PDO::FETCH_ASSOC);

				$Token = md5($user["email"] . date("Y-m-d") . $user["pass"]);

				if ($Token == $Foo[0]) {
					$this->_connected = TRUE;
					$this->_user = $user;
				}
			}
		}
	}

	/**
	 * Getter
	 * @param string $key
	 * @return mixed
	 */
	public function getUSer($key = null) {
		if ($key != null)
			return $this->_user[$key];
		return $this->_user;
	}

	/**
	 * Getter
	 * @return bool
	 */
	public function isConnected() {
		return $this->_connected;
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
		if ($this->_connected)
			return TRUE;

		if (!is_string($login) || !is_string($password))
			return FALSE;

		$query = $this->_db->prepare($query);// . $Foo[1];
		$query->bindValue(':login', $login, \PDO::PARAM_STR);
		$query->execute();
		$user = $query->fetch(\PDO::FETCH_ASSOC);

		if($hash){
			if($user["pass"] != sha1($password))
				return FALSE;
		}
		else{
			if($user["pass"] != $password)
				return FALSE;
		}

		$Token = md5($user["email"] . date("Y-m-d") . $user["pass"]);
		$Cookie = $Token . "_" . $user["id"];

		if (setcookie($this->_cookieName, $Cookie, time() + 60 * 60 * 24, '/')) {
			$this->_user = $user;
			$this->_connected = TRUE;
		}

		return $this->_connected;
	}

	/**
	 *
	 */
	public function disconnect() {
		$this->_connected = FALSE;
		setcookie($this->_cookieName, "", time() - 60 * 60 * 24, '/');
	}
}
?>
