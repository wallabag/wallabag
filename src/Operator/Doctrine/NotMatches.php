<?php

namespace Wallabag\Operator\Doctrine;

/**
 * Provides a "notmatches" operator used for tagging rules.
 *
 * It asserts that a given pattern is not contained in a subject, in a
 * case-insensitive way.
 *
 * This operator will be used to compile tagging rules in DQL, usable
 * by Doctrine ORM.
 * It's registered in RulerZ using a service;
 */
class NotMatches
{
    public function __invoke($subject, $pattern)
    {
        if ("'" === $pattern[0]) {
            $pattern = \sprintf("'%%%s%%'", substr($pattern, 1, -1));
        }

        return \sprintf('UPPER(%s) NOT LIKE UPPER(%s)', $subject, $pattern);
    }
}
