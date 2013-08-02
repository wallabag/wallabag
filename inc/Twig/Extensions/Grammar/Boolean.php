<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Grammar_Boolean extends Twig_Extensions_Grammar
{
    public function __toString()
    {
        return sprintf('<%s:boolean>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect(Twig_Token::NAME_TYPE, array('true', 'false'));

        return new Twig_Node_Expression_Constant('true' === $token->getValue() ? true : false, $token->getLine());
    }
}
