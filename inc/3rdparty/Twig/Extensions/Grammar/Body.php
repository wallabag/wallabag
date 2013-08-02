<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Grammar_Body extends Twig_Extensions_Grammar
{
    protected $end;

    public function __construct($name, $end = null)
    {
        parent::__construct($name);

        $this->end = null === $end ? 'end'.$name : $end;
    }

    public function __toString()
    {
        return sprintf('<%s:body>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return $this->parser->subparse(array($this, 'decideBlockEnd'), true);
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test($this->end);
    }
}
