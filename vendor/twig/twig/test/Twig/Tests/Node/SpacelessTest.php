<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_SpacelessTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_Spaceless::__construct
     */
    public function testConstructor()
    {
        $body = new Twig_Node(array(new Twig_Node_Text('<div>   <div>   foo   </div>   </div>', 1)));
        $node = new Twig_Node_Spaceless($body, 1);

        $this->assertEquals($body, $node->getNode('body'));
    }

    /**
     * @covers Twig_Node_Spaceless::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $body = new Twig_Node(array(new Twig_Node_Text('<div>   <div>   foo   </div>   </div>', 1)));
        $node = new Twig_Node_Spaceless($body, 1);

        return array(
            array($node, <<<EOF
// line 1
ob_start();
echo "<div>   <div>   foo   </div>   </div>";
echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
EOF
            ),
        );
    }
}
