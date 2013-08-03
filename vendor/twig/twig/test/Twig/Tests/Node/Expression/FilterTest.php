<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_FilterTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_Filter::__construct
     */
    public function testConstructor()
    {
        $expr = new Twig_Node_Expression_Constant('foo', 1);
        $name = new Twig_Node_Expression_Constant('upper', 1);
        $args = new Twig_Node();
        $node = new Twig_Node_Expression_Filter($expr, $name, $args, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($name, $node->getNode('filter'));
        $this->assertEquals($args, $node->getNode('arguments'));
    }

    /**
     * @covers Twig_Node_Expression_Filter::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $tests = array();

        $expr = new Twig_Node_Expression_Constant('foo', 1);
        $node = $this->createFilter($expr, 'upper');
        $node = $this->createFilter($node, 'number_format', array(new Twig_Node_Expression_Constant(2, 1), new Twig_Node_Expression_Constant('.', 1), new Twig_Node_Expression_Constant(',', 1)));

        if (function_exists('mb_get_info')) {
            $tests[] = array($node, 'twig_number_format_filter($this->env, twig_upper_filter($this->env, "foo"), 2, ".", ",")');
        } else {
            $tests[] = array($node, 'twig_number_format_filter($this->env, strtoupper("foo"), 2, ".", ",")');
        }

        // named arguments
        $date = new Twig_Node_Expression_Constant(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'timezone' => new Twig_Node_Expression_Constant('America/Chicago', 1),
            'format'   => new Twig_Node_Expression_Constant('d/m/Y H:i:s P', 1),
        ));
        $tests[] = array($node, 'twig_date_format_filter($this->env, 0, "d/m/Y H:i:s P", "America/Chicago")');

        // skip an optional argument
        $date = new Twig_Node_Expression_Constant(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'timezone' => new Twig_Node_Expression_Constant('America/Chicago', 1),
        ));
        $tests[] = array($node, 'twig_date_format_filter($this->env, 0, null, "America/Chicago")');

        // underscores vs camelCase for named arguments
        $string = new Twig_Node_Expression_Constant('abc', 1);
        $node = $this->createFilter($string, 'reverse', array(
            'preserve_keys' => new Twig_Node_Expression_Constant(true, 1),
        ));
        $tests[] = array($node, 'twig_reverse_filter($this->env, "abc", true)');
        $node = $this->createFilter($string, 'reverse', array(
            'preserveKeys' => new Twig_Node_Expression_Constant(true, 1),
        ));
        $tests[] = array($node, 'twig_reverse_filter($this->env, "abc", true)');

        // filter as an anonymous function
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            $node = $this->createFilter(new Twig_Node_Expression_Constant('foo', 1), 'anonymous');
            $tests[] = array($node, 'call_user_func_array($this->env->getFilter(\'anonymous\')->getCallable(), array("foo"))');
        }

        return $tests;
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Unknown argument "foobar" for filter "date".
     */
    public function testCompileWithWrongNamedArgumentName()
    {
        $date = new Twig_Node_Expression_Constant(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'foobar' => new Twig_Node_Expression_Constant('America/Chicago', 1),
        ));

        $compiler = $this->getCompiler();
        $compiler->compile($node);
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Value for argument "from" is required for filter "replace".
     */
    public function testCompileWithMissingNamedArgument()
    {
        $value = new Twig_Node_Expression_Constant(0, 1);
        $node = $this->createFilter($value, 'replace', array(
            'to' => new Twig_Node_Expression_Constant('foo', 1),
        ));

        $compiler = $this->getCompiler();
        $compiler->compile($node);
    }

    protected function createFilter($node, $name, array $arguments = array())
    {
        $name = new Twig_Node_Expression_Constant($name, 1);
        $arguments = new Twig_Node($arguments);

        return new Twig_Node_Expression_Filter($node, $name, $arguments, 1);
    }

    protected function getEnvironment()
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            return include 'PHP53/FilterInclude.php';
        }

        return parent::getEnvironment();
    }
}
