<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_NameTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Expression_Name::__construct
     */
    public function testConstructor()
    {
        $node = new Twig_Node_Expression_Name('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    /**
     * @covers Twig_Node_Expression_Name::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $node = new Twig_Node_Expression_Name('foo', 1);
        $self = new Twig_Node_Expression_Name('_self', 1);
        $context = new Twig_Node_Expression_Name('_context', 1);

        $env = new Twig_Environment(null, array('strict_variables' => true));
        $env1 = new Twig_Environment(null, array('strict_variables' => false));

        return array(
            version_compare(PHP_VERSION, '5.4.0') >= 0 ? array($node, '(isset($context["foo"]) ? $context["foo"] : $this->getContext($context, "foo"))', $env) : array($node, '$this->getContext($context, "foo")', $env),
            array($node, $this->getVariableGetter('foo'), $env1),
            array($self, '$this'),
            array($context, '$context'),
        );
    }
}
