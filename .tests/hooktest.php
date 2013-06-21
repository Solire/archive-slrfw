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

        mkdir(TMP_DIR . 'hook');
        mkdir(TMP_DIR . 'hook' . DS . 'toto');
        mkdir(TMP_DIR . 'hook' . DS . 'data');
        mkdir(TMP_DIR . 'hook' . DS . 'enreg');
        mkdir(TMP_DIR . 'hook' . DS . 'sub');
        mkdir(TMP_DIR . 'hook' . DS . 'sub/toto');
        $data = <<<END
<?php
namespace App\Hook\Toto;
function monHook(\$env) {
    throw new \Slrfw\Exception\User('ToutVaBien');
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'toto/monhook.php', $data);

        $data = <<<END
<?php
namespace App\Hook\Data;
function data(\$env) {
    throw new \Slrfw\Exception\User(\$env->message);
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'data/data.php', $data);


        $data = <<<END
<?php
namespace App\Hook\Sub\Toto;
function monHook(\$env) {
    throw new \Slrfw\Exception\User('ToutVaBienSub');
}
END;
        file_put_contents(TMP_DIR . 'hook' . DS . 'sub/toto/monhook.php', $data);

        $data = <<<END
<?php
namespace App\Hook\Enreg;
function monHook(&\$env) {
    \$env->toto = 8;
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
        rmdir(TMP_DIR . 'hook' . DS . 'data');
        unlink(TMP_DIR . 'hook' . DS . 'enreg/monhook.php');
        rmdir(TMP_DIR . 'hook' . DS . 'enreg');
        unlink(TMP_DIR . 'hook' . DS . 'sub/toto/monhook.php');
        rmdir(TMP_DIR . 'hook' . DS . 'sub/toto');
        rmdir(TMP_DIR . 'hook' . DS . 'sub');
        rmdir(TMP_DIR . 'hook');
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
}

