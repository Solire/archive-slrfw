<?php
// Start session !
session_name();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'On');

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . '../library/'
    . PATH_SEPARATOR . '../include/'
    . PATH_SEPARATOR . '../app/model/'
);

function __autoload($name) {
    require_once strtolower($name) . '.php';
}

// Include des fichiers system !
require_once 'front-controller.php';
require_once 'config.php';
require_once 'registry.php';
require_once 'db.php';
require_once 'log.php';
require_once 'cache.php';

// On recupere la config de l'appli !
$mainConfig = new Config('../config/main.ini');

// Detection local ou online !
$localHostnames = explode(',', $mainConfig->get('detect', 'development'));
$env = (in_array($_SERVER['SERVER_NAME'], $localHostnames) === true) ? 'local' : 'online';

// On recupere la config de l'appli selon l'environnement !
$envConfig = new Config("../config/$env.ini");

$application = isset($_REQUEST['application']) ? $_REQUEST['application'] : "front"; 
$baseHrefSuffix = isset($_REQUEST['application']) ? $_REQUEST['application'] . '/' : ''; 

$appConfig = null;

// Config de l'application
if(file_exists("../config/app_$application.ini"))
    $appConfig = new Config("../config/app_$application.ini");

// Connexion à la base de donnée !
$db = DB::factory($envConfig->get('database'));

$base     = $envConfig->get('base');
$baseHref = $base["url"];
$baseRoot = $base["root"];
$log      = Log::newLog($db);


//On configure le registre
Registry::set("mainconfig", $mainConfig);
Registry::set("appconfig", $appConfig);
Registry::set("envconfig", $envConfig);
Registry::set("db", $db);
Registry::set("base", $baseHref);
Registry::set("basehref", $baseHref . $baseHrefSuffix);
Registry::set("baseroot", $baseRoot);
Registry::set("log", $log);

Registry::set("site", $mainConfig->get("name", "project"));

$suf_version = isset($_GET['version']) ? $_GET['version'] : 'FR';
$query = "SELECT * FROM `version` WHERE `suf` LIKE " . $db->quote($suf_version);
$version = $db->query($query)->fetch(PDO::FETCH_ASSOC);

define("ID_VERSION", $version['id']);
define("SUF_VERSION", $version['suf']);

if (FrontController::run($mainConfig, $envConfig, $application) === false) {
    header('HTTP/1.0 404 Not Found');
}