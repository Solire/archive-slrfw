<?php
/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw\Formulaire;

use atoum;
use Slrfw\Formulaire\Champ as TestClass;

/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Champ extends atoum
{
    protected function getChampTest()
    {
        $foo = new TestClass('id');
        $foo
            ->setRule('test', 'isInt|notEmpty')
            ->setRule('obligatoire', true)
            ->setRule('designe', 'id-table')
        ;

        return $foo;
    }

    /**
     * Contrôle construct
     *
     * @return void
     */
    public function testConstruct()
    {
        $this
            ->object(new TestClass('id'))
                ->isInstanceOf('\Slrfw\Formulaire\Champ')
        ;
    }

    public function testSetRule()
    {
        $this
            ->if($conf = new TestClass('id'))
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

    public function testGetTargetName()
    {
        $this
            ->if($field = $this->getChampTest())
            ->string($field->getTargetName())
                ->isEqualTo('id-table')
            ->if($field->rmRule('designe'))
            ->string($field->getTargetName())
                ->isEqualTo('id')
            ->if($field->setRule('designe', 'toto'))
            ->string($field->getTargetName())
                ->isEqualTo('toto')
        ;
    }

    public function testGetFinalName()
    {
        $this
            ->if($field = $this->getChampTest())
            ->string($field->getFinalName())
                ->isEqualTo('id')
            ->if($field->setRule('renomme', 'tata'))
            ->string($field->getFinalName())
                ->isEqualTo('tata')
        ;
    }

    public function testIsRequired()
    {
        $this
            ->if($field = $this->getChampTest())
            ->boolean($field->isRequired())
                ->isTrue()
            ->if($field->setRule('obligatoire', false))
            ->boolean($field->isRequired())
                ->isFalse()
        ;
    }

    public function testGetTests()
    {
        $this
            ->if($field = $this->getChampTest())
            ->array($field->getTests())
                ->isEqualTo(['isInt', 'notEmpty'])
            ->if($field->setRule('test', ['isMail', 'notEmpty']))
            ->array($field->getTests())
                ->isEqualTo(['isMail', 'notEmpty'])
        ;
    }
}
