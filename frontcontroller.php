<?php
/**
 * Front controller
 *
 * @author     dev <dev@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Front controller
 *
 * @author     dev <dev@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
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
     * Nom de l'application en cours d'utilisation
     *
     * @var string
     */
    public static $appName;

    /**
     * Préfix url pour l'application
     *
     * @var string
     */
    public static $appUrl = '';

    /**
     * Id api utilisé par page du front
     * @var int
     */
    public static $idApiRew = 1;

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

    private $translate = false;
    private $view = false;

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
     * Renvois le tableau des répertoires app
     *
     * @return array
     */
    public static function getAppDirs()
    {
        return self::$appDirs;
    }

    /**
     * Initialise les données nécessaires pour FrontController
     *
     * @return void
     */
    public static function init()
    {
        /** Chargement de la configuration **/
        self::$mainConfig = new Config('config/main.ini');
        self::$envConfig = new Config('config/local.ini');

        /* = Fichiers de configuration
          ------------------------------- */
        Registry::set('mainconfig', self::$mainConfig);
        Registry::set('envconfig', self::$envConfig);

        Registry::set('baseroot', self::$envConfig->get('base', 'root'));

        /* = base de données
          ------------------------------- */
        try {
            $db = DB::factory(self::$envConfig->get('database'));
        } catch (\PDOException $exc) {
            throw new Exception\Lib($exc->getMessage());
        }
        Registry::set('db', $db);

        Registry::set('project-name', self::$mainConfig->get('project', 'name'));
        $emails = self::$envConfig->get('email');

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

    /**
     * Ajoute une partie de rewrinting
     *
     * @param string $rewriting Parte de rewriting à ajouter
     *
     * @return void
     * @uses Slrfw\Controller->acceptRew Contrôle si le
     * rewriting est accepté
     */
    private function addRewriting($rewriting)
    {
        $className = $this->getClassName();
        $class = new $className();
        if ($class->acceptRew !== true) {
            $exc = new \Slrfw\Exception\HttpError('Erreur HTTP');
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
        $class = $app . '\\' . $this->application . '\\Controller\\';
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
        $this->application = self::$mainConfig->get('project', 'defaultApp');
        self::$appName = $this->application;

        self::loadAppConfig();

        /** On met la valeur par défaut pour pouvoir tester l'app par défaut **/
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
                if ($this->testApp($ctrl) !== false) {
                    /** Si un application est déjà définie */
                    if ($application === true) {
                        $this->addRewriting($ctrl);
                        $rewritingMod = true;
                        continue;
                    }

                    $conf = self::loadAppConfig($ctrl);
                    $idApi = $conf->get('fx', 'idApi');
                    if (!empty($idApi)) {
                        self::$idApiRew = $idApi;
                        self::$appUrl = $ctrl;
                        unset($idApi, $conf);
                        continue;
                    }

                    $this->application = ucfirst($ctrl);
                    self::$appName = $this->application;

                    $this->app = $this->testApp($ctrl);
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

            /** Si l'application à changé on charge sa configuration **/
            if ($application === true) {
                self::loadAppConfig();
            }
        }


        if ($controller === false) {
            $this->controller = $this->getDefault('controller');
            $this->classExists($this->controller);
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
     * Cherche un fichier dans les applications
     *
     * @param string  $path    Chemin Chemin du dossier / fichier à chercher dans
     * les applications
     * @param boolean $current Utiliser le nom de l'application courante
     *
     * @return string|boolean
     */
    final public static function search($path, $current = true)
    {
        $appLibDir = self::$mainConfig->get('appLibDir');

        $initialPath = $path;
        if ($current === true) {
            $path = DS . strtolower(self::$appName) . DS . $path;
        } else {
            $path = DS . $path;
        }
        foreach (self::$appDirs as $app) {
            if ($current === true) {
                $dir = $app['dir'] . DS . strtolower(self::$appName);
            } else {
                $dir = $app['dir'];
            }
            $fooPath = $app['dir'] . $path;

            // Permet de faire correspondre des répertoires d'application
            if ($appLibDir && isset($appLibDir[$dir])) {
                $dir = $appLibDir[$dir];
                $fooPath = $dir . DS . $initialPath;
            }

            $testPath = new Path($fooPath, Path::SILENT);
            if ($testPath->get() !== false) {
                return $testPath->get();
            }
        }

        return false;
    }

    /**
     * Cherche une classe
     *
     * @param string $className nom de la classe, avec les namespace, qui sera
     * préfixé par le nom de l'app
     *
     * @return string|boolean
     */
    final public static function searchClass($className)
    {
        $path = str_replace('\\', DS, $className);
        $path = DS . strtolower($path) . '.php';

        foreach (self::$appDirs as $app) {
            $fooPath = $app['dir'] . $path;
            $testPath = new Path($fooPath, Path::SILENT);
            if ($testPath->get() !== false) {
                return $app['name'] . '\\' . $className;
            }
        }

        return false;
    }

    /**
     * Charge la configuration relative à l'application
     *
     * @param string $test ?
     *
     * @return \Slrfw\Config|null
     */
    final public static function loadAppConfig($test = null)
    {
        if (empty($test)) {
            $confPath = self::search('conf.ini');
        } else {
            $confPath = self::search($test . DS . 'conf.ini', false);
        }
        if (!empty($confPath)) {
            $appConfig = new Config($confPath);
            if (empty($test)) {
                Registry::set('appconfig', $appConfig);
            }

            return $appConfig;
        }

        return null;
    }

    /**
     * Test si le morceau d'url est une application
     *
     * @param string $ctrl Morceau d'url
     *
     * @return boolean|string false si ce n'est pas une application, sinon
     * renvoi le dir App
     */
    private function testApp($ctrl)
    {
        foreach (self::$appDirs as $app) {
            $testPath = new Path($app['dir'] . DS . $ctrl, Path::SILENT);
            if ($testPath->get()) {
                return ucfirst($app['dir']);
            }
        }

        return false;
    }

    /**
     * Contrôle l'existence d'une classe controller
     *
     * @param string $ctrl Nom de la classe
     *
     * @return boolean
     */
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
     * @param string $controller Nom du controller à lancer
     * @param string $action     Nom de l'action à lancer
     *
     * @return boolean
     */
    public static function run($controller = null, $action = null)
    {
        $front = self::getInstance();
        if (empty($controller) && empty($action)) {
            $front->parseUrl();
        } else {
            /** Chargement du controller **/
            $front->classExists($controller);
            $front->controller = $controller;

            /** Chargement de l'action **/
            $front->action = $action;

            if (isset($front->view) && !empty($front->view)) {
                $defaultViewPath = strtolower($front->controller) . DS . $front->action;
                $front->view->setViewPath($defaultViewPath);
                unset($defaultViewPath);
            }

            self::loadAppConfig();
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

        $front->setVersion();

        $class = $front->getClassName();
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

        /**
         * On créé le controller
         */
        $instance = new $class();

        $instance
            ->setView($front->loadView())
            ->setTranslate($front->loadTranslate())
            ->setRewriting($front->rewriting)
        ;

        $instance->start();
        $instance->$method();
        if ($front->view->isEnabled()) {
            $instance->shutdown();
            $front->view->display();
        }
        return true;
    }

    /**
     * Chargement de la classe de traduction
     *
     * @return \Slrfw\TranslateMysql
     */
    public function loadTranslate()
    {
        if ($this->translate !== false) {
            return $this->translate;
        }

        $this->translate = new TranslateMysql(ID_VERSION, ID_API, Registry::get('db'));
        $this->translate->addTranslation();

        return $this->translate;
    }

    /**
     * Chargement de la vue
     *
     * @return View
     */
    public function loadView()
    {
        if ($this->view !== false) {
            return $this->view;
        }

        $this->view = new View();

        $defaultViewPath = strtolower($this->controller) . DS . $this->action;
        $mainViewPath = sprintf($this->getFormat('view-file'), 'main');

        try {
            $this->view
                ->setPathFormat($this->getFormat('view-file'))
                ->setPathPrefix(self::$mainConfig->get('dirs', 'views'))
                ->setTranslate($this->loadTranslate())
                ->setMainPath('main')
                ->setViewPath($defaultViewPath)
            ;
        } catch (Exception\Lib $exc) {
            if ($exc->getCode() === 0 || $exc->getCode() > 400) {
                throw $exc;
            }
        }

        return $this->view;
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
            /** On essaie de recuperer l'api par le domaine **/
            $serverUrl = $_SERVER['SERVER_NAME'];
            $query = 'SELECT id_api '
                   . 'FROM version '
                   . 'WHERE domaine = ' . $db->quote($serverUrl);
            $apiId = $db->query($query)->fetchColumn();
            if (empty($apiId)) {
                $apiId = 1;
            }
        }
        if (!defined('ID_API')) {
            define('ID_API', $apiId);
        }
        $configPath = 'config/app_' . $this->application . '.ini';
        $configPath = strtolower($configPath);

        $path = new Path($configPath, Path::SILENT);
        if (!$path->get()) {
            return false;
        }

        return true;
    }

    /**
     * Défini la version en cours de l'application
     *
     * Défini la constante ID_VERSION et SUF_VERSION
     * Paramètre le basehref
     *
     * @return boolean
     *
     * @uses Registry
     */
    public function setVersion()
    {
        $db = Registry::get('db');

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

        /**
         * On verifie en base si le nom de domaine courant correspond
         *  à une langue
         **/
        $serverUrl = $_SERVER['SERVER_NAME'];

        $query = 'SELECT * '
               . 'FROM `version` '
               . 'WHERE  id_api = ' . intval(ID_API) . ' AND `domaine` = "' . $serverUrl . '"';
        $version = $db->query($query)->fetch(\PDO::FETCH_ASSOC);

        /**
         * Si aucune langue ne correspond
         *  on prend la version FR
         **/
        if (!isset($version['id'])) {
            $query = 'SELECT * '
                   . 'FROM `version` '
                   . 'WHERE id_api = ' . intval(ID_API)
                   . ' AND `suf` LIKE ' . $db->quote($sufVersion);
            $version = $db->query($query)->fetch(\PDO::FETCH_ASSOC);

            /**
             * Dans le cas d'un changement d'api
             *  Si la langue en SESSION n'existe pas dans l'api
             *  On récupère la version FR DE la nouvelle api
             */
            if (!isset($version['id'])) {
                $sufVersion = 'FR';
                $query = 'SELECT * '
                   . 'FROM `version` '
                   . 'WHERE id_api = ' . intval(ID_API)
                   . ' AND `suf` LIKE ' . $db->quote($sufVersion);
                $version = $db->query($query)->fetch(\PDO::FETCH_ASSOC);
            }

            $serverUrl = self::$envConfig->get('base', 'url');
            Registry::set('url', $serverUrl);
            Registry::set('basehref', $serverUrl);
        } else {
            Registry::set('url', 'http://' . $serverUrl . '/' . Registry::get('baseroot'));
            Registry::set('basehref', 'http://' . $serverUrl . '/' . Registry::get('baseroot'));
        }


        Registry::set('analytics', $version['analytics']);

        if (!defined('ID_VERSION')) {
            define('ID_VERSION', $version['id']);
            define('SUF_VERSION', $version['suf']);
        }

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
        $conf = Registry::get('appconfig');
        return $conf->get('default', $key);
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
     * Renvoi l'url complète de la page courante
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        // On ajoute selon le cas http ou https
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $currentURL = 'https://';
        } else {
            $currentURL = 'http://';
        }

        // On ajoute le nom d'hote de l'url
        $currentURL .= $_SERVER['SERVER_NAME'];

        // Si le port est différent de 80 ou 443, on l'ajoute à l'url
        if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
            $currentURL .= ':' . $_SERVER['SERVER_PORT'];
        }

        // On ajoute enfin la fin de l'url
        $currentURL .= $_SERVER['REQUEST_URI'];
        return $currentURL;
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

