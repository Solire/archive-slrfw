<?php
class managers
{
	/**
	 *
	 * @var MyPDO
	 */
	protected $_db = null;
	
	/**
	 *
	 * @var array
	 */
	protected $_managers = array();

	/**
	 *
	 * @param MyPDO $db 
	 */
	public function __construct($db = null) {
		$this->_db = $db;
	}

	/**
	 *
	 * @param string $nom
	 * @return manager
	 */
	public function getManagerOf($nom) {
		if (!is_string($nom) || empty($nom)) {
			throw new InvalidArgumentException('Le module spécifié est invalide');
		}

		if (!isset($this->_managers[$nom])) {
			$manager = $nom . 'Manager';
			$this->_managers[$nom] = new $manager($this->_db);
		}

		return $this->_managers[$nom];
	}
}