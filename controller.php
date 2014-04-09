<?php
/**
 * Base controller
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Base controller
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Controller
{

    protected $_request = null;

    /**
     * Url absolue du site
     * Elle sera enregistrée sous le nom $Url dans l'objet View
     * @var string
     * @uses View::$Url
     */
    protected $_url = null;
    protected $_root = null;

    /**
     *
     * @var Config
     */
    protected $_mainConfig = null;

    /**
     *
     * @var Config
     */
    protected $_appConfig = null;

    /**
     *
     * @var Config
     */
    protected $_envConfig = null;

    /**
     *
     * @var View
     */
    public $_view = null;

    /**
     *
     * @var MyPDO
     */
    public $_db = null;

    /**
     *
     * @var bool
     */
    protected $_ajax = false;

    /**
     *
     * @var Seo
     */
    public $_seo;

    /**
     *
     * @var Loader\Javascript
     */
    public $_javascript;

    /**
     *
     * @var Loader\Css
     */
    public $_css;

    /**
     *
     * @var TranslateMysql
     */
    protected $_translate = null;

    /**
     *
     * @var Log
     */
    protected $_log = null;

    /**
     * Informations de rewriting
     *
     * @var stdClass
     */
    protected $rew;

    /**
     * Accepte ou non les rewritings
     *
     * @var boolean
     */
    public $acceptRew = false;

    /**
     * Chargement du controller
     */
    public function __construct()
    {
        if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            $this->_ajax = true;
        }

        $this->_mainConfig = Registry::get('mainconfig');
        $this->_appConfig = Registry::get('appconfig');
        $this->_envConfig = Registry::get('envconfig');

        $this->_request = $_REQUEST;
        $this->_url = Registry::get('basehref');
        $this->_root = Registry::get('baseroot');
        $this->_db = Registry::get('db');
        $this->_log = Registry::get('log');
    }

    /**
     * Fonction éxécutée avant l'execution de la fonction relative à la page en cours
     *
     * @return void
     */
    public function start()
    {
        $this->_css = new Loader\Css();
        $this->_javascript = new Loader\Javascript();

        $this->_seo = new Seo();
        $this->_view->mainConfig = Registry::get('mainconfig');
        $this->_view->appConfig = Registry::get('appconfig');
        $this->_view->envConfig = Registry::get('envconfig');
        $this->_view->css = $this->_css;
        $this->_view->javascript = $this->_javascript;
        $this->_view->ajax = $this->_ajax;

        $this->_view->seo = $this->_seo;

        if (isset($this->_option['mobile.enable'])) {
            $mobile = new Mobile(
                Registry::get('base'), $_SERVER['HTTP_USER_AGENT'], 'mobile', 'mobile'
            );
            $this->_version = $mobile->currentVersion();
            $this->_view->version = $this->_version;
            Registry::set('base', $mobile->baseHref());
        }
    }

    /**
     * Fonction éxécutée après l'execution de la fonction relative à la page en cours
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_view->url = $this->_url;
    }

    /**
     * Lance les executions automatiques
     *
     * @param string $type Type d'execution (shutdown pour le moment)
     *
     * @return void
     * @throws Exception\lib Si le type n'est pas cohérent
     * @deprecated since version 3.0
     */
    final protected function loadExec($type)
    {
        if (!in_array($type, array('shutdown'))) {
            throw new Exception\lib('Type d\'execution incorrecte');
        }

        $dirs = FrontController::getAppDirs();
        $config = FrontController::$mainConfig;
        foreach ($dirs as $foo) {
            $dir = $foo['dir'] . DS . strtolower(FrontController::$appName)
                 . DS . $config->get('dirs', $type . 'Exec');
            $path = new Path($dir, Path::SILENT);
            if ($path->get() === false) {
                continue;
            }

            $dir = opendir($path->get());
            while ($file = readdir($dir)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                if (is_dir($path->get() . $file)) {
                    continue;
                }

                $funcName = $foo['name'] . '\\' . FrontController::$appName . '\\'
                          . str_replace(DS, '\\', $config->get('dirs', $type . 'Exec'))
                          . pathinfo($file, PATHINFO_FILENAME);
                if (!function_exists($funcName)) {
                    include $path->get() . $file;
                }
                    $funcName($this);
            }
            closedir($dir);
        }
    }

    /**
     * Définit la vue
     *
     * @param View $view
     *
     * @return void
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Renvois la vue
     *
     * @return View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Chargement de la classe de traduction
     *
     * @param TranslateMysql $translate
     *
     * @return void
     */
    public function setTranslate($translate)
    {
        $this->_translate = $translate;
    }

    /**
     * Définit l'objet de traduction
     *
     * @return TranslateMysql
     */
    public function getTranslate()
    {
        return $this->_translate;
    }


    /**
     * Redirection vers une autre action d'un controller
     *
     * @param string  $controller Nom du controller
     * @param string  $action     Nom de l'action
     * @param array   $params     Paramètres à faire passer en plus
     * @param boolean $teardown   Supprimer les anciens paramètres oui / non
     *
     * @return void
     *
     */
    public function redirect($controller, $action, $params = null, $teardown = true)
    {
        if (!$params) {
            $params = array();
        }

        if (!$teardown) {
            $params = array_merge($this->_request, $params);
        }

        $redirect = $controller . '/' . $action . '.html?'
                  . http_build_query($params);
        header('Location:' . $this->_root . $redirect);
        exit();
    }

    /**
     * Redirection vers une url
     *
     * @param string $url      Url vers laquelle renvoyer l'utilisateur
     * @param bool   $relative Url relative ?
     *
     * @return void
     */
    public function simpleRedirect($url, $relative = false)
    {
        if ($relative) {
            $url = Registry::get('basehref') . $url;
        }

        header('Location: ' . $url);

        exit();
    }

    /**
     * Detect les redirection 301 et renvoi l'url si une existe
     *
     * @return boolean|string
     */
    public function check301()
    {
        $url = preg_replace("`^/" . Registry::get('baseroot') . "`", "", $_SERVER['REQUEST_URI']);
        $urlParts = explode('/', $url);
        $urlsToTest[] = $url;

        $ajustLen = 0;
        if (substr($url, -1) == '/') {
            $ajustLen = 1;
            unset($urlParts[count($urlParts) - 1]);
        }

        $urlPartsReverse = array_reverse($urlParts);
        for ($index = 0; $index < count($urlPartsReverse) - 1; $index++) {
            $url = substr($url, 0, -strlen($urlPartsReverse[$index]) - $ajustLen);
            $urlsToTest[] = $url;
            $ajustLen = 1;
        }

        $urlPartRedirect = '';
        $redirection301 = false;
        foreach ($urlsToTest as $key => $urlToTest) {
            $query = 'SELECT new '
                    . 'FROM redirection '
                    . 'WHERE id_version = ' . ID_VERSION . ' '
                    . ' AND id_api = ' . ID_API . ' '
                    . ' AND old LIKE ' . $this->_db->quote($urlToTest) . ' '
                    . 'LIMIT 1';
            $redirection301 = $this->_db->query($query)->fetch(\PDO::FETCH_COLUMN);
            if ($redirection301 !== false) {
                $keyChange = $key;
                $urlPartRedirect = $urlToTest;
                break;
            }
        }

        if ($redirection301 !== false) {
            $samePart = array_slice($urlPartsReverse, 0, $keyChange);
            $redirection301 .= implode('/', $samePart);
            $redirection301 = $this->_url . $redirection301;
        }

        return $redirection301;
    }

    /**
     * Transforme la page en cour en une erreur 301 ou 404
     *
     * @return void
     * @uses ActionController::redirectError() 301 / 404
     */
    final public function pageNotFound()
    {

        $urlRedirect301 = $this->check301();

        if ($urlRedirect301) {
            $this->redirectError(301, $urlRedirect301);
        } else {
            $this->redirectError(404);
        }
    }

    /**
     * Transforme la page en une erreur HTTP
     *
     * @param string $codeError code erreur HTTP
     * @param string $url       Url vers laquelle rediriger l'utilisateur
     *
     * @return void
     * @uses Slrfw\Exception\HttpError marque l'erreur HTTP
     */
    final public function redirectError($codeError = null, $url = null)
    {
        $exc = new \Slrfw\Exception\HttpError('Erreur HTTP');
        if (!empty($codeError)) {
            $exc->http($codeError, $url);
        }

        throw $exc;
    }

    /**
     * La page est en ajax
     *
     * Désactive la vue et contrôle le fait que l'appel soit bien de l'ajax
     *
     * @return void
     */
    final protected function onlyAjax()
    {
        $this->_view->enable(false);
        if (!$this->_ajax) {
            $this->redirectError(405);
        }
    }

    /**
     * Enregistrement des paramètres de rewriting
     *
     * @param array $rew Rewriting contenu dans les "/"
     *
     * @return void
     *
     */
    final public function setRewriting(array $rew)
    {
        $this->rew = $rew;
    }

    /**
     * Renvois les informations de rewriting courante
     *
     * @return array
     */
    final public function getRewriting()
    {
        return $this->rew;
    }

    /**
     * Test si les valeurs du tableau sont dans les paramètres de la page
     *
     * @param array $inputs Liste des valeurs à contrôler
     *
     * @return bool
     * @deprecated
     */
    public function issetAndNotEmpty($inputs)
    {
        foreach ($inputs as $input) {
            if (!isset($this->_request[$input]) || empty($this->_request[$input])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Alias à l'utilisation de translate
     *
     * @param string $string Chaine à traduire
     * @param string $aide   Texte permettant de situer l'emplacement de la
     * chaine à traduire, exemple : 'Situé sur le bas de page'
     *
     * @return string
     * @uses TranslateMysql
     */
    public function _($string, $aide = '')
    {
        return $this->_translate->_($string, $aide);
    }
}

