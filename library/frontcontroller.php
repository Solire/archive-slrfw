<?php
/**
 * Front controller
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

/**
 * Front controller
 *
 * @package    Library
 * @subpackage Core
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class FrontController
{
    /**
     * Configuration principale du site
     *
     * @var Config
     */
    public static $mainConfig;

    /**
     * Configuration de l'environnement utilisé
     *
     * @var Config
     */
    public static $envConfig;

    /**
     * Liste des répertoires app à utiliser
     *
     * @var array
     */
    protected static $appDirs = array();

    /**
     * Nom du controller utilisé
     *
     * @var string
     */
    public $controller = '';

    /**
     * Nom de l'application utilisée
     *
     * @var string
     */
    public $application = '';

    /**
     * Dossier de app utilisé.
     *
     * @var string
     */
    public $app = '';

    /**
     * Nom de l'action utilisée
     *
     * @var string
     */
    public $action = '';

    /**
     * Tableau des éléments de rewriting présents dans l'url
     *
     * @var array
     */
    protected $rewriting = array();

    /**
     *
     *
     * @var string
     */
    public $target = '';

    private static $_singleton = null;

    /**
     * Indicateur pour ne faire qu'une fois la configuration d'api
     *
     * @var boolean
     */
    private static $singleApi = false;

    private $_dirs = null;
    private $_format = null;
    private $_debug = null;

    const CONTROLLER_FILE_NOT_EXISTS = 0;
    const CONTROLLER_CLASS_NOT_EXISTS = 1;
    const CONTROLLER_ACTION_NOT_EXISTS = 2;
    const VIEW_FILE_NOT_EXISTS = 3;

    /**
     * instantiation du frontController
     */
    private function __construct()
    {
        $this->_dirs = self::$mainConfig->get('dirs');
        $this->_format = self::$mainConfig->get('format');
        $this->_debug = self::$mainConfig->get('debug');

        /** Chargement du rep app par défaut **/
        $count = count(self::$appDirs);
        $this->app = self::$appDirs[$count - 1]['name'];
        unset($count);
    }

    /**
     * Renvois une instance du FrontController
     *
     * @return FrontController
     */
    public static function getInstance()
    {
        if (!self::$_singleton) {
            self::$_singleton = new self();
        }
        return self::$_singleton;
    }

    /**
     * Initialise les données nécessaires pour FrontController
     *
     * @return void
     */
    public function init()
    {

        /** Chargement de la configuration **/
        self::$mainConfig = new Config('../config/main.ini');

        /** Detection de l'environnement **/
        $localHostnames = explode(',', self::$mainConfig->get('detect', 'development'));
        if (in_array($_SERVER['SERVER_NAME'], $localHostnames) === true) {
            $env = 'local';
        } else {
            $env = 'online';
        }

        self::$envConfig = new Config('../config/' . $env . '.ini');


        /* = Fichiers de configuration
          ------------------------------- */
        Registry::set('mainconfig', self::$mainConfig);
        Registry::set('envconfig', self::$envConfig);


        /* = base de données
          ------------------------------- */
        $db = DB::factory(self::$envConfig->get('database'));
        Registry::set('db', $db);

        Registry::set('project-name', self::$mainConfig->get('name', 'project'));
        $emails = self::$envConfig->get('email');

        Registry::set('basehref', self::$envConfig->get('url', 'base'));

        /* = Permet de forcer une version (utile en dev ou recette)
          ------------------------------- */
        if (isset($_GET['version-force'])) {
            $_SESSION['version-force'] = $_GET['version-force'];
        }
        if (isset($_SESSION['version-force'])) {
            $sufVersion = $_SESSION['version-force'];
        } else {
            if (isset($_GET['version'])) {
                $sufVersion = $_GET['version'];
            } else {
                $sufVersion = 'FR';
            }
        }

        if ($env != 'local') {
            $serverUrl = str_replace('www.', '', $_SERVER['SERVER_NAME']);
            Registry::set('url', 'http://www.' . $serverUrl . '/');
            Registry::set('basehref', 'http://www.' . $serverUrl . '/');


            Registry::set('email', $emails);

        } else {
            $serverUrl = str_replace('solire-02', $_SERVER['SERVER_NAME']
                       . ':' . $_SERVER['SERVER_PORT'], Registry::get('basehref'));
            Registry::set('url', $serverUrl);
            Registry::set('basehref', $serverUrl);

            /** Ajout d'un prefix au mail **/
            if (isset($emails['prefix']) && $emails['prefix'] != '') {
                $prefix = $emails['prefix'];
                unset($emails['prefix']);
                foreach ($emails as &$email) {
                    $email = $prefix . $email;
                }
            }
            Registry::set('email', $emails);
        }


        $query = 'SELECT * '
               . 'FROM `version` '
               . 'WHERE `domaine` = "' . $serverUrl . '"';
        $version = $db->query($query)->fetch(\PDO::FETCH_ASSOC);

        if (!isset($version['id'])) {
            $query = 'SELECT * '
                   . 'FROM `version` '
                   . 'WHERE `suf` LIKE ' . $db->quote($sufVersion);
            $version = $db->query($query)->fetch(\PDO::FETCH_ASSOC);
        }


        Registry::set('analytics', $version['analytics']);
        define('ID_VERSION', $version['id']);
        define('SUF_VERSION', $version['suf']);

    }

    /**
     * Ajoute une partie de rewrinting
     *
     * @param string $rewriting Parte de rewriting à ajouter
     *
     * @return void
     * @uses Slrfw\Library\Controller->acceptRew Contrôle si le
     * rewriting est accepté
     */
    private function addRewriting($rewriting)
    {
        $className = $this->getClassName();
        $class = new $className();
        if ($class->acceptRew !== true) {
            $exc = new \Slrfw\Library\Exception\HttpError('Erreur HTTP');
            $exc->http(404, null);
            throw $exc;
        }
        $this->rewriting[] = $rewriting;
    }

    /**
     * Renvois le nom de la classe du controller
     *
     * @param string $controller Nom du controller
     * @param string $app        Code du repertoire App à utiliser
     *
     * @return string
     */
    protected function getClassName($controller = null, $app = null)
    {
        if (!empty($app)) {
            $app = ucfirst($app);
        } else {
            $app = $this->app;
        }
        $class = 'Slrfw\\' . $app . '\\' . $this->application . '\\Controller\\';
        if (empty($controller)) {
            $class .= $this->controller;
        } else {
            $class .= $controller;
        }

        return $class;
    }

    /**
     * Lecture de l'url pour en extraire les données
     *
     * @return void
     */
    public function parseUrl()
    {
        /** Nom de l'application par défaut */
        $this->application = 'Front';

        $this->controller = $this->getDefault('controller');

        $this->rewriting = array();

        $controller = false;
        /** Contrôle du controller **/
        $rewritingMod = false;
        if (isset($_GET['controller']) && !empty($_GET['controller'])) {
            $url = strtolower($_GET['controller']);
            $arrSelect = explode('/', $url);
            unset($url);

            $application = false;
            $appDir = '../app/';
            $rewritingMod = false;
            foreach ($arrSelect as $ctrl) {
                /**
                 * Si on est en mode rewriting,
                 * tout ce qui reste de l'url est du rewriting
                 **/
                if ($rewritingMod === true) {
                    $this->addRewriting($ctrl);
                    continue;
                }

                /**
                 * Si le contrôller n'est pas en minuscule
                 *  on concidère que c'est un rewriting
                 **/
                if ($ctrl != strtolower($ctrl)) {
                    $this->addRewriting($ctrl);
                    $rewritingMod = true;
                    continue;
                }

                /** On test l'existence du dossier app répondant au nom $ctrl **/
                if ($this->testApp($ctrl)) {

                    /** Erreur doublon */
                    if ($this->application == $ctrl) {
                        $this->addRewriting($ctrl);
                        $rewritingMod = true;
                        continue;
                    }

                    /** Si un application est déjà définie */
                    if ($application === true) {
                        $this->addRewriting($ctrl);
                        $rewritingMod = true;
                        continue;
                    }
                    $this->application = ucfirst($ctrl);
                    $application = true;
                    continue;
                }

                /** Test existence d'un controller **/
                if ($this->classExists($ctrl)) {

                    if ($controller === true) {
                        $this->addRewriting($ctrl);
                        $rewritingMod = true;
                        continue;
                    }
                    $this->controller = ucfirst($ctrl);
                    $controller = true;
                    continue;
                }

                $this->addRewriting($ctrl);
                $rewritingMod = true;
            }
        }

        if ($controller === false) {
            $this->controller = $this->getDefault('controller');
        }

        if (isset($_GET['action']) && !empty($_GET['action'])) {
            if ($rewritingMod === true) {
                $this->addRewriting($_GET['action']);
                $this->action = $this->getDefault('action');
            } else {
                $class = $this->getClassName();
                $method = sprintf(
                    $this->getFormat('controller-action'), $_GET['action']
                );
                if (method_exists($class, $method)) {
                    $this->action = $_GET['action'];
                } else {
                    $this->addRewriting($_GET['action']);
                    $this->action = $this->getDefault('action');
                }
            }
        } else {
            $this->action = $this->getDefault('action');
        }
    }

    /**
     * Test si le morceau d'url est une application
     *
     * @param string $ctrl Morceau d'url
     *
     * @return boolean
     */
    private function testApp($ctrl)
    {
        foreach (self::$appDirs as $app) {
            $testPath = new Path($app['dir'] . DS . $ctrl, Path::SILENT);
            if ($testPath->get()) {
                $this->app = $app['name'];
                return true;
            }
        }

        return false;
    }

    protected function classExists($ctrl)
    {
        foreach (self::$appDirs as $app) {
            $class = $this->getClassName(ucfirst($ctrl), $app['name']);
            if (class_exists($class)) {
                $this->app = $app['name'];
                return true;
            }
        }
        return false;
    }

    /**
     * Lance l'affichage de la page
     *
     * @param string $application Nom de l'api à utiliser
     * @param string $controller  Nom du controller à lancer
     * @param string $action      Nom de l'action à lancer
     *
     * @return boolean
     */
    public static function run($application = null, $controller = null, $action = null)
    {
        $front = self::getInstance();

        if (empty($application) && empty($controller) && empty($actString)) {
            $front->parseUrl();
        } else {
            $front->application = $application;
            $front->controller = $controller;
            $front->action = $action;
        }
        unset($application, $controller, $action);

        /**
         * Pour eviter les conflits lors de l'envois d'une 404 on ne charge les
         * informations relative à l'api
         **/
        if (self::$singleApi === false) {
            $front->setAppConfig();
        }
        self::$singleApi = true;

        $class = 'Slrfw\\' . $front->app . '\\' . $front->application
               . '\\Controller\\' . $front->controller;
        $method = sprintf($front->getFormat('controller-action'), $front->action);
        if (!class_exists($class)) {
            $front->debug(self::CONTROLLER_CLASS_NOT_EXISTS, array($class));
            return false;
        }

        if (!method_exists($class, $method)) {
            $front->rewriting[] = $front->action;
            $method = $front->getDefault('action');
            if (!method_exists($class, $method)) {
                $front->debug(self::CONTROLLER_ACTION_NOT_EXISTS, array($class, $method));
                return false;
            }
        }

        $instance = new $class();

        /** Passage des paramètres de rewriting **/
        $instance->setRewriting($front->rewriting);

        $instance->start();
        $view = $instance->getView();
        foreach (self::$appDirs as $app) {
            $viewDir = sprintf($front->getDir('views'), $app['dir']
                     . DS . strtolower($front->application));
            $view->setDir($viewDir);
        }
        $view->setTemplate('main');
        $view->setFormat($front->getFormat('view-file'));
        $view->base = $front->getDir('base');
        $instance->$method();
        $instance->shutdown();

        if ($view->isEnabled()) {
            try {
                $view->display($front->controller, $front->action, false);
            } catch (\Exception $exc) {
                $front->debug(self::VIEW_FILE_NOT_EXISTS, array($view));
                return false;
            }
        }

        return true;
    }

    /**
     * Charge la configuration de l'application utilisée
     *
     * Place le fichier de configuration dans le Registre, à 'appconfig'
     * Paramètre le basehref pour prendre en compte l'application si besoin
     *
     * @return boolean
     *
     * @uses Registry
     */
    public function setAppConfig()
    {
        /** Id api **/
        $db = Registry::get('db');
        $query = 'SELECT id '
               . 'FROM gab_api '
               . 'WHERE name = ' . $db->quote($this->application);
        $apiId = $db->query($query)->fetchColumn();

        if (empty($apiId)) {
            $apiId = 1;
        }
        if (!defined('ID_API')) {
            define('ID_API', $apiId);
        }
        $configPath = 'app_' . $this->application . '.ini';
        $configPath = strtolower($configPath);
        $path = new Path($configPath, Path::SILENT);
        if (!$path->get()) {
            return false;
        }
        $appConfig = new Config($path->get());
        Registry::set('appconfig', $appConfig);


        /** Url **/
        if ($this->application == 'Front') {
            $baseSuffix = '';
        } else {
            $baseSuffix = strtolower($this->application) . '/';
        }

        Registry::set('base', self::$envConfig->get('url', 'base'));
        Registry::set('basehref', self::$envConfig->get('url', 'base') . $baseSuffix);
        Registry::set('baseroot', self::$envConfig->get('root', 'base'));



        return true;
    }

    /**
     * Enregistre un nouveau répertoire d'app
     *
     * @param string $name Nom du répertoire
     *
     * @return void
     */
    public static function setApp($name)
    {
        $name = strtolower($name);
        self::$appDirs[] = array(
            'name' => ucfirst($name),
            'dir' => $name,
        );
    }


    /**
     * Renvois les valeurs par défaut propre à l'application
     *
     * @param string $key Identifiant de la configuration demandé
     *
     * @return string
     */
    public function getDefault($key)
    {
        $name = strtolower('app_' . $this->application);
        $config = self::$mainConfig->get($name);

        if (isset($config[$key . '-default'])) {
            return $config[$key . '-default'];
        }

        return '';
    }

    /**
     * Renvois les chemins vers les dossiers configurés
     *
     * @param string $key Identifiant du dossier
     *
     * @return string
     */
    public function getDir($key)
    {
        if (isset($this->_dirs[$key])) {
            return $this->_dirs[$key];
        }
        return '';
    }

    /**
     * Renvois les formats des noms
     *
     * @param string $key Nom du format
     *
     * @return string
     */
    public function getFormat($key)
    {
        if (isset($this->_format[$key])) {
            return $this->_format[$key];
        }
        return '';
    }

    /**
     * Marque une erreur
     *
     * @param int   $id     identifiant de l'erreur
     * @param array $params Suite d'informations relatives à l'erreur
     *
     * @return void
     * @throws Exception\HttpError
     */
    public function debug($id, $params)
    {
        if ($this->_debug['enable']) {
            $errors = array(
                self::CONTROLLER_FILE_NOT_EXISTS => 'Le fichier de '
                    . 'contr&ocirc;leur <strong>%s</strong> n\'existe pas.',
                self::CONTROLLER_CLASS_NOT_EXISTS => 'La classe de '
                    . 'contr&ocirc;leur <strong>%s</strong> n\'existe pas.',
                self::CONTROLLER_ACTION_NOT_EXISTS => 'Impossible de trouver '
                    . 'l\'action <strong>%s</strong> pour le contr&ocirc;leur '
                    . '<strong>%s</strong> dans le fichier <strong>%s</strong>.',
                self::VIEW_FILE_NOT_EXISTS => 'Le fichier de vue '
                    . '<strong>%s</strong> n\'existe pas.',
            );
            $message = sprintf($errors[$id], $params);
            throw new Exception\Marvin(new \Exception($message));
        } else {
            $error = new Exception\HttpError('');
            $error->http(404);
            throw $error;
        }
    }
}

