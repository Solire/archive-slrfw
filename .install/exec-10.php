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
$query = '
    INSERT INTO `gab_champ_param` (
      `code` ,
      `name` ,
      `default_value` ,
      `code_champ_type`
    )
    VALUES (
      "MINIATURE",
      "Miniature (200x100;*x50;50x*)",
      "",
      "FILE"
    );
';

$db->exec($query);

