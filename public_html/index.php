<?php

namespace Slrfw;

header('Content-Type: text/html; charset=utf-8');

/* = Session PHP
  ------------------------------- */
session_name();
session_start();

/* = Affichage des erreurs
  ------------------------------- */
error_reporting(E_ALL);
//ini_set('display_errors', 'On');

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath('../')
    . PATH_SEPARATOR . realpath('../config/')
    . PATH_SEPARATOR . realpath('../model/')
);
require_once 'library/path.php';
require_once 'library/front-controller.php';

/* = Autoload
  ------------------------------- */
/**
 * Chargement de classes dynamiquement
 * @package Library
 * @param string $name nom de la classe à charger
 */
function autoload($name)
{
    if (strpos($name, 'Slrfw') !== false) {
        $name = str_replace('Slrfw\\', '', $name);
        $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        $name = strtolower($name) . '.php';
    }

    $path = new Library\Path($name);
    require_once $path->get();
}

spl_autoload_register('Slrfw\autoload');


/* = Gestion des erreurs HTTP
  ------------------------------- */
//include_once 'exception.php';
//include_once 'error.php';

/* = lancement du script
  ------------------------------- */
try {
    Library\FrontController::init();
    Library\FrontController::run();
} catch (Library\Exception\Marvin $exc) {
    Library\Error::report($exc);
} catch (Library\Exception\User $exc) {
    Library\Error::message($exc);
} catch (Library\Exception\HttpError $exc) {
    Library\Error::http($exc->getHttp());
} catch (\Exception $exc) {
    $marv = new Library\Marvin('debug', $exc);
    $marv->display();

    Library\Error::run();
}
