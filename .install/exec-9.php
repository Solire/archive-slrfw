<?php
/**
 * Ajout d'une clé sha256 dans al table traduction
 *
 * @package    Slrfw
 * @subpackage Install
 * @author     Shinbuntu <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');


// Ajout de la colonne cle_sha dans la table traduction
$query = 'UPDATE utilisateur SET pass = "$2y$10$VfOIubFk/IjdqxPwL60xtOrxXwlOEQqSn5Ml0Jc5z0cSkenCgH8/e";';
$db->exec($query);

// Suppression de la colonne certificat (inutilisée)
$query = 'ALTER TABLE utilisateur DROP COLUMN certificat';
$db->exec($query);
