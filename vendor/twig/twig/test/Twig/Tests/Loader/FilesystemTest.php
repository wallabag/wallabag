<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Loader_FilesystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSecurityTests
     */
    public function testSecurity($template)
    {
        $loader = new Twig_Loader_Filesystem(array(dirname(__FILE__).'/../Fixtures'));

        try {
            $loader->getCacheKey($template);
            $this->fail();
        } catch (Twig_Error_Loader $e) {
            $this->assertNotContains('Unable to find template', $e->getMessage());
        }
    }

    public function getSecurityTests()
    {
        return array(
            array("AutoloaderTest\0.php"),
            array('..\\AutoloaderTest.php'),
            array('..\\\\\\AutoloaderTest.php'),
            array('../AutoloaderTest.php'),
            array('..////AutoloaderTest.php'),
            array('./../AutoloaderTest.php'),
            array('.\\..\\AutoloaderTest.php'),
            array('././././././../AutoloaderTest.php'),
            array('.\\./.\\./.\\./../AutoloaderTest.php'),
            array('foo/../../AutoloaderTest.php'),
            array('foo\\..\\..\\AutoloaderTest.php'),
            array('foo/../bar/../../AutoloaderTest.php'),
            array('foo/bar/../../../AutoloaderTest.php'),
            array('filters/../../AutoloaderTest.php'),
            array('filters//..//..//AutoloaderTest.php'),
            array('filters\\..\\..\\AutoloaderTest.php'),
            array('filters\\\\..\\\\..\\\\AutoloaderTest.php'),
            array('filters\\//../\\/\\..\\AutoloaderTest.php'),
            array('/../AutoloaderTest.php'),
        );
    }

    public function testPaths()
    {
        $basePath = dirname(__FILE__).'/Fixtures';

        $loader = new Twig_Loader_Filesystem(array($basePath.'/normal', $basePath.'/normal_bis'));
        $loader->setPaths(array($basePath.'/named', $basePath.'/named_bis'), 'named');
        $loader->addPath($basePath.'/named_ter', 'named');
        $loader->addPath($basePath.'/normal_ter');
        $loader->prependPath($basePath.'/normal_final');
        $loader->prependPath($basePath.'/named_final', 'named');

        $this->assertEquals(array(
            $basePath.'/normal_final',
            $basePath.'/normal',
            $basePath.'/normal_bis',
            $basePath.'/normal_ter',
        ), $loader->getPaths());
        $this->assertEquals(array(
            $basePath.'/named_final',
            $basePath.'/named',
            $basePath.'/named_bis',
            $basePath.'/named_ter',
        ), $loader->getPaths('named'));

        $this->assertEquals("path (final)\n", $loader->getSource('index.html'));
        $this->assertEquals("path (final)\n", $loader->getSource('@__main__/index.html'));
        $this->assertEquals("named path (final)\n", $loader->getSource('@named/index.html'));
    }

    public function testEmptyConstructor()
    {
        $loader = new Twig_Loader_Filesystem();
        $this->assertEquals(array(), $loader->getPaths());
    }

    public function testGetNamespaces()
    {
        $loader = new Twig_Loader_Filesystem(sys_get_temp_dir());
        $this->assertEquals(array(Twig_Loader_Filesystem::MAIN_NAMESPACE), $loader->getNamespaces());

        $loader->addPath(sys_get_temp_dir(), 'named');
        $this->assertEquals(array(Twig_Loader_Filesystem::MAIN_NAMESPACE, 'named'), $loader->getNamespaces());
    }
}
