<?php
/**
 * Interface pour les hooks
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
interface HookInterface
{
    /**
    * Chargement des filtres
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env);
}