<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_GetAttrTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_GetAttr::__construct
     */
    public function testConstructor()
    {
        $expr = new Twig_Node_Expression_Name('foo', 1);
        $attr = new Twig_Node_Expression_Constant('bar', 1);
        $args = new Twig_Node_Expression_Array(array(), 1);
        $args->addElement(new Twig_Node_Expression_Name('foo', 1));
        $args->addElement(new Twig_Node_Expression_Constant('bar', 1));
        $node = new Twig_Node_Expression_GetAttr($expr, $attr, $args, Twig_TemplateInterface::ARRAY_CALL, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($attr, $node->getNode('attribute'));
        $this->assertEquals($args, $node->getNode('arguments'));
        $this->assertEquals(Twig_TemplateInterface::ARRAY_CALL, $node->getAttribute('type'));
    }

    /**
     * @covers Twig_Node_Expression_GetAttr::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $tests = array();

        $expr = new Twig_Node_Expression_Name('foo', 1);
        $attr = new Twig_Node_Expression_Constant('bar', 1);
        $args = new Twig_Node_Expression_Array(array(), 1);
        $node = new Twig_Node_Expression_GetAttr($expr, $attr, $args, Twig_TemplateInterface::ANY_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar")', $this->getAttributeGetter(), $this->getVariableGetter('foo')));

        $node = new Twig_Node_Expression_GetAttr($expr, $attr, $args, Twig_TemplateInterface::ARRAY_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar", array(), "array")', $this->getAttributeGetter(), $this->getVariableGetter('foo')));

        $args = new Twig_Node_Expression_Array(array(), 1);
        $args->addElement(new Twig_Node_Expression_Name('foo', 1));
        $args->addElement(new Twig_Node_Expression_Constant('bar', 1));
        $node = new Twig_Node_Expression_GetAttr($expr, $attr, $args, Twig_TemplateInterface::METHOD_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar", array(0 => %s, 1 => "bar"), "method")', $this->getAttributeGetter(), $this->getVariableGetter('foo'), $this->getVariableGetter('foo')));

        return $tests;
    }
}
