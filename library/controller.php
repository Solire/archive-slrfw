<?php
/**
 * Base controller
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

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
    protected $_view = null;

    /**
     *
     * @var MyPDO
     */
    protected $_db = null;

    /**
     *
     * @var bool
     */
    protected $_ajax = false;

    /**
     *
     * @var Seo
     */
    protected $_seo;

    /**
     *
     * @var Javascript
     */
    protected $_javascript;

    /**
     *
     * @var Css
     */
    protected $_css;

    /**
     *
     * @var Project
     */
    protected $_project;

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
     * Fonction éxécutée avant l'execution de la fonction relative à la page en cours
     *
     * @return void
     */
    public function start()
    {

    }

    /**
     * Fonction éxécutée après l'execution de la fonction relative à la page en cours
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_view->Url = $this->_url;

        if ($this->_mainConfig->get('js_combined', 'optimization') == true) {
            $this->_view->jsComponents = $this->_javascript->getCombined();
        } else {
            if ($this->_mainConfig->get('js_min', 'optimization') == true) {
                $this->_view->jsComponents = $this->_javascript->getMinified();
            } else {
                $this->_view->jsComponents = $this->_javascript;
            }
        }

        if ($this->_mainConfig->get('css_combined', 'optimization') == true) {
            $this->_view->cssComponents = $this->_css->getCombined();
        } else {
            if ($this->_mainConfig->get('css_min', 'optimization') == true) {
                $this->_view->cssComponents = $this->_css->getMinified();
            } else {
                $this->_view->cssComponents = $this->_css;
            }
        }
    }

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
        $this->_css = new Loader\Css();
        $this->_javascript = new Loader\Javascript();
        $this->_translate = new TranslateMysql(ID_VERSION, ID_API, $this->_db);
        $this->_translate->addTranslation();
        $this->_seo = new Seo();
        $this->_project = new Project($this->_mainConfig->get('name', 'project'));
        $this->_view = new View($this->_translate);
        $this->_view->mainConfig = Registry::get('mainconfig');
        $this->_view->appConfig = Registry::get('appconfig');
        $this->_view->envConfig = Registry::get('envconfig');
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
     * Renvois la vue
     *
     * @return \Slrfw\Library\View
     */
    public function getView()
    {
        return $this->_view;
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
        $url = str_replace('/' . Registry::get('baseroot'), '', $_SERVER['REQUEST_URI']);
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
            if ($redirection301) {
                $keyChange = $key;
                $urlPartRedirect = $urlToTest;
                break;
            }
        }

        if ($redirection301) {
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
     * @uses Slrfw\Library\Exception\HttpError marque l'erreur HTTP
     */
    final public function redirectError($codeError = null, $url = null)
    {
        $exc = new \Slrfw\Library\Exception\HttpError('Erreur HTTP');
        if (!empty($codeError)) {
            $exc->http($codeError, $url);
        }

        throw $exc;
    }

    /**
     * Enregistrement des paramètres de rewriting
     *
     * @param array  $categories Rewriting contenu dans les "/"
     * @param string $target     Rewriting de l'action
     *
     * @return void
     *
     */
    final public function setRewriting(array $categories)
    {
        $this->rew = $categories;
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
     * Traduit si possible la chaine
     *
     * @param string $string Chaine à traduire
     *
     * @return string
     * @uses TranslateMysql
     */
    public function _($string)
    {
        return $this->_translate->_($string);
    }
}

