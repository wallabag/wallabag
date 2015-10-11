<?php

namespace Wallabag\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StringToListTransformer implements DataTransformerInterface
{
    private $separator;

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
     * @param  string $string
     *
     * @return array|null
     */
    public function reverseTransform($string)
    {
        if (!$string) {
            return null;
        }

        return array_filter(array_map('trim', explode($this->separator, $string)));
    }
}
