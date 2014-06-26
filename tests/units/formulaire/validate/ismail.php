<?php
/**
 * Test class for IsMail.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw\Param;

use atoum;
use Slrfw\Param\IsMail as TestClass;

/**
 * Test class for IsMail.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class IsMail extends atoum
{
    /**
     * Contrôle ajout de dossiers dans l'include_path
     *
     * @return void
     */
    public function testCtrl()
    {
        $this
            ->boolean(TestClass::test('aimbert@solire.fr'))
                ->isTrue()
            ->boolean(TestClass::test('toto+test@free.fr'))
                ->isTrue()
            ->boolean(TestClass::test('53-zert@club-marine53.com'))
                ->isTrue()
            ->boolean(TestClass::test('aimbert@@solire.fr'))
                ->isFalse()
            ->boolean(TestClass::test(''))
                ->isFalse()
        ;
    }
}
