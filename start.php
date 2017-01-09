<?php
/**
 * Lancement du framework
 *
 * @package    Slrfw
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/* = lancement du script
  ------------------------------- */
try {
    FrontController::setApp('app');
    FrontController::init();
    FrontController::run();
} catch (Exception\Marvin $exc) {
    Error::report($exc);
} catch (Exception\User $exc) {
    Error::message($exc);
} catch (Exception\HttpError $exc) {
    $code = current($exc->getHttp());
    if (in_array($code, [
        401,
        403,
        404,
    ])) {
        switch ($code) {
            case 401:
                $header = 'HTTP/1.0 401 Unauthorized';
                break;

            case 403:
                $header = 'HTTP/1.0 403 Forbidden';
                break;

            default :
                $header = 'HTTP/1.0 404 Not Found';
                break;
        }

        header($header);
        FrontController::run('Error', 'error404');

        return;
    }

    Error::http($exc->getHttp());

} catch (\Exception $exc) {
    $marvin = new Marvin('debug', $exc);
    if ($debug) {
        $marvin->display();
    } else {
        $marvin->send();
    }
    Error::run();
}

