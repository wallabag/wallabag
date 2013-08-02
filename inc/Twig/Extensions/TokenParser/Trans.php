<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_TokenParser_Trans extends Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $count = null;
        $plural = null;

        if (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(Twig_Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse(array($this, 'decideForFork'));
            if ('plural' === $stream->next()->getValue()) {
                $count = $this->parser->getExpressionParser()->parseExpression();
                $stream->expect(Twig_Token::BLOCK_END_TYPE);
                $plural = $this->parser->subparse(array($this, 'decideForEnd'), true);
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $this->checkTransString($body, $lineno);

        return new Twig_Extensions_Node_Trans($body, $plural, $count, $lineno, $this->getTag());
    }

    public function decideForFork(Twig_Token $token)
    {
        return $token->test(array('plural', 'endtrans'));
    }

    public function decideForEnd(Twig_Token $token)
    {
        return $token->test('endtrans');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @param string The tag name
     */
    public function getTag()
    {
        return 'trans';
    }

    protected function checkTransString(Twig_NodeInterface $body, $lineno)
    {
        foreach ($body as $i => $node) {
            if (
                $node instanceof Twig_Node_Text
                ||
                ($node instanceof Twig_Node_Print && $node->getNode('expr') instanceof Twig_Node_Expression_Name)
            ) {
                continue;
            }

            throw new Twig_Error_Syntax(sprintf('The text to be translated with "trans" can only contain references to simple variables'), $lineno);
        }
    }
}
