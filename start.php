<?php

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
    if (current($exc->getHttp()) == '404') {
        header('HTTP/1.0 404 Not Found');
        FrontController::run('front', 'error', 'error404');
    } else {
        exit('ok');
        $marvin = new Marvin('debug', $exc);
        $marvin->display();
    }
} catch (\Exception $exc) {
    $marvin = new Marvin('debug', $exc);
    $marvin->display();

//    Error::run();
}
