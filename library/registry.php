<?php

class Registry
{
    private static $_maps;

    private function __construct(){
    }

    public static function set($key, $value) {
        self::$_maps[$key] = $value;
    }

    public static function get($key) {
        return (isset(self::$_maps[$key]) ? self::$_maps[$key] : '');
    }
}