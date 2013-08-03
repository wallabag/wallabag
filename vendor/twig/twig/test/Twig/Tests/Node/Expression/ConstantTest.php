<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_ConstantTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_Constant::__construct
     */
    public function testConstructor()
    {
        $node = new Twig_Node_Expression_Constant('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('value'));
    }

    /**
     * @covers Twig_Node_Expression_Constant::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $tests = array();

        $node = new Twig_Node_Expression_Constant('foo', 1);
        $tests[] = array($node, '"foo"');

        return $tests;
    }
}
