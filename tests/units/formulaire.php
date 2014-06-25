<?php
/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Slrfw;

use atoum;
use Slrfw\Formulaire as TestClass;

/**
 * Test class for formulaire.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Formulaire extends atoum
{

    /**
     * Renvois une configuration simple
     *
     * @return \Slrfw\Formulaire\FastConfig
     */
    protected function getConfigTest()
    {
        $conf = new \Slrfw\Formulaire\FastConfig();
        $conf
            ->create('id')
                ->setRule('obligatoire', false)
                ->setRule('test', 'notEmpty|isInt|isPositive')
                ->setRule('erreur', 'Erreur interne')
            ->create('nom')
                ->setRule('obligatoire', true)
                ->setRule('test', ['notEmpty', 'isString'])
                ->setRule('erreur', 'Erreur saisie')
        ;

        return $conf;
    }


    /**
     * Contrôle chargement de la configuration
     *
     * @return void
     */
    public function testConstruct()
    {
        $this
            ->exception(function () {
                $foo = new TestClass(null);
            })
                ->hasMessage('Configuration non valide')
                ->isInstanceOf('\Slrfw\Exception\Lib')
            ->exception(function () {
                $foo = new TestClass([]);
            })
                ->hasMessage('Configuration non valide')
                ->isInstanceOf('\Slrfw\Exception\Lib')
            ->object(new TestClass($this->getConfigTest()))
                ->isInstanceOf('\Slrfw\Formulaire')
        ;
    }

    /**
     * Contrôle récupération des variable POST, GET, COOKIE
     *
     * @return void
     */
    public function testHttpDispatcher()
    {
        $this
            ->if ($conf = $this->getConfigTest())
            ->and($conf->set('g', '__global', 'ordre'))
            ->and($_GET = ['nom' => 'fuuu', 'id' => 'HACK'])
            ->and($_POST = ['nom' => 'fuuuPOST', 'id' => 5])
            ->and($_COOKIE = ['nom' => 'fuuuCOOK', 'id' => 35])
            ->and($form = new TestClass($conf))
            ->array($form->run())
                ->isEqualTo(['nom' => 'fuuu'])
            ->if($conf->set('cpg', '__global', 'ordre'))
            ->array($form->run())
                ->isEqualTo(['nom' => 'fuuu'])
            ->if($conf->set('gp', '__global', 'ordre'))
            ->array($form->run())
                ->isEqualTo(['nom' => 'fuuuPOST', 'id' => 5])
            ->if($conf->set('gpc', '__global', 'ordre'))
            ->array($form->run())
                ->isEqualTo(['nom' => 'fuuuCOOK', 'id' => 35])
        ;
    }

    /**
     * Contrôles des differents getter de Formulaire
     *
     * @return void
     */
    public function testGetAndSet()
    {
        $this
            ->if ($conf = $this->getConfigTest())
            ->and($conf->set('g', '__global', 'ordre'))
            ->and($_GET = ['nom' => 'fuuu', 'id' => '8'])
            ->and($form = new TestClass($conf))
            ->array($form->run())
                ->isEqualTo(['id' => 8, 'nom' => 'fuuu'])
            ->array($form->getArray())
                ->isEqualTo(['id' => 8, 'nom' => 'fuuu'])
            ->string($form->id)
                ->isEqualTo(8)
            ->string($form->nom)
                ->isEqualTo('fuuu')
            ->boolean(isset($form->nom))
                ->isTrue()
            ->boolean(isset($form->nombre))
                ->isFalse()
            ->array($form->getList())
                ->isEqualTo(['8', 'fuuu'])

            ->object($form->getConfig())
                ->isIdenticalTo($conf)
        ;
    }

    /**
     * Contrôle bon traitement des champs obligatoires
     *
     * @return void
     */
    public function testObligatoire()
    {
        $this
            ->if ($conf = $this->getConfigTest())
            ->and($conf->set('g', '__global', 'ordre'))
            ->and($_GET = ['nom' => 'fuuu', 'id' => 'HACK'])
            ->and($form = new TestClass($conf))
            ->array($form->run())
                ->isEqualTo(['nom' => 'fuuu'])
            ->if($conf->set(true, 'id', 'obligatoire'))
            ->exception(function () use ($form) {
                $form->run();
            })
                ->hasMessage('Erreur interne')
                ->isInstanceOf('\Slrfw\Exception\Lib')
        ;
    }
}
