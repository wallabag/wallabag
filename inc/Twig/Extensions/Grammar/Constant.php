<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Grammar_Constant extends Twig_Extensions_Grammar
{
    protected $type;

    public function __construct($name, $type = null)
    {
        $this->name = $name;
        $this->type = null === $type ? Twig_Token::NAME_TYPE : $type;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect($this->type, $this->name);

        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }
}
