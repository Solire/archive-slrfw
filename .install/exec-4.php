<?php
/**
 * Création des données en base relative au type de champs GMAP
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
    INSERT INTO `gab_champ_type` (`code`, `ordre`) VALUES ('GMAP', '12');
";
$db->exec($query);

$query = "
    INSERT INTO `gab_champ_typedonnee` (`code`, `ordre`) VALUES ('GMAP_POINT', '6');
";
$db->exec($query);

$query = "
    ALTER TABLE `gab_champ` CHANGE `typesql` `typesql` ENUM( 'varchar(255) NOT NULL', 'text NOT NULL', 'date NOT NULL', 'INT( 11 ) NOT NULL', 'TINYINT( 1 ) NOT NULL', 'FLOAT(10,7) NOT NULL', 'GMAP POINT' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
";
$db->exec($query);


