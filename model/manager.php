<?php

namespace Slrfw\Model;

use Slrfw\Registry;

/**
 * Description of objectmanager
 *
 * @author thomas
 */
class manager {
	/**
	 *
	 * @var Slrfw\MyPDO
	 */
	protected $_db;

	/**
	 *
	 * @param Slrfw\MyPDO $db
	 */
	public function __construct(Slrfw\MyPDO $db = null) {
		if ($db) {
            $this->_db = $db;
        } else {
            $this->_db = Registry::get("db");
        }
	}
}
