<?php

/**
 * Gestionnaire de connexion à la base de données
 *
 * @author Adrien
 * @version 1.3
 */
require_once 'mypdo.php';

class DB
{

    /**
     * Contient les objets PDO de connection
     * @var array
     */
    static private $present;

    /**
     * Parametrage de base
     * @var array
     */
    static private $config = array(
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_STATEMENT_CLASS => array('MyPDOStatement')
    );

    private function __construct()
    {
        
    }

    /**
     * Crée une connection à la base de données.
     *
     *
     * @param array $ini doit être sous la forme :
     *      dsn => ''        // chaine de connexion propre à pdo, par exemple : 
     * "mysql:dbname=%s;host=%s" ou "mysql:dbname=%s;host=%s;port=%s"
     *      host => ''       // host de la connexion à la bdd
     *      dbname => ''     // Nom de la base de données
     *      user => ''       // utilisateur mysql
     *      password => ''   // mot de passe
     *      port => ''       // [facultatif], port de la connexion
     *      utf8 => true     // [facultatif], activer encodage buffer sortie
     *      error => true    // [facultatif], activer les erreurs pdo
     *      profil => false  // [facultatif], activer le profiling
     *      nocache => false // [facultatif], désactiver le cache
     *
     * @param string $otherDbName Nom de la base de données dans le cas où l'on
     * veut se connecter à une difference de celle présente dans $ini
     * @return PDO
     */
    public static function factory($ini, $otherDbName = null)
    {
        
        if ($otherDbName)
            $ini['dbname'] = $otherDbName;
        
        if (isset(self::$present[$ini['dbname']]))
            return self::$present[$ini['dbname']];
        

        $DSN = sprintf($ini['dsn'], $ini['dbname'], $ini['host'], $ini['port']);
        
        self::$present[$ini['dbname']] =
                new MyPDO($DSN, $ini['user'], $ini['password'], self::$config);


        /* = Option d'affichage des erreurs
          | Parametrable dans le config.ini de la bdd
          `-------------------------------------------------------------------- */
        if (isset($ini['error']) && $ini['error'] == true)
            self::$present[$ini['dbname']]->setAttribute(
                    PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /* = Profiling
          `-------------------------------------------------------------------- */
        if (isset($ini['profil']) && $ini['profil'] == true)
            self::$present[$ini['dbname']]->exec('SET profiling = 1;');

        /* = Spécifique à mysql
          | Modifie l'encodage du buffer de sortie de la base qui est par
          | defaut en ISO pour être en accord avec l'encodage de la base.
          `-------------------------------------------------------------------- */
        if (isset($ini['utf8']) && $ini['utf8'] == true)
            self::$present[$ini['dbname']]->exec('SET NAMES UTF8');

        /* = Cache
          `-------------------------------------------------------------------- */
        if (isset($ini['nocache']) && $ini['nocache'] == true)
            self::$present[$ini['dbname']]->exec('SET SESSION query_cache_type = OFF;');

        return self::$present[$ini['dbname']];
    }

}