<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Grammar_Tag extends Twig_Extensions_Grammar
{
    protected $grammar;

    public function __construct()
    {
        $this->grammar = array();
        foreach (func_get_args() as $grammar) {
            $this->addGrammar($grammar);
        }
    }

    public function __toString()
    {
        $repr = array();
        foreach ($this->grammar as $grammar) {
            $repr[] = (string) $grammar;
        }

        return implode(' ', $repr);
    }

    public function addGrammar(Twig_Extensions_GrammarInterface $grammar)
    {
        $this->grammar[] = $grammar;
    }

    public function parse(Twig_Token $token)
    {
        $elements = array();
        foreach ($this->grammar as $grammar) {
            $grammar->setParser($this->parser);

            $element = $grammar->parse($token);
            if (is_array($element)) {
                $elements = array_merge($elements, $element);
            } else {
                $elements[$grammar->getName()] = $element;
            }
        }

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return $elements;
    }
}
