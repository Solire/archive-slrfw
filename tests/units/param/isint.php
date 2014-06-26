<?php
/**
 * Test class for IsInt.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw\Param;

use atoum;
use Slrfw\Param\IsInt as TestClass;

/**
 * Test class for IsInt.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class IsInt extends atoum
{
    /**
     * Contrôle ajout de dossiers dans l'include_path
     *
     * @return void
     */
    public function testCtrl()
    {
        $this
            ->boolean(TestClass::test(5))
                ->isTrue()
            ->boolean(TestClass::test(+15))
                ->isTrue()
            ->boolean(TestClass::test(-5365))
                ->isTrue()
            ->boolean(TestClass::test(5365.000))
                ->isTrue()
            ->boolean(TestClass::test('+564654'))
                ->isTrue()
            ->boolean(TestClass::test('8565'))
                ->isTrue()
            ->boolean(TestClass::test('-65489'))
                ->isTrue()
            ->boolean(TestClass::test(0.256))
                ->isFalse()
            ->boolean(TestClass::test('a523'))
                ->isFalse()
            ->boolean(TestClass::test('0.6'))
                ->isFalse()
            ->boolean(TestClass::test('-5648.5'))
                ->isFalse()
        ;
    }
}
