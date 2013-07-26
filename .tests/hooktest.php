<?php
/**
 * Tests unitaires sur Hook
 *
 * @package    Slrfw
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

define('TMP_DIR', realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/tmp/') . '/');
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME))
);

require 'slrfw/init.php';


/**
 * Tests unitaires sur Hook
 *
 * @package    Slrfw
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class HookTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Config
     */
    protected $object;

    /**
     * Liste de dossiers de test
     *
     * @var array
     */
    public static $dirs;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        /**
         * Les dossiers doivent être envoyés dans le sens inverse à d'habitude
         * Le dossier le plus bas en premier (app)
         **/
        self::$dirs = array(
            array(
                'name' => 'app',
                'dir' => 'tmp/',
            ),
            array(
                'name' => 'surch',
                'dir' => 'tmp/surch/',
            ),
        );

        mkdir(TMP_DIR . 'hook');
        mkdir(TMP_DIR . 'hook' . DS . 'toto');
        mkdir(TMP_DIR . 'hook' . DS . 'data');
        mkdir(TMP_DIR . 'hook' . DS . 'nointer');
        mkdir(TMP_DIR . 'hook' . DS . 'enreg');
        mkdir(TMP_DIR . 'hook' . DS . 'sub');
        mkdir(TMP_DIR . 'hook' . DS . 'sub/toto');
        $data = <<<END
<?php
namespace App\Hook\Toto;
class MonHook implements \Slrfw\HookInterface {
    function run(\$env) {
        throw new \Slrfw\Exception\User('ToutVaBien');
    }
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'toto/monhook.php', $data);

        $data = <<<END
<?php
namespace App\Hook\Data;
class Data implements \Slrfw\HookInterface {
    function run(\$env) {
        throw new \Slrfw\Exception\User(\$env->message);
    }
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'data/data.php', $data);
        $data = <<<END
<?php
namespace App\Hook\NoInter;
class Data {
    function run(\$env) {
            echo 'ok';
    }
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'nointer/data.php', $data);


        $data = <<<END
<?php
namespace App\Hook\Sub\Toto;
class MonHook implements \Slrfw\HookInterface {
    function run(\$env) {
        throw new \Slrfw\Exception\User('ToutVaBienSub');
    }
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'sub/toto/monhook.php', $data);

        $data = <<<END
<?php
namespace App\Hook\Enreg;
class MonHook implements \Slrfw\HookInterface {
    function run(\$env) {
        \$env->toto = 8;
    }
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'enreg/monhook.php', $data);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        unlink(TMP_DIR . 'hook' . DS . 'toto/monhook.php');
        rmdir(TMP_DIR . 'hook' . DS . 'toto');
        unlink(TMP_DIR . 'hook' . DS . 'data/data.php');
        unlink(TMP_DIR . 'hook' . DS . 'nointer/data.php');
        rmdir(TMP_DIR . 'hook' . DS . 'data');
        rmdir(TMP_DIR . 'hook' . DS . 'nointer');
        unlink(TMP_DIR . 'hook' . DS . 'enreg/monhook.php');
        rmdir(TMP_DIR . 'hook' . DS . 'enreg');
        unlink(TMP_DIR . 'hook' . DS . 'sub/toto/monhook.php');
        rmdir(TMP_DIR . 'hook' . DS . 'sub/toto');
        rmdir(TMP_DIR . 'hook' . DS . 'sub');
        rmdir(TMP_DIR . 'hook');

        unlink(TMP_DIR . 'surch/hook/toto/monhook.php');
        rmdir(TMP_DIR . 'surch/hook/toto');
        rmdir(TMP_DIR . 'surch/hook');
        rmdir(TMP_DIR . 'surch');
    }

    /**
     * Contrôle de l'erreur sur une mauvaise configuration de appDirs
     *
     * @return void
     * @expectedException Slrfw\Exception\Lib
     * @expectedExceptionMessage Problème de configuration appDirs
     */
    public function testConstructErrorConfig()
    {
        $hook = new Hook();
        $hook->exec('toto');
    }

    /**
     * Contrôle d'une bonne construction du hook
     *
     * @return void
     * @expectedException Slrfw\Exception\User
     * @expectedExceptionMessage ToutVaBien
     */
    public function testHookToto()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);

        $hook->exec('toto');
    }

    /**
     * Contrôle d'une bonne construction du hook avec sous répertoire
     *
     * @return void
     * @expectedException Slrfw\Exception\User
     * @expectedExceptionMessage ToutVaBienSub
     */
    public function testHookSubToto()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);
        $hook->setSubdirName('sub');

        $hook->exec('toto');
    }

    /**
     * Contrôle du passage des variables
     *
     * @return void
     * @expectedException Slrfw\Exception\User
     * @expectedExceptionMessage dataOk
     */
    public function testHookData()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);

        $hook->message = 'dataOk';
        $this->assertEquals($hook->message, 'dataOk');

        $hook->exec('data');
    }

    /**
     * Vérification du contrôle de la présence de l'interface
     *
     * @return void
     * @expectedException Slrfw\Exception\Lib
     * @expectedExceptionMessage Hook au mauvais format
     */
    public function testNoInterfaceError()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);

        $hook->exec('nointer');
    }

    /**
     * Contrôle du passage des variables
     *
     * @return void
     */
    public function testHookDataSave()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);

        $hook->toto = array('toto' => 6);
        $this->assertEquals($hook->toto, array('toto' => 6));

        $hook->exec('enreg');
        $this->assertEquals($hook->toto, 8);
    }

    /**
     * Test de la surcharge
     *
     * @return void
     * @expectedException Slrfw\Exception\User
     * @expectedExceptionMessage ToutVaBienSurch
     */
    public function testSurcharge()
    {
        $hook = new Hook();
        $hook->setDirs(self::$dirs);
        mkdir(TMP_DIR . 'surch');
        mkdir(TMP_DIR . 'surch/hook');
        mkdir(TMP_DIR . 'surch/hook' . DS . 'toto');

        $data = <<<END
<?php
namespace Surch\Hook\Toto;
class MonHook implements \Slrfw\HookInterface {
    function run(\$env) {
        throw new \Slrfw\Exception\User('ToutVaBienSurch');
    }
}
END;
        file_put_contents(TMP_DIR . 'surch/hook/toto/monhook.php', $data);
        $hook->exec('toto');
    }
}

