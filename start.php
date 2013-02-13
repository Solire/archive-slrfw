<?php

namespace Slrfw;

/* = lancement du script
  ------------------------------- */
try {
    Library\FrontController::setApp('app');
    Library\FrontController::init();
    Library\FrontController::run();
} catch (Library\Exception\Marvin $exc) {
    Library\Error::report($exc);
} catch (Library\Exception\User $exc) {
    Library\Error::message($exc);
} catch (Library\Exception\HttpError $exc) {
    if (current($exc->getHttp()) == '404') {
        header('HTTP/1.0 404 Not Found');
        Library\FrontController::run('front', 'error', 'error404');
    }
} catch (\Exception $exc) {
    Library\Error::run();
}
