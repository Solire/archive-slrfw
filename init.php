<?php

namespace Slrfw;

header('Content-Type: text/html; charset=utf-8');
define('DS', DIRECTORY_SEPARATOR);

/** Session PHP */
session_name();
session_start();

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath('.')
);
require_once 'slrfw/path.php';

/* = Autoload
  ------------------------------- */
/**
 * Chargement de classes dynamiquement
 * @package Library
 * @param string $name nom de la classe à charger
 */
function autoload($name)
{
    $name = str_replace('\\', DS, $name);
    $name = strtolower($name) . '.php';

    $path = new Path($name, Path::SILENT);
    $fullPath = $path->get();

    if ($fullPath) {
        require_once $path->get();
    }
}

spl_autoload_register('Slrfw\autoload');
