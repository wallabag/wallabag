<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_IfTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_If::__construct
     */
    public function testConstructor()
    {
        $t = new Twig_Node(array(
            new Twig_Node_Expression_Constant(true, 1),
            new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1),
        ), array(), 1);
        $else = null;
        $node = new Twig_Node_If($t, $else, 1);

        $this->assertEquals($t, $node->getNode('tests'));
        $this->assertEquals(null, $node->getNode('else'));

        $else = new Twig_Node_Print(new Twig_Node_Expression_Name('bar', 1), 1);
        $node = new Twig_Node_If($t, $else, 1);
        $this->assertEquals($else, $node->getNode('else'));
    }

    /**
     * @covers Twig_Node_If::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $tests = array();

        $t = new Twig_Node(array(
            new Twig_Node_Expression_Constant(true, 1),
            new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1),
        ), array(), 1);
        $else = null;
        $node = new Twig_Node_If($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
}
EOF
        );

        $t = new Twig_Node(array(
            new Twig_Node_Expression_Constant(true, 1),
            new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1),
            new Twig_Node_Expression_Constant(false, 1),
            new Twig_Node_Print(new Twig_Node_Expression_Name('bar', 1), 1),
        ), array(), 1);
        $else = null;
        $node = new Twig_Node_If($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
} elseif (false) {
    echo {$this->getVariableGetter('bar')};
}
EOF
        );

        $t = new Twig_Node(array(
            new Twig_Node_Expression_Constant(true, 1),
            new Twig_Node_Print(new Twig_Node_Expression_Name('foo', 1), 1),
        ), array(), 1);
        $else = new Twig_Node_Print(new Twig_Node_Expression_Name('bar', 1), 1);
        $node = new Twig_Node_If($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
} else {
    echo {$this->getVariableGetter('bar')};
}
EOF
        );

        return $tests;
    }
}
