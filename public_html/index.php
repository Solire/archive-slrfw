<?php
header('Content-Type: text/html; charset=utf-8');

/* = Session PHP
  ------------------------------- */
session_name();
session_start();

/* = Affichage des erreurs
  ------------------------------- */
error_reporting(E_ALL);
ini_set('display_errors', 'On');

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath('../library/')
    . PATH_SEPARATOR . realpath('../')
    . PATH_SEPARATOR . realpath('../library/shop/')
    . PATH_SEPARATOR . realpath('../config/')
    . PATH_SEPARATOR . realpath('../include/')
    . PATH_SEPARATOR . realpath('../app/model/')
);
require_once 'path.php';
require_once 'front-controller.php';

/* = Autoload
  ------------------------------- */
/**
 * Chargement de classes dynamiquement
 * @package Library
 * @param string $name nom de la classe à charger
 */
function autoload($name)
{
    $path = new Path(strtolower($name) . '.php');
    require_once $path->get();
}

spl_autoload_register('autoload');


/* = Gestion des erreurs HTTP
  ------------------------------- */
include_once 'exception.php';
include_once 'error.php';

/* = lancement du script
  ------------------------------- */
try {
    FrontController::init();
    FrontController::run();
} catch (MarvinException $exc) {
    Error::report($exc);
} catch (UserException $exc) {
    Error::message($exc);
} catch (HttpException $exc) {
    Error::http($exc->getHttp());
} catch (Exception $exc) {
    $marv = new Marvin('debug', $exc);
    $marv->display();
    
    Error::run();
}
