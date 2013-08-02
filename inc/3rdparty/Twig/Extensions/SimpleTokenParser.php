<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Twig_Extensions_SimpleTokenParser extends Twig_TokenParser
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
        $grammar = $this->getGrammar();
        if (!is_object($grammar)) {
            $grammar = self::parseGrammar($grammar);
        }

        $grammar->setParser($this->parser);
        $values = $grammar->parse($token);

        return $this->getNode($values, $token->getLine());
    }

    /**
     * Gets the grammar as an object or as a string.
     *
     * @return string|Twig_Extensions_Grammar A Twig_Extensions_Grammar instance or a string
     */
    abstract protected function getGrammar();

    /**
     * Gets the nodes based on the parsed values.
     *
     * @param array   $values An array of values
     * @param integer $line   The parser line
     */
    abstract protected function getNode(array $values, $line);

    protected function getAttribute($node, $attribute, $arguments = array(), $type = Twig_Node_Expression_GetAttr::TYPE_ANY, $line = -1)
    {
        return new Twig_Node_Expression_GetAttr(
            $node instanceof Twig_NodeInterface ? $node : new Twig_Node_Expression_Name($node, $line),
            $attribute instanceof Twig_NodeInterface ? $attribute : new Twig_Node_Expression_Constant($attribute, $line),
            $arguments instanceof Twig_NodeInterface ? $arguments : new Twig_Node($arguments),
            $type,
            $line
        );
    }

    protected function call($node, $attribute, $arguments = array(), $line = -1)
    {
        return $this->getAttribute($node, $attribute, $arguments, Twig_Node_Expression_GetAttr::TYPE_METHOD, $line);
    }

    protected function markAsSafe(Twig_NodeInterface $node, $line = -1)
    {
        return new Twig_Node_Expression_Filter(
            $node,
            new Twig_Node_Expression_Constant('raw', $line),
            new Twig_Node(),
            $line
        );
    }

    protected function output(Twig_NodeInterface $node, $line = -1)
    {
        return new Twig_Node_Print($node, $line);
    }

    protected function getNodeValues(array $values)
    {
        $nodes = array();
        foreach ($values as $value) {
            if ($value instanceof Twig_NodeInterface) {
                $nodes[] = $value;
            }
        }

        return $nodes;
    }

    static public function parseGrammar($str, $main = true)
    {
        static $cursor;

        if (true === $main) {
            $cursor = 0;
            $grammar = new Twig_Extensions_Grammar_Tag();
        } else {
            $grammar = new Twig_Extensions_Grammar_Optional();
        }

        while ($cursor < strlen($str)) {
            if (preg_match('/\s+/A', $str, $match, null, $cursor)) {
                $cursor += strlen($match[0]);
            } elseif (preg_match('/<(\w+)(?:\:(\w+))?>/A', $str, $match, null, $cursor)) {
                $class = sprintf('Twig_Extensions_Grammar_%s', ucfirst(isset($match[2]) ? $match[2] : 'Expression'));
                if (!class_exists($class)) {
                    throw new Twig_Error_Runtime(sprintf('Unable to understand "%s" in grammar (%s class does not exist)', $match[0], $class));
                }
                $grammar->addGrammar(new $class($match[1]));
                $cursor += strlen($match[0]);
            } elseif (preg_match('/\w+/A', $str, $match, null, $cursor)) {
                $grammar->addGrammar(new Twig_Extensions_Grammar_Constant($match[0]));
                $cursor += strlen($match[0]);
            } elseif (preg_match('/,/A', $str, $match, null, $cursor)) {
                $grammar->addGrammar(new Twig_Extensions_Grammar_Constant($match[0], Twig_Token::PUNCTUATION_TYPE));
                $cursor += strlen($match[0]);
            } elseif (preg_match('/\[/A', $str, $match, null, $cursor)) {
                $cursor += strlen($match[0]);
                $grammar->addGrammar(self::parseGrammar($str, false));
            } elseif (true !== $main && preg_match('/\]/A', $str, $match, null, $cursor)) {
                $cursor += strlen($match[0]);

                return $grammar;
            } else {
                throw new Twig_Error_Runtime(sprintf('Unable to parse grammar "%s" near "...%s..."', $str, substr($str, $cursor, 10)));
            }
        }

        return $grammar;
    }
}
