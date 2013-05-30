<?php
/**
 * Template simple de script d'installation
 *
 * @package    Slrfw
 * @subpackage Install
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

$query = "
    ALTER TABLE `gab_gabarit` ADD `view` BOOLEAN NOT NULL DEFAULT '1'
";
$db->exec($query);


