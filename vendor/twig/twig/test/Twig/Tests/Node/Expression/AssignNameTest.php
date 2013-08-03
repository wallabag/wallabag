<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_AssignNameTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_AssignName::__construct
     */
    public function testConstructor()
    {
        $node = new Twig_Node_Expression_AssignName('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    /**
     * @covers Twig_Node_Expression_AssignName::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $node = new Twig_Node_Expression_AssignName('foo', 1);

        return array(
            array($node, '$context["foo"]'),
        );
    }
}
