<?php

namespace Wallabag\Operator\PHP;

/**
 * Provides a "~" operator used for ignore origin rules.
 *
 * It asserts that a subject matches a given regexp pattern, in a
 * case-insensitive way.
 *
 * This operator will be used to compile ignore origin rules in PHP, usable
 * directly on Entry objects for instance.
 * It's registered in RulerZ using a service;
 */
class PatternMatches
{
    public function __invoke($subject, $pattern)
    {
        $count = preg_match("`$pattern`i", (string) $subject);

        return \is_int($count) && $count > 0;
    }
}
