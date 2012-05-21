<?php

// Start session !
session_name();
session_start();


set_include_path(
        get_include_path()
        . PATH_SEPARATOR . '../library/'
        . PATH_SEPARATOR . '../include/'
        . PATH_SEPARATOR . '../app/model/'
        . PATH_SEPARATOR . realpath('../config/')
        . PATH_SEPARATOR . realpath('../')
);

function __autoload($name)
{
    require_once strtolower($name) . '.php';
}

// Include des fichiers system !
require_once 'front-controller.php';
require_once 'config.php';
require_once 'registry.php';
require_once 'exception.php';
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
if (file_exists("../config/app_$application.ini"))
    $appConfig = new Config("../config/app_$application.ini");

// Connexion à la base de donnée !
$db = DB::factory($envConfig->get('database'));

//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$base = $envConfig->get('base');
$baseHref = $base["url"];
$baseRoot = $base["root"];
$log = Log::newLog($db);


//On configure le registre
Registry::set("mainconfig", $mainConfig);
Registry::set("appconfig", $appConfig);
Registry::set("envconfig", $envConfig);

Registry::set("options", $mainConfig->get("options"));

Registry::set("db", $db);
Registry::set("base", $baseHref);
Registry::set("basehref", $baseHref . $baseHrefSuffix);
Registry::set("baseroot", $baseRoot);
Registry::set("email", $envConfig->get("email"));
Registry::set("log", $log);
Registry::set("site", $mainConfig->get("name", "project"));



if (isset($_GET["version-force"])) {
    $_SESSION["version-force"] = $_GET["version-force"];
}
if (isset($_SESSION["version-force"])) {
    $suf_version = $_SESSION["version-force"];
} else {
    $suf_version = isset($_GET['version']) ? $_GET['version'] : 'FR';
}

$serverUrl = str_replace('www.', '', $_SERVER['SERVER_NAME']);

if ($serverUrl != 'solire-01' && $serverUrl != "ks389489.kimsufi.com") {
    Registry::set("url", "http://www." . $serverUrl . '/');
    Registry::set("basehref", "http://www." . $serverUrl . '/');

}

if (isset($_GET["basehref-force"])) {
    $serverUrl = str_replace('www.', '', $_GET["basehref-force"]);
    Registry::set("url", "http://" . $_GET["basehref-force"] . '');
    Registry::set("basehref", "http://" . $_GET["basehref-force"] . '');
}

/* Si domaine en .tw, on affiche la vesion .hk
 * Permet d'avoir deux domaine pour la meme langue
 * Redirection invisible
 */
if($serverUrl == "objectif-france.tw") {
    $serverUrl = "objectif-france.hk";
}

$query = "SELECT * FROM `version` WHERE `domaine` = '$serverUrl'";
$version = $db->query($query)->fetch(PDO::FETCH_ASSOC);

if (!isset($version["id"])) {

        
    $query = "SELECT * FROM `version` WHERE `suf` LIKE " . $db->quote($suf_version);
    $version = $db->query($query)->fetch(PDO::FETCH_ASSOC);
}

Registry::set("analytics", $version['analytics']);

if($serverUrl == "objectif-france.tw") {
    Registry::set("analytics", "UA–30238091–3");
}

define("ID_VERSION", $version['id']);
define("SUF_VERSION", $version['suf']);

if (FrontController::run($mainConfig, $envConfig, $application) === false) {
    header('HTTP/1.0 404 Not Found');
}