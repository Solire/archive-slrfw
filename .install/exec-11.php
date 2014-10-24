<?php
/**
 * Ajout d'un paramètre pour les champs de type fichier
 * afin de paramétrer des créations de miniatures pour
 * les images uploadées
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


// Ajout du paramètre en base de données
$query = 'CREATE TABLE IF NOT EXISTS `domaine` ( '
       . '`id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT, '
       . '`id_version` tinyint(3) unsigned NOT NULL, '
       . '`id_api` tinyint(3) unsigned NOT NULL, '
       . '`hote` varchar(255) COLLATE utf8_unicode_ci NOT NULL, '
       . 'PRIMARY KEY (`id`) '
       . ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1';
$db->exec($query);

$query = 'INSERT INTO domaine (id_version, id_api, hote) '
       . 'SELECT id, id_api, domaine FROM version WHERE domaine != ""';
$db->exec($query);

$query = 'ALTER TABLE version DROP COLUMN domaine';
$db->exec($query);
