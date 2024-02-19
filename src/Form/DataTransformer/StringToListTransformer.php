<?php

namespace Wallabag\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms a comma-separated list to a proper PHP array.
 * Example: the string "foo, bar" will become the array ["foo", "bar"].
 */
class StringToListTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $separator;

    /**
     * @param string $separator The separator used in the list
     */
    public function __construct($separator = ',')
    {
        $this->separator = $separator;
    }

    /**
     * Transforms a list to a string.
     *
     * @param array|null $list
     *
     * @return string
     */
    public function transform($list)
    {
        if (null === $list) {
            return '';
        }

        return implode($this->separator, $list);
    }

    /**
     * Transforms a string to a list.
     *
     * @param string $string
     *
     * @return array|null
     */
    public function reverseTransform($string)
    {
        if (null === $string) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode($this->separator, $string))));
    }
}
