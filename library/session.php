<?php
/**
 * Gestionnaire des sessions
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

/**
 * Gestionnaire des sessions
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Session
{
    /**
     * Informations sur le compte lié à la session
     *
     * @var array
     */
    private $user;

    /**
     * Nom du cookie
     *
     * @var string
     */
    private $cookieName;

    /**
     * Etat de la connexion
     *
     * @var bool
     */
    private $connected = false;

    /**
     * Configuration de la session
     *
     * @var array
     */
    private $config;

    /**
     * Initialise une session de type $sessionCode
     *
     * @param type $sessionCode Code d'identification du type de sessions
     *
     * @return boolean
     * @throws LibException
     * @config main [format] session Format du bloc session dans la config main
     * @uses Session->regen()
     */
    public function __construct($sessionCode)
    {
        $config = Registry::get('mainconfig');
        $format = $config->get('session', 'format');

        if (empty($format)) {
            throw new Exception\Lib('Aucune configuration format des sessions');
        }

        $sessionCode = sprintf($format, $sessionCode);

        $this->config = $config->get($sessionCode);

        if (empty($this->config)) {
            throw new Exception\Lib('Aucune configuration pour la session [' . $sessionCode . ']');
        }

        $this->cookieName = $this->config['cookie'];

        if (isset($_COOKIE[$this->cookieName]) && !empty($_COOKIE[$this->cookieName])) {
            $foo = explode('_', $_COOKIE[$this->cookieName]);

            if (count($foo) == 2) {
                $db = Registry::get('db');
                $query = $db->prepare($this->config['query']);
                $query->bindValue(':id', $foo[1], \PDO::PARAM_INT);
                $query->execute();
                $user = $query->fetch(\PDO::FETCH_ASSOC);

                if (isset($user['certificat'])) {
                    $this->certificat = $user['certificat'];
                }

                $token = $this->makeToken($user['login'], date('m-d'), $user['id']);

                if ($token == $foo[0]) {
                    $this->connected = true;
                    $this->user = $this->presentVars($user);
                    $this->oldData = $user;
                    $this->regen();
                    return $this;
                }
            }
        }

        $this->disconnect();
    }

    /**
     * Renvois les informations sur la session en cours
     *
     * @param string $key Nom de la variable à renvoyer
     *
     * @return mixed
     */
    public function getUser($key = null)
    {
        if ($key != null) {
            return $this->user[$key];
        }
        return $this->user;
    }

    /**
     * Test si il y a une connection en cours
     * Renvois vrai si c'est le cas
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Génère le token de la session à partir des paramètres
     *
     * @return string
     */
    private function makeToken()
    {
        $foo = func_get_args();
        $token = implode(', ', $foo);
        $token = hash('md5', $token);

        return $token;
    }

    /**
     * Relance le temps de garde de la session
     *
     * @return void
     */
    private function regen()
    {
        if (isset($_COOKIE[$this->cookieName]) && !empty($_COOKIE[$this->cookieName])) {
            $life = time() + $this->config['duration'];
            setcookie(
                $this->cookieName, $_COOKIE[$this->cookieName], $life, '/'
            );
        }
    }

    /**
     * Génère un mot de passe
     *
     * @param int $longueur Longueur du mot de passe
     *
     * @return string
     */
    public static function makePass($longueur = 9)
    {
        $caractere = '0123456789abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJK'
                   . 'LMNOPQRSTUVWXYZ0123456789';
        $long = strlen($caractere);
        $mdp = '';
        for ($i = 1; $i < $longueur; $i++) {
            $mdp .= substr($caractere, rand(0, $long), 1);
        }
        return $mdp;
    }


    /**
     * "Sale" et fait un sha256 du mot de passe
     *
     * @param string $mdp Mot de passe du client
     *
     * @return string
     */
    final static public function prepareMdp($mdp)
    {
        $ordre = array(
            '$char . $id',
            '$id . $id',
            '$id . $char',
            '$char . $id . $char',
            '$char . $char',
        );

        $key = str_split(strrev($mdp));
        $result = '';
        $i = ceil(count($key) / 3);
        $iOrdre = 0;
        foreach (str_split($mdp) as $char) {
            if (!isset($key[$i])) {
                $i = 0;
            }

            if (empty($ordre[$iOrdre])) {
                $iOrdre = 0;
            }
            $id = ord($key[$i]);
            $exec = '$result .= ' . $ordre[$iOrdre] . ';';
            eval($exec);
            $iOrdre++;
            $i++;
        }
        return hash('sha256', $result);
    }

    /**
     * Format le nom des variable pour qu'il soit compatible avec la notation camel
     *
     * @param array $array Tableau de valeurs sortie de la bdd
     *
     * @return array
     */
    final private function presentVars(array $array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $key = strtolower($key);
            if (strpos($key, '_') === false) {
                $result[$key] = $value;
                continue;
            }
            preg_match_all('#_([a-z])#', $key, $match);

            if (empty($match)) {
                $result[$key] = $value;
                continue;
            }

            for ($i = 0; $i < count($match[0]); $i++) {
                $replace = strtoupper($match[1][$i]);
                $key = str_replace($match[0][$i], $replace, $key);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Crée une session à partir du couple Courriel / Mot de passe
     *
     * @param string $login    Courriel de la session
     * @param string $password Mot de passe de la session
     *
     * @return bool
     * @throws LibException
     * @throws UserException
     */
    public function connect($login, $password)
    {
        if ($this->connected) {
            return $this->connected;
        }

        if (!is_string($login) || !is_string($password)) {
            throw new Exception\Lib('Format du Courriel / Mot de passe incorrect');
        }

        $db = Registry::get('db');
        $query = $db->prepare($this->config['queryLogin']);
        $query->bindValue(':login', $login, \PDO::PARAM_STR);
        $query->execute();
        $user = $query->fetch(\PDO::FETCH_ASSOC);

        $password = self::prepareMdp($password);

        if ($user['pass'] != $password) {
            throw new Exception\User('Couple Courriel / Mot de passe incorrect');
        }

        if (isset($user['certificat'])) {
            $this->certificat = $user['certificat'];
        }

        $token = $this->makeToken($user['login'], date('m-d'), $user['id']);
        $cookie = $token . '_' . $user['id'];

        $life = time() + $this->config['duration'];
        if (setcookie($this->cookieName, $cookie, $life, '/')) {
            $this->user = $user;
            $this->connected = true;
        } else {
            throw new LibException('Impossible d\'enregistrer un cookie');
        }

        return $this->connected;
    }

    /**
     * Désactive la session
     *
     * @return void
     */
    public function disconnect()
    {
        $this->connected = false;
        setcookie($this->cookieName, '', time() - 42, '/');
    }

    /**
     * Contrôle d'existence d'une variable de la session
     *
     * @param string $name Nom de la variable
     *
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->user[$name]) && !empty($this->user[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Récupération des variables de la session
     * Fonction présente pour rétro compatibilité, ne pas s'en servir
     *
     * @param string $name Nom de la variable
     *
     * @return mixed
     * @deprecated
     */
    public function get($name)
    {
        if (is_array($this->oldData) && array_key_exists($name, $this->oldData)) {
            return $this->oldData[$name];
        }

        return null;
    }

    /**
     * Récupération des variables de la session
     *
     * @param string $name Nom de la variable
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->user[$name];
    }
}

