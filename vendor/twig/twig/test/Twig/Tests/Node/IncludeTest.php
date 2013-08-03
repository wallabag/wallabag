<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_IncludeTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Include::__construct
     */
    public function testConstructor()
    {
        $expr = new Twig_Node_Expression_Constant('foo.twig', 1);
        $node = new Twig_Node_Include($expr, null, false, false, 1);

        $this->assertEquals(null, $node->getNode('variables'));
        $this->assertEquals($expr, $node->getNode('expr'));
        $this->assertFalse($node->getAttribute('only'));

        $vars = new Twig_Node_Expression_Array(array(new Twig_Node_Expression_Constant('foo', 1), new Twig_Node_Expression_Constant(true, 1)), 1);
        $node = new Twig_Node_Include($expr, $vars, true, false, 1);
        $this->assertEquals($vars, $node->getNode('variables'));
        $this->assertTrue($node->getAttribute('only'));
    }

    /**
     * @covers Twig_Node_Include::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $tests = array();

        $expr = new Twig_Node_Expression_Constant('foo.twig', 1);
        $node = new Twig_Node_Include($expr, null, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->env->loadTemplate("foo.twig")->display(\$context);
EOF
        );

        $expr = new Twig_Node_Expression_Conditional(
                        new Twig_Node_Expression_Constant(true, 1),
                        new Twig_Node_Expression_Constant('foo', 1),
                        new Twig_Node_Expression_Constant('foo', 1),
                        0
                    );
        $node = new Twig_Node_Include($expr, null, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$template = \$this->env->resolveTemplate(((true) ? ("foo") : ("foo")));
\$template->display(\$context);
EOF
        );

        $expr = new Twig_Node_Expression_Constant('foo.twig', 1);
        $vars = new Twig_Node_Expression_Array(array(new Twig_Node_Expression_Constant('foo', 1), new Twig_Node_Expression_Constant(true, 1)), 1);
        $node = new Twig_Node_Include($expr, $vars, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->env->loadTemplate("foo.twig")->display(array_merge(\$context, array("foo" => true)));
EOF
        );

        $node = new Twig_Node_Include($expr, $vars, true, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->env->loadTemplate("foo.twig")->display(array("foo" => true));
EOF
        );

        $node = new Twig_Node_Include($expr, $vars, true, true, 1);
        $tests[] = array($node, <<<EOF
// line 1
try {
    \$this->env->loadTemplate("foo.twig")->display(array("foo" => true));
} catch (Twig_Error_Loader \$e) {
    // ignore missing template
}
EOF
        );

        return $tests;
    }
}
