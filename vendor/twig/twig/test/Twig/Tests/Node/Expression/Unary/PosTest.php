<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_Unary_PosTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_Unary_Pos::__construct
     */
    public function testConstructor()
    {
        $expr = new Twig_Node_Expression_Constant(1, 1);
        $node = new Twig_Node_Expression_Unary_Pos($expr, 1);

        $this->assertEquals($expr, $node->getNode('node'));
    }

    /**
     * @covers Twig_Node_Expression_Unary_Pos::compile
     * @covers Twig_Node_Expression_Unary_Pos::operator
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $node = new Twig_Node_Expression_Constant(1, 1);
        $node = new Twig_Node_Expression_Unary_Pos($node, 1);

        return array(
            array($node, '(+1)'),
        );
    }
}
