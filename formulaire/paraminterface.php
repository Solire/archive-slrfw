<?php
/**
 * Interface des plugins formulaire
 *
 * @package    Slrfw
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Slrfw\Formulaire;

/**
 * Interface des plugins formulaire
 *
 * @package    Slrfw
 * @subpackage Formulaire
 * @author     Siwaÿll <sanath.labs@gmail.com>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
interface ParamInterface
{
    /**
     * Traitement sur les données d'un formulaire
     *
     * @param array $data Données du formulaire
     *
     * @return void
     * @throws Exception Pour marquer une erreur dans le formulaire
     */
    public static function validate($data, $param);
}