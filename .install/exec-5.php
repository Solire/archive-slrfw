<?php
/**
 * Ajout du paramètre sur les jointures pour choisir une valeur par défaut
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
    INSERT INTO `gab_champ_param` (
        `code` ,
        `name` ,
        `default_value` ,
        `code_champ_type`
    )
    VALUES (
        'VALUE.DEFAULT', 'Valeur par défaut', '', 'JOIN'
    );
";
$db->exec($query);




