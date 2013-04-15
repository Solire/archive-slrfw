<?php

namespace Slrfw\Model;

use Slrfw\Registry;

/**
 * Description of objectmanager
 *
 * @author thomas
 */
class manager {

	const LOAD_BY_ID = 1;
	const LOAD_BY_REWRITING = 2;

	/**
	 *
	 * @var Slrfw\MyPDO
	 */
	protected $_db;

	/**
	 *
	 * @param Slrfw\MyPDO $db
	 */
	public function __construct($db = null) {
		if ($db)	$this->_db = $db;
		else		$this->_db = Registry::get("db");
	}
}