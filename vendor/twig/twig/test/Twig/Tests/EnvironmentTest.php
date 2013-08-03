<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_EnvironmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage You must set a loader first.
     */
    public function testRenderNoLoader()
    {
        $env = new Twig_Environment();
        $env->render('test');
    }

    public function testAutoescapeOption()
    {
        $loader = new Twig_Loader_Array(array(
            'html' => '{{ foo }} {{ foo }}',
            'js'   => '{{ bar }} {{ bar }}',
        ));

        $twig = new Twig_Environment($loader, array(
            'debug'      => true,
            'cache'      => false,
            'autoescape' => array($this, 'escapingStrategyCallback'),
        ));

        $this->assertEquals('foo&lt;br/ &gt; foo&lt;br/ &gt;', $twig->render('html', array('foo' => 'foo<br/ >')));
        $this->assertEquals('foo\x3Cbr\x2F\x20\x3E foo\x3Cbr\x2F\x20\x3E', $twig->render('js', array('bar' => 'foo<br/ >')));
    }

    public function escapingStrategyCallback($filename)
    {
        return $filename;
    }

    public function testGlobals()
    {
        // globals can be added after calling getGlobals
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->addGlobal('foo', 'bar');
        $globals = $twig->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after runtime init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->initRuntime();
        $twig->addGlobal('foo', 'bar');
        $globals = $twig->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after extensions init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->getFunctions();
        $twig->addGlobal('foo', 'bar');
        $globals = $twig->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after extensions and runtime init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->getFunctions();
        $twig->initRuntime();
        $twig->addGlobal('foo', 'bar');
        $globals = $twig->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->getGlobals();
        $twig->addGlobal('foo', 'bar');
        $template = $twig->loadTemplate('{{foo}}');
        $this->assertEquals('bar', $template->render(array()));

        /* to be uncomment in Twig 2.0
        // globals cannot be added after runtime init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->initRuntime();
        try {
            $twig->addGlobal('bar', 'bar');
            $this->fail();
        } catch (LogicException $e) {
            $this->assertFalse(array_key_exists('bar', $twig->getGlobals()));
        }

        // globals cannot be added after extensions init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->getFunctions();
        try {
            $twig->addGlobal('bar', 'bar');
            $this->fail();
        } catch (LogicException $e) {
            $this->assertFalse(array_key_exists('bar', $twig->getGlobals()));
        }

        // globals cannot be added after extensions and runtime init
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addGlobal('foo', 'foo');
        $globals = $twig->getGlobals();
        $twig->getFunctions();
        $twig->initRuntime();
        try {
            $twig->addGlobal('bar', 'bar');
            $this->fail();
        } catch (LogicException $e) {
            $this->assertFalse(array_key_exists('bar', $twig->getGlobals()));
        }

        // test adding globals after initRuntime without call to getGlobals
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->initRuntime();
        try {
            $twig->addGlobal('bar', 'bar');
            $this->fail();
        } catch (LogicException $e) {
            $this->assertFalse(array_key_exists('bar', $twig->getGlobals()));
        }
        */
    }

    public function testExtensionsAreNotInitializedWhenRenderingACompiledTemplate()
    {
        $options = array('cache' => sys_get_temp_dir().'/twig', 'auto_reload' => false, 'debug' => false);

        // force compilation
        $twig = new Twig_Environment(new Twig_Loader_String(), $options);
        $cache = $twig->getCacheFilename('{{ foo }}');
        if (!is_dir(dirname($cache))) {
            mkdir(dirname($cache), 0777, true);
        }
        file_put_contents($cache, $twig->compileSource('{{ foo }}', '{{ foo }}'));

        // check that extensions won't be initialized when rendering a template that is already in the cache
        $twig = $this
            ->getMockBuilder('Twig_Environment')
            ->setConstructorArgs(array(new Twig_Loader_String(), $options))
            ->setMethods(array('initExtensions'))
            ->getMock()
        ;

        $twig->expects($this->never())->method('initExtensions');

        // render template
        $output = $twig->render('{{ foo }}', array('foo' => 'bar'));
        $this->assertEquals('bar', $output);

        unlink($cache);
    }

    public function testAddExtension()
    {
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addExtension(new Twig_Tests_EnvironmentTest_Extension());

        $this->assertArrayHasKey('test', $twig->getTags());
        $this->assertArrayHasKey('foo_filter', $twig->getFilters());
        $this->assertArrayHasKey('foo_function', $twig->getFunctions());
        $this->assertArrayHasKey('foo_test', $twig->getTests());
        $this->assertArrayHasKey('foo_unary', $twig->getUnaryOperators());
        $this->assertArrayHasKey('foo_binary', $twig->getBinaryOperators());
        $this->assertArrayHasKey('foo_global', $twig->getGlobals());
        $visitors = $twig->getNodeVisitors();
        $this->assertEquals('Twig_Tests_EnvironmentTest_NodeVisitor', get_class($visitors[2]));
    }

    public function testRemoveExtension()
    {
        $twig = new Twig_Environment(new Twig_Loader_String());
        $twig->addExtension(new Twig_Tests_EnvironmentTest_Extension());
        $twig->removeExtension('environment_test');

        $this->assertFalse(array_key_exists('test', $twig->getTags()));
        $this->assertFalse(array_key_exists('foo_filter', $twig->getFilters()));
        $this->assertFalse(array_key_exists('foo_function', $twig->getFunctions()));
        $this->assertFalse(array_key_exists('foo_test', $twig->getTests()));
        $this->assertFalse(array_key_exists('foo_unary', $twig->getUnaryOperators()));
        $this->assertFalse(array_key_exists('foo_binary', $twig->getBinaryOperators()));
        $this->assertFalse(array_key_exists('foo_global', $twig->getGlobals()));
        $this->assertCount(2, $twig->getNodeVisitors());
    }
}

class Twig_Tests_EnvironmentTest_Extension extends Twig_Extension
{
    public function getTokenParsers()
    {
        return array(
            new Twig_Tests_EnvironmentTest_TokenParser(),
        );
    }

    public function getNodeVisitors()
    {
        return array(
            new Twig_Tests_EnvironmentTest_NodeVisitor(),
        );
    }

    public function getFilters()
    {
        return array(
            'foo_filter' => new Twig_Filter_Function('foo_filter'),
        );
    }

    public function getTests()
    {
        return array(
            'foo_test' => new Twig_Test_Function('foo_test'),
        );
    }

    public function getFunctions()
    {
        return array(
            'foo_function' => new Twig_Function_Function('foo_function'),
        );
    }

    public function getOperators()
    {
        return array(
            array('foo_unary' => array()),
            array('foo_binary' => array()),
        );
    }

    public function getGlobals()
    {
        return array(
            'foo_global' => 'foo_global',
        );
    }

    public function getName()
    {
        return 'environment_test';
    }
}

class Twig_Tests_EnvironmentTest_TokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
    }

    public function getTag()
    {
        return 'test';
    }
}

class Twig_Tests_EnvironmentTest_NodeVisitor implements Twig_NodeVisitorInterface
{
    public function enterNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        return $node;
    }

    public function leaveNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
