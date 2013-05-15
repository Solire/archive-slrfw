<?php
/**
 * Tests unitaires sur Hook
 *
 * @package    Library
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
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
 * @package    Library
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class HookTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Config
     */
    protected $object;

    public static $dirs;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::$dirs = array(
            array(
                'name' => 'app',
                'dir' => 'tmp/',
            ),
        );

        mkdir(TMP_DIR . 'toto');
        mkdir(TMP_DIR . 'sub');
        mkdir(TMP_DIR . 'sub/toto');
        $data = <<<END
<?php
namespace App\Toto;
function monHook(\$env) {
    throw new \Slrfw\Exception\User('ToutVaBien');
}
END;
        file_put_contents(TMP_DIR . 'toto/monhook.php', $data);

        $data = <<<END
<?php
namespace App\Data;
function data(\$env) {
    throw new \Slrfw\Exception\User(\$env->message);
}
END;
        mkdir(TMP_DIR . 'data');
        file_put_contents(TMP_DIR . 'data/data.php', $data);


        $data = <<<END
<?php
namespace App\Sub\Toto;
function monHook(\$env) {
    throw new \Slrfw\Exception\User('ToutVaBienSub');
}
END;
        file_put_contents(TMP_DIR . 'sub/toto/monhook.php', $data);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        unlink(TMP_DIR . 'toto/monhook.php');
        rmdir(TMP_DIR . 'toto');
        unlink(TMP_DIR . 'data/data.php');
        rmdir(TMP_DIR . 'data');
        unlink(TMP_DIR . 'sub/toto/monhook.php');
        rmdir(TMP_DIR . 'sub/toto');
        rmdir(TMP_DIR . 'sub');
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
        $hook = new Hook('test');
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
        $hook = new Hook('test');
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
        $hook = new Hook('test');
        $hook->setDirs(self::$dirs);
        $hook->setSubdirName('sub');

        $hook->exec('toto');
    }

    /**
     * Contrôle d'une bonne construction du hook avec sous répertoire
     *
     * @return void
     * @expectedException Slrfw\Exception\User
     * @expectedExceptionMessage dataOk
     */
    public function testHookData()
    {
        $hook = new Hook('test');
        $hook->setDirs(self::$dirs);

        $hook->message = 'dataOk';
        $this->assertEquals($hook->message, 'dataOk');

        $hook->exec('data');
    }
}

