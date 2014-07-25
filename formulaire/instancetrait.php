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
trait InstanceTrait
{
    /**
     * Charge un formulaire
     *
     * Le formulaire est juste instancié à partir du fichier présent dans
     * <app>/config/form/
     *
     * @param string $name Nom du fichier de configuration du formulaire
     *
     * @return \Slrfw\Formulaire
     */
    protected function chargeForm($name)
    {
        $name = 'config/form/' . $name;
        $path = \Slrfw\FrontController::search($name, false);
        $form = new \Slrfw\Formulaire($path, true);

        return $form;
    }

    /**
     * Charge un fichier de config formulaire
     *
     * @param string $name Nom du fichier de configuration du formulaire
     *
     * @return \Slrfw\Config
     */
    protected function chargeFormConfig($name)
    {
        $name = 'config/form/' . $name;
        $path = \Slrfw\FrontController::search($name, false);
        $conf = new \Slrfw\Config($path);

        return $conf;
    }
}
