<?php
/**
 * Test class for IsBoolean.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw\Formulaire\Validate;

use atoum;
use Slrfw\Formulaire\Validate\IsBoolean as TestClass;

/**
 * Test class for IsBoolean.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class IsBoolean extends atoum
{
    /**
     * Contrôle ajout de dossiers dans l'include_path
     *
     * @return void
     */
    public function testCtrl()
    {
        $this
            ->boolean(TestClass::test(true))
                ->isTrue()
            ->boolean(TestClass::test(false))
                ->isTrue()
            ->boolean(TestClass::test(1))
                ->isTrue()
            ->boolean(TestClass::test('true'))
                ->isTrue()
            ->boolean(TestClass::test('false'))
                ->isTrue()
        ;
    }
}
