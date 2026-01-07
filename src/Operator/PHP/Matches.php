<?php

namespace Wallabag\Operator\PHP;

/**
 * Provides a "matches" operator used for tagging rules.
 *
 * It asserts that a given pattern is contained in a subject, in a
 * case-insensitive way.
 *
 * This operator will be used to compile tagging rules in PHP, usable
 * directly on Entry objects for instance.
 * It's registered in RulerZ using a service;
 */
class Matches
{
    public function __invoke($subject, $pattern)
    {
        if (null === $subject) {
            return false;
        }

        return false !== stripos((string) $subject, (string) $pattern);
    }
}
