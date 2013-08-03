<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_SandboxedModuleTest extends Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_SandboxedModule::__construct
     */
    public function testConstructor()
    {
        $body = new Twig_Node_Text('foo', 1);
        $parent = new Twig_Node_Expression_Constant('layout.twig', 1);
        $blocks = new Twig_Node();
        $macros = new Twig_Node();
        $traits = new Twig_Node();
        $filename = 'foo.twig';
        $node = new Twig_Node_Module($body, $parent, $blocks, $macros, $traits, new Twig_Node(array()), $filename);
        $node = new Twig_Node_SandboxedModule($node, array('for'), array('upper'), array('cycle'));

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($blocks, $node->getNode('blocks'));
        $this->assertEquals($macros, $node->getNode('macros'));
        $this->assertEquals($parent, $node->getNode('parent'));
        $this->assertEquals($filename, $node->getAttribute('filename'));
    }

    /**
     * @covers Twig_Node_SandboxedModule::compile
     * @covers Twig_Node_SandboxedModule::compileDisplayBody
     * @covers Twig_Node_SandboxedModule::compileDisplayFooter
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        $twig = new Twig_Environment(new Twig_Loader_String());

        $tests = array();

        $body = new Twig_Node_Text('foo', 1);
        $extends = null;
        $blocks = new Twig_Node();
        $macros = new Twig_Node();
        $traits = new Twig_Node();
        $filename = 'foo.twig';

        $node = new Twig_Node_Module($body, $extends, $blocks, $macros, $traits, new Twig_Node(array()), $filename);
        $node = new Twig_Node_SandboxedModule($node, array('for'), array('upper'), array('cycle'));

        $tests[] = array($node, <<<EOF
<?php

/* foo.twig */
class __TwigTemplate_be925a7b06dda0dfdbd18a1509f7eb34 extends Twig_Template
{
    public function __construct(Twig_Environment \$env)
    {
        parent::__construct(\$env);

        \$this->parent = false;

        \$this->blocks = array(
        );
    }

    protected function doDisplay(array \$context, array \$blocks = array())
    {
        \$this->checkSecurity();
        // line 1
        echo "foo";
    }

    protected function checkSecurity()
    {
        \$this->env->getExtension('sandbox')->checkSecurity(
            array('upper'),
            array('for'),
            array('cycle')
        );
    }

    public function getTemplateName()
    {
        return "foo.twig";
    }

    public function getDebugInfo()
    {
        return array (  20 => 1,);
    }
}
EOF
        , $twig);

        $body = new Twig_Node();
        $extends = new Twig_Node_Expression_Constant('layout.twig', 1);
        $blocks = new Twig_Node();
        $macros = new Twig_Node();
        $traits = new Twig_Node();
        $filename = 'foo.twig';

        $node = new Twig_Node_Module($body, $extends, $blocks, $macros, $traits, new Twig_Node(array()), $filename);
        $node = new Twig_Node_SandboxedModule($node, array('for'), array('upper'), array('cycle'));

        $tests[] = array($node, <<<EOF
<?php

/* foo.twig */
class __TwigTemplate_be925a7b06dda0dfdbd18a1509f7eb34 extends Twig_Template
{
    public function __construct(Twig_Environment \$env)
    {
        parent::__construct(\$env);

        \$this->parent = \$this->env->loadTemplate("layout.twig");

        \$this->blocks = array(
        );
    }

    protected function doGetParent(array \$context)
    {
        return "layout.twig";
    }

    protected function doDisplay(array \$context, array \$blocks = array())
    {
        \$this->checkSecurity();
        \$this->parent->display(\$context, array_merge(\$this->blocks, \$blocks));
    }

    protected function checkSecurity()
    {
        \$this->env->getExtension('sandbox')->checkSecurity(
            array('upper'),
            array('for'),
            array('cycle')
        );
    }

    public function getTemplateName()
    {
        return "foo.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array ();
    }
}
EOF
        , $twig);

        return $tests;
    }
}
