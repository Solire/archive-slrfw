<?php
/**
 * Gestionnaire de connexion à la base de données
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

/**
 * Gestionnaire de connexion à la base de données
 *
 * @package    Library
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class DB
{

    /**
     * Contient les objets PDO de connection
     *
     * @var array
     */
    static private $_present;

    /**
     * Parametrage de base
     *
     * @var array
     */
    static private $_config = array(
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
//        \PDO::ATTR_STATEMENT_CLASS => array('Slrfw\Library\MyPDOStatement')
    );

    /**
     * Inutilisé
     *
     * @ignore
     */
    private function __construct()
    {

    }

    /**
     * Crée une connection à la base de données.
     *
     * @param array  $ini         doit être sous la forme :<ul>
     * <li>dsn => ''        // chaine de connexion propre à pdo, par exemple :
     * "mysql:dbname=%s;host=%s" ou "mysql:dbname=%s;host=%s;port=%s"</li>
     * <li>host => ''       // host de la connexion à la bdd</li>
     * <li>dbname => ''     // Nom de la base de données</li>
     * <li>user => ''       // utilisateur mysql</li>
     * <li>password => ''   // mot de passe</li>
     * <li>port => ''       // [facultatif], port de la connexion</li>
     * <li>utf8 => true     // [facultatif], activer encodage buffer sortie</li>
     * <li>error => true    // [facultatif], activer les erreurs pdo</li>
     * <li>profil => false  // [facultatif], activer le profiling</li>
     * <li>nocache => false // [facultatif], désactiver le cache</li>
     * </ul>
     * @param string $otherDbName Nom de la base de données dans le cas où l'on
     * veut se connecter à une difference de celle présente dans $ini
     *
     * @return MyPDO
     */
    public static function factory($ini, $otherDbName = null)
    {
        if ($otherDbName) {
            $ini['dbname'] = $otherDbName;
        }

        if (isset(self::$_present[$ini['name']])) {
            return self::$_present[$ini['name']];
        }


        $dsn = sprintf(
            $ini['dsn'], $ini['dbname'], $ini['host'], $ini['port']
        );

        self::$_present[$ini['name']] = new MyPDO(
            $dsn, $ini['user'], $ini['password'], self::$_config
        );


        /**
         * Option d'affichage des erreurs
         * Parametrable dans le config.ini de la bdd
         */
        if (isset($ini['error']) && $ini['error'] == true) {
            self::$_present[$ini['name']]->setAttribute(
                \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION
            );
        }

        /** Profiling */
        if (isset($ini['profil']) && $ini['profil'] == true) {
            self::$_present[$ini['name']]->exec('SET profiling = 1;');
        }

        /**
         * Spécifique à mysql
         * Modifie l'encodage du buffer de sortie de la base qui est par
         * defaut en ISO pour être en accord avec l'encodage de la base.
         */
        if (isset($ini['utf8']) && $ini['utf8'] == true) {
            self::$_present[$ini['name']]->exec('SET NAMES UTF8');
        }

        /** Cache */
        if (isset($ini['nocache']) && $ini['nocache'] == true) {
            self::$_present[$ini['name']]->exec('SET SESSION query_cache_type = OFF;');
        }

        return self::$_present[$ini['name']];
    }

    /**
     * Renvois la connexion à la base déjà paramétré
     *
     * @param string $dbName Nom de la base de données
     *
     * @return \PDO
     * @throws LibExeception Si il n'y a pas de bdd répondant au nom $dbName
     */
    final static public function get($dbName)
    {
        if (isset(self::$_present[$dbName])) {
            return self::$_present[$dbName];
        }

        throw new Exception\Lib('Aucune connexion sous le nom ' . $dbName);
    }
}

