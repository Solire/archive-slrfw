<?php
/**
 *
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
 *
 *
 * @package    Library
 * @subpackage Test
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class MailTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Chargé avant le lancement des tests
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        /** Définition des répertoires app **/
        FrontController::setApp('tmp');
        FrontController::$appName = 'front';
        define('ID_VERSION', 1);
        define('ID_API', 1);

        mkdir(TMP_DIR . 'front');
        mkdir(TMP_DIR . 'front/mail');
        $data = <<<END
toto
END;
        file_put_contents(TMP_DIR . 'front/mail/test.phtml', $data);
        $data = <<<END
<?php echo \$this->foo; ?>
END;
        file_put_contents(TMP_DIR . 'front/mail/test2.phtml', $data);


        /** Fichier de configuration local contenant [mail] **/
        $data = <<<END
[mail]
bcc[] = dev@solire.fr
bcc[] = aimbert@solire.fr
from = aimbert@solire.fr

END;
        file_put_contents(TMP_DIR . 'mail.ini', $data);
        $config = new Config(TMP_DIR . 'mail.ini');
        Registry::set('envconfig', $config);

        /** Fichier main.ini **/
        $data = <<<END
[dirs]
mail = mail/
END;
        file_put_contents(TMP_DIR . 'main.ini', $data);
        $config = new Config(TMP_DIR . 'main.ini');
        Registry::set('mainconfig', $config);


        /** Récupération de la configuration locale pour charger la bdd **/
        $envConfig = new Config('config/local.ini');

        $db = DB::factory($envConfig->get('database'));
        Registry::set('db', $db);
    }

    /**
     * Exécuté après la fin de la série de test
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        unlink(TMP_DIR . 'mail.ini');
        unlink(TMP_DIR . 'main.ini');
        unlink(TMP_DIR . 'front/mail/test.phtml');
        unlink(TMP_DIR . 'front/mail/test2.phtml');
        rmdir(TMP_DIR . 'front/mail');
        rmdir(TMP_DIR . 'front');
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {

    }

    /**
     * Contrôle chargement informations par défaut du mail
     *
     * @return void
     */
    public function testConstruct()
    {
        $mail = new Mail('test');
        $bcc = array('dev@solire.fr', 'aimbert@solire.fr');
        $this->assertEquals($mail->bcc, $bcc);
        $this->assertEquals($mail->from, 'aimbert@solire.fr');
    }

    /**
     * Chargement de nouvelles variables
     *
     * @return void
     */
    public function testLoadVar()
    {
        $mail = new Mail('test');
        $bcc = array('dev@solire.fr', 'aimbert@solire.fr');
        $this->assertEquals($mail->bcc, $bcc);
        $mail->bcc = 'aimbert@solire.fr';
        $this->assertEquals($mail->bcc, 'aimbert@solire.fr');

        $mail->foo = 'bar';
        $this->assertEquals($mail->foo, 'bar');
    }

    /**
     * Chargement simple d'une vue
     *
     * @return void
     */
    public function testLoadBody()
    {
        $mail = new Mail('test');
        $this->assertEquals($mail->loadBody(), 'toto');
    }

    /**
     * Chargement simple d'une vue
     *
     * @return void
     */
    public function testLoadBodyAdvance()
    {
        $mail = new Mail('test2');
        $mail->foo = 'bar';
        $this->assertEquals($mail->loadBody(), 'bar');
    }

    /**
     * Envois d'un mail
     *
     * @return void
     */
    public function testSend()
    {
        $mail = new Mail('test2');
        $mail->foo = 'bar';
        $mail->to = 'dev@solire.fr';
        $mail->subject = 'Test d\'envois ';

        $mail->send();
    }
}

