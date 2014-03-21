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
$query = 'ALTER TABLE `traduction` ADD `cle_sha` VARCHAR( 64 ) NOT NULL FIRST ;';

$db->exec($query);

/**
 * Calcule du sha256 de toutes les valeurs présentent dans la table
 * et  mise à jour en base de données
 */
$query = 'SELECT * FROM `traduction` ;';

$allTranslations = $db->query($query)->fetchAll();

$query = 'DELETE FROM `traduction` ;';

$db->exec($query);

// Ajout d'une clé primaire
$query = 'ALTER TABLE `traduction` ADD PRIMARY KEY ( `cle_sha` , `id_version` , `id_api` ) COMMENT "";';

$db->exec($query);

if(count($allTranslations) > 0) {
    
    foreach($allTranslations as $translation) {
        $stringSha = hash('sha256', $translation['cle']);
        $translation['cle_sha'] = $stringSha;
        
        $values = array_map(array($db, 'quote'), (array) $translation);
        $fieldNames = array_keys($values);
        $query  = 'INSERT IGNORE  INTO `traduction`'
                . ' (`' . implode('`,`', $fieldNames) . '`)'
                . ' VALUES(' . implode(',', $values) . ')';
        $db->exec($query);

    }
}


