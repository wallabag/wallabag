<?php

/**
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Henrik Bjornskov <hb@peytz.dk>
 * @package Twig
 * @subpackage Twig-extensions
 */
class Twig_Extensions_Extension_Text extends Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            'truncate' => new Twig_Filter_Function('twig_truncate_filter', array('needs_environment' => true)),
            'wordwrap' => new Twig_Filter_Function('twig_wordwrap_filter', array('needs_environment' => true)),
        );

        if (version_compare(Twig_Environment::VERSION, '1.5.0-DEV', '<')) {
            $filters['nl2br'] = new Twig_Filter_Function('twig_nl2br_filter', array('pre_escape' => 'html', 'is_safe' => array('html')));
        }

        return $filters;
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'Text';
    }
}

function twig_nl2br_filter($value, $sep = '<br />')
{
    return str_replace("\n", $sep."\n", $value);
}

if (function_exists('mb_get_info')) {
    function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...')
    {
        if (mb_strlen($value, $env->getCharset()) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
                    $length = $breakpoint;
                }
            }

            return rtrim(mb_substr($value, 0, $length, $env->getCharset())) . $separator;
        }

        return $value;
    }

    function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false)
    {
        $sentences = array();

        $previous = mb_regex_encoding();
        mb_regex_encoding($env->getCharset());

        $pieces = mb_split($separator, $value);
        mb_regex_encoding($previous);

        foreach ($pieces as $piece) {
            while(!$preserve && mb_strlen($piece, $env->getCharset()) > $length) {
                $sentences[] = mb_substr($piece, 0, $length, $env->getCharset());
                $piece = mb_substr($piece, $length, 2048, $env->getCharset());
            }

            $sentences[] = $piece;
        }

        return implode($separator, $sentences);
    }
} else {
    function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...')
    {
        if (strlen($value) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = strpos($value, ' ', $length))) {
                    $length = $breakpoint;
                }
            }

            return rtrim(substr($value, 0, $length)) . $separator;
        }

        return $value;
    }

    function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false)
    {
        return wordwrap($value, $length, $separator, !$preserve);
    }
}