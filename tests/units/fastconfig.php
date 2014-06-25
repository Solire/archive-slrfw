<?php
/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw;

use atoum;
use Slrfw\FastConfig as TestClass;

/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class FastConfig extends atoum
{
    /**
     * Contrôle construct
     *
     * @return void
     */
    public function testConstruct()
    {
        $this
            ->object(new TestClass())
                ->isInstanceOf('\Slrfw\FastConfig')

        ;
    }

    /**
     * Contrôle des getters & setters
     *
     * @return void
     */
    public function testSetters()
    {
        $this
            ->if($conf = new TestClass())
            ->object($conf->set(1, 'section1', 'name1'))
                ->isIdenticalTo($conf)
            ->integer($conf->get('section1', 'name1'))
                ->isEqualTo(1)
            ->object($conf->set(3, 'section1'))
                ->isIdenticalTo($conf)
            ->integer($conf->get('section1'))
                ->isEqualTo(3)
            ->object($conf->set('toto', 'section2'))
                ->isIdenticalTo($conf)
            ->string($conf->get('section2'))
                ->isEqualTo('toto')
            ->array($conf->getAll())
                ->isEqualTo(['section1' => 3, 'section2' => 'toto'])
        ;
    }

    /**
     * Contrôle supression d'un élément
     *
     * @return void
     */
    public function testKill()
    {
        $this
            ->if($conf = new TestClass())
            ->and($conf->set(1, 'section1', 'name1'))
            ->and($conf->set(2, 'section1', 'name2'))
            ->and($conf->set('toto', 'stringSection'))
            ->object($conf->kill('section1', 'name1'))
                ->isIdenticalTo($conf)
            ->variable($conf->get('section1', 'name1'))
                ->isNull()
            ->integer($conf->get('section1', 'name2'))
                ->isEqualTo(2)
            ->array($conf->getAll())
                ->isEqualTo(['section1' => ['name2' => 2], 'stringSection' => 'toto'])
        ;
    }
}
