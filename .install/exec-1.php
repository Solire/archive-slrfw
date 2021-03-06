<?php
/**
 * Template simple de script d'installation
 *
 * @package    Slrfw
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

$query = 'ALTER TABLE utilisateur ADD gabarit_niveau INT NOT NULL AFTER niveau ';
$db->exec($query);

$query = 'UPDATE utilisateur SET gabarit_niveau = 0;';
$db->exec($query);


