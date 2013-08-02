<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Twig_Extensions_Grammar implements Twig_Extensions_GrammarInterface
{
    protected $name;
    protected $parser;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setParser(Twig_ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function getName()
    {
        return $this->name;
    }
}
