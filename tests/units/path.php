<?php
/**
 * Test class for path.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/ 
 */

namespace tests\unit\Slrfw;

use atoum;

/**
 * Test class for path.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Path extends atoum
{
    /**
     * Contrôle ajout de dossiers dans l'include_path
     *
     * @return void
     */
    public function testAddPath()
    {
        $this
            ->boolean(\Slrfw\Path::addPath(__DIR__))
                ->isTrue()
            ->string(get_include_path())
                ->contains(PATH_SEPARATOR . __DIR__)
            ->boolean(\Slrfw\Path::addPath(__DIR__))
                ->isTrue()
            ->exception(function () {
                \Slrfw\Path::addPath('skldfjghsdlkfjghzieb');
            })
                ->hasMessage('Fichier introuvable : skldfjghsdlkfjghzieb')
                ->isInstanceOf('\Slrfw\Exception\Lib')
            ->if(touch(TEST_TMP_DIR . 'toto.txt'))
            ->boolean(\Slrfw\Path::addPath(TEST_TMP_DIR . 'toto.txt'))
                ->isTrue()
            ->and(unlink(TEST_TMP_DIR . 'toto.txt'))
            ->exception(function () {
                \Slrfw\Path::addPath(TEST_TMP_DIR . 'toto.txt');
            })
                ->hasMessage('Fichier introuvable : ' . TEST_TMP_DIR . 'toto.txt')
                ->isInstanceOf('\Slrfw\Exception\Lib')

        ;
    }

    /**
     * Contrôle test présence fichier
     *
     * @return void
     */
    public function testConstruct()
    {
        $this
            ->exception(function () {
                $path = new \Slrfw\Path('sdjfsl');
            })
                ->hasMessage('Fichier introuvable : sdjfsl')
                ->isInstanceOf('\Slrfw\Exception\Lib')
            ->if($path = new \Slrfw\Path(__FILE__))
            ->string($path->get())
                ->isEqualTo(__FILE__)
            ->if($path = new \Slrfw\Path(__DIR__))
            ->string($path->get())
                ->isEqualTo(__DIR__ . DIRECTORY_SEPARATOR)
            ->if($path = new \Slrfw\Path('sdjfsl', \Slrfw\Path::SILENT))
            ->boolean($path->get())
                ->isFalse()
        ;
    }

    /**
     * Contrôle convertion chaine de la classe
     *
     * @return void
     */
    public function testToString()
    {
        $this
            ->if($path = new \Slrfw\Path(__FILE__))
            ->string((string) $path)
                ->isEqualTo(__FILE__)
        ;
    }
}
