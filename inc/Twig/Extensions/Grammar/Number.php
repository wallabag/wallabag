<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Grammar_Number extends Twig_Extensions_Grammar
{
    public function __toString()
    {
        return sprintf('<%s:number>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect(Twig_Token::NUMBER_TYPE);

        return new Twig_Node_Expression_Constant($token->getValue(), $token->getLine());
    }
}
