<?php
/**
 * Tests unitaires sur Config
 *
 * @package    Library
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath('../../')
);

require '../init.php';


/**
 * Tests unitaires sur Config
 *
 * @package    Library
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Config
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $data = <<<END
[section1]
key1 = toto

[section2]
key1 = toto

END;
        file_put_contents('test1.ini', $data);

        $data = <<<END
[section1]
key2 = tata

[section2]
key1 = tata

[section3]
key1 = tata

END;
        file_put_contents('test2.ini', $data);

        $data = <<<END
[section1]
key1 = {%var3}toto
var3 = result3

[section2]
key1 = {%section1:var3}suite

END;
        file_put_contents('testVar.ini', $data);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unlink('test1.ini');
        unlink('test2.ini');
        unlink('testVar.ini');
    }

    /**
     * Contrôle de l'erreur sur mauvais fichier
     *
     * @return void
     * @expectedException Exception
     */
    public function testConstruct()
    {
        $foo = new Config('sdfkmjgdfjkmqgssdklmjg.ini');
    }

    /**
     * Contrôle du get
     *
     * @return void
     * @covers Slrfw\Config::get
     */
    public function testGet()
    {
        $conf = new Config('test1.ini');
        $this->assertEquals($conf->get('section1', 'key1'), 'toto');
        $this->assertEquals($conf->get('section1'), array('key1' => 'toto'));
    }


    /**
     * Contrôle de la bonne gestion des variables dans les .ini
     *
     * @return void
     */
    public function testVar()
    {
        $conf = new Config('testVar.ini');
        $this->assertEquals($conf->get('section1', 'key1'), 'result3toto');
        $this->assertEquals($conf->get('section2', 'key1'), 'result3suite');
    }

    /**
     * Contrôle du fonctionnement des extends
     *
     * @return void
     * @covers Slrfw\Config::setExtends
     */
    public function testSetExtends()
    {
        $conf = new Config('test1.ini');
        $conf->setExtends('test2.ini');

        $this->assertEquals($conf->get('section1', 'key1'), 'toto');
        $this->assertEquals($conf->get('section1', 'key2'), 'tata');
        $this->assertEquals($conf->get('section2', 'key1'), 'toto');
        $this->assertEquals($conf->get('section3', 'key1'), 'tata');
    }
}

