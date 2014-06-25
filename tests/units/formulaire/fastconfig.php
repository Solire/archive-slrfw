<?php
/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw\Formulaire;

use atoum;
use Slrfw\Formulaire\FastConfig as TestClass;

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
                ->isInstanceOf('\Slrfw\Formulaire\FastConfig')
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

    /**
     * Test d'utilisation du process de création d'une règle formulaire
     *
     * @return void
     */
    public function testCreate()
    {
        $this
            ->if($conf = new TestClass())
            ->object($conf->create('id'))
                ->isIdenticalTo($conf)
            ->array($conf->getAll())
                ->isEqualTo(['id' => ['test' => '', 'obligatoire' => false]])
            ->object($conf->setRule('test', 'isInt|notEmpty'))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('obligatoire', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('erreur', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('renomme', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('designe', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('exception', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('force', true))
                ->isIdenticalTo($conf)
            ->object($conf->setRule('egale', true))
                ->isIdenticalTo($conf)
            ->exception(function () use ($conf) {
                $conf->setRule('maRègleFoireuse', -1);
            })
                ->hasMessage('maRègleFoireuse n\'est pas une règle formulaire')
                ->isInstanceOf('\Slrfw\Exception\Lib')
        ;
    }
}
