<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Twig_Tests_Node_Expression_CallTest extends PHPUnit_Framework_TestCase
{
    public function testGetArguments()
    {
        $node = new Twig_Tests_Node_Expression_Call(array(), array('type' => 'function', 'name' => 'date'));
        $this->assertEquals(array('U'), $node->getArguments('date', array('format' => 'U')));
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Positional arguments cannot be used after named arguments for function "date".
     */
    public function testGetArgumentsWhenPositionalArgumentsAfterNamedArguments()
    {
        $node = new Twig_Tests_Node_Expression_Call(array(), array('type' => 'function', 'name' => 'date'));
        $node->getArguments('date', array('timestamp' => 123456, 'Y-m-d'));
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Argument "format" is defined twice for function "date".
     */
    public function testGetArgumentsWhenArgumentIsDefinedTwice()
    {
        $node = new Twig_Tests_Node_Expression_Call(array(), array('type' => 'function', 'name' => 'date'));
        $node->getArguments('date', array('Y-m-d', 'format' => 'U'));
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Unknown argument "unknown" for function "date".
     */
    public function testGetArgumentsWithWrongNamedArgumentName()
    {
        $node = new Twig_Tests_Node_Expression_Call(array(), array('type' => 'function', 'name' => 'date'));
        $node->getArguments('date', array('Y-m-d', 'unknown' => ''));
    }

    /**
     * @expectedException        Twig_Error_Syntax
     * @expectedExceptionMessage Unknown arguments "unknown1", "unknown2" for function "date".
     */
    public function testGetArgumentsWithWrongNamedArgumentNames()
    {
        $node = new Twig_Tests_Node_Expression_Call(array(), array('type' => 'function', 'name' => 'date'));
        $node->getArguments('date', array('Y-m-d', 'unknown1' => '', 'unknown2' => ''));
    }
}

class Twig_Tests_Node_Expression_Call extends Twig_Node_Expression_Call
{
    public function getArguments($callable, $arguments)
    {
        return parent::getArguments($callable, $arguments);
    }
}
