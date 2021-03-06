<?php
/**
 * Gestionnaire des fichiers de configurations
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
namespace Slrfw;

header('Content-Type: text/html; charset=utf-8');
define('DS', DIRECTORY_SEPARATOR);

/** Session PHP */
session_name();
session_start();

require 'slrfw/vendor/autoload.php';

$dir = pathinfo(__FILE__, PATHINFO_DIRNAME);
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath($dir)
);
unset($dir);
require_once 'slrfw/path.php';

/* = Autoload
  ------------------------------- */
/**
 * Chargement de classes dynamiquement
 *
 * @param string $name nom de la classe à charger
 *
 * @return void
 */
function autoload($name)
{
    $name = str_replace('\\', DS, $name);
    $name = strtolower($name) . '.php';

    $path = new Path($name, Path::SILENT);
    $fullPath = $path->get();

    if ($fullPath) {
        include_once $path->get();
    }
}

spl_autoload_register('Slrfw\autoload');

$debug = false;
