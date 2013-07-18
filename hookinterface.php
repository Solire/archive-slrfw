<?php
/**
 * Interface pour les hooks
 *
 * @package    Slrfw
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Interface des classes de hook
 *
 * @package    Slrfw
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
interface HookInterface
{
    /**
    * Fonction exécutée lors du chargement du hook
    *
    * @param \Slrfw\Hook $env Objet contenant les variables d'environnement
    *
    * @return void
    */
    public function run($env);
}

