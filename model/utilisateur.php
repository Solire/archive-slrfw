<?php

namespace Slrfw\Model;

/**
 * Description of client
 *
 * @author thomas
 */
class utilisateur {
	private $_connected = FALSE;
	private $_data = NULL;

	public function __construct() {}

	public function hydrateData($data) {
		$this->_data = $data;
	}

	public function connect() {
		$this->_connected = TRUE;
	}

	public function disconnect() {
		$this->_connected = FALSE;
	}

	public function get($key) {
        if (is_array($this->_data) && array_key_exists($key, $this->_data))
			return $this->_data[$key];

		return NULL;
	}

	public function isConnected() {
		return $this->_connected;
	}
}

?>
