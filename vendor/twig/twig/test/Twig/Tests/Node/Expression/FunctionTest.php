<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_FunctionTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_Function::__construct
     */
    public function testConstructor()
    {
        $name = 'function';
        $args = new Twig_Node();
        $node = new Twig_Node_Expression_Function($name, $args, 1);

        $this->assertEquals($name, $node->getAttribute('name'));
        $this->assertEquals($args, $node->getNode('arguments'));
    }

    /**
     * @covers Twig_Node_Expression_Function::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $environment = new Twig_Environment();
        $environment->addFunction('foo', new Twig_Function_Function('foo', array()));
        $environment->addFunction('bar', new Twig_Function_Function('bar', array('needs_environment' => true)));
        $environment->addFunction('foofoo', new Twig_Function_Function('foofoo', array('needs_context' => true)));
        $environment->addFunction('foobar', new Twig_Function_Function('foobar', array('needs_environment' => true, 'needs_context' => true)));

        $tests = array();

        $node = $this->createFunction('foo');
        $tests[] = array($node, 'foo()', $environment);

        $node = $this->createFunction('foo', array(new Twig_Node_Expression_Constant('bar', 1), new Twig_Node_Expression_Constant('foobar', 1)));
        $tests[] = array($node, 'foo("bar", "foobar")', $environment);

        $node = $this->createFunction('bar');
        $tests[] = array($node, 'bar($this->env)', $environment);

        $node = $this->createFunction('bar', array(new Twig_Node_Expression_Constant('bar', 1)));
        $tests[] = array($node, 'bar($this->env, "bar")', $environment);

        $node = $this->createFunction('foofoo');
        $tests[] = array($node, 'foofoo($context)', $environment);

        $node = $this->createFunction('foofoo', array(new Twig_Node_Expression_Constant('bar', 1)));
        $tests[] = array($node, 'foofoo($context, "bar")', $environment);

        $node = $this->createFunction('foobar');
        $tests[] = array($node, 'foobar($this->env, $context)', $environment);

        $node = $this->createFunction('foobar', array(new Twig_Node_Expression_Constant('bar', 1)));
        $tests[] = array($node, 'foobar($this->env, $context, "bar")', $environment);

        // named arguments
        $node = $this->createFunction('date', array(
            'timezone' => new Twig_Node_Expression_Constant('America/Chicago', 1),
            'date'     => new Twig_Node_Expression_Constant(0, 1),
        ));
        $tests[] = array($node, 'twig_date_converter($this->env, 0, "America/Chicago")');

        // function as an anonymous function
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            $node = $this->createFunction('anonymous', array(new Twig_Node_Expression_Constant('foo', 1)));
            $tests[] = array($node, 'call_user_func_array($this->env->getFunction(\'anonymous\')->getCallable(), array("foo"))');
        }

        return $tests;
    }

    protected function createFunction($name, array $arguments = array())
    {
        return new Twig_Node_Expression_Function($name, new Twig_Node($arguments), 1);
    }

    protected function getEnvironment()
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            return include 'PHP53/FunctionInclude.php';
        }

        return parent::getEnvironment();
    }
}
