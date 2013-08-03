<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_MacroTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Macro::__construct
     */
    public function testConstructor()
    {
        $body = new Twig_Node_Text('foo', 1);
        $arguments = new Twig_Node(array(new Twig_Node_Expression_Name('foo', 1)), array(), 1);
        $node = new Twig_Node_Macro('foo', $body, $arguments, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($arguments, $node->getNode('arguments'));
        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    /**
     * @covers Twig_Node_Macro::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $body = new Twig_Node_Text('foo', 1);
        $arguments = new Twig_Node(array(
            'foo' => new Twig_Node_Expression_Constant(null, 1),
            'bar' => new Twig_Node_Expression_Constant('Foo', 1),
        ), array(), 1);
        $node = new Twig_Node_Macro('foo', $body, $arguments, 1);

        return array(
            array($node, <<<EOF
// line 1
public function getfoo(\$_foo = null, \$_bar = "Foo")
{
    \$context = \$this->env->mergeGlobals(array(
        "foo" => \$_foo,
        "bar" => \$_bar,
    ));

    \$blocks = array();

    ob_start();
    try {
        echo "foo";
    } catch (Exception \$e) {
        ob_end_clean();

        throw \$e;
    }

    return ('' === \$tmp = ob_get_clean()) ? '' : new Twig_Markup(\$tmp, \$this->env->getCharset());
}
EOF
            ),
        );
    }
}
