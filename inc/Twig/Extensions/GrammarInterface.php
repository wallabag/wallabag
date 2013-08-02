<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
interface Twig_Extensions_GrammarInterface
{
    function setParser(Twig_ParserInterface $parser);

    function parse(Twig_Token $token);

    function getName();
}
