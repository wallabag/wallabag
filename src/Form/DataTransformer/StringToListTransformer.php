<?php

namespace Wallabag\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms a comma-separated list to a proper PHP array.
 * Example: the string "foo, bar" will become the array ["foo", "bar"].
 */
class StringToListTransformer implements DataTransformerInterface
{
    /**
     * @param string $separator The separator used in the list
     */
    public function __construct(
        private $separator = ',',
    ) {
    }

    /**
     * Transforms a list to a string.
     *
     * @return string
     */
    public function transform($list)
    {
        if (null === $list) {
            return '';
        }

        if (!\is_array($list)) {
            throw new UnexpectedTypeException($list, 'array');
        }

        return implode($this->separator, $list);
    }

    /**
     * Transforms a string to a list.
     *
     * @return array|null
     */
    public function reverseTransform($string)
    {
        if (null === $string) {
            return null;
        }

        if (!\is_string($string)) {
            throw new UnexpectedTypeException($string, 'string');
        }

        return array_values(array_filter(array_map('trim', explode($this->separator, $string))));
    }
}
