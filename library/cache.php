<?php


class Cache
{
	/**
	 *
	 * @var string cache directory
	 */
    private $_dir = null;

	/**
	 *
	 * @param array $ini
	 */
    public function __construct($ini) {
        $this->_dir = $ini["dir"];
    }

	/**
	 *
	 * @param string $key
	 * @param mixed $value
	 */
    public function set($key, $value) {
        file_put_contents($this->_dir . $key, serialize($value));
    }

	/**
	 *
	 * @param string $key
	 * @return mixed
	 */
    public function get($key) {
        $file = $this->_dir . $key;

        return (file_exists($file) && date("Ymd") <= date("Ymd", filemtime($file))) ? unserialize(file_get_contents($file)) : false;
    }
}