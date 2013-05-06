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

$query = "
    INSERT INTO `gab_champ_param` (
    `code` ,
    `name` ,
    `default_value` ,
    `code_champ_type`
    )
    VALUES (
    'TYPE.GAB_PAGE', 'Jointure avec gab_page', '1', 'JOIN'
    );
";
$db->exec($query);


