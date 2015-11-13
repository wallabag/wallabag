<?php

namespace Wallabag\CoreBundle\Operator\PHP;

class Matches
{
    public function __invoke($subject, $pattern)
    {
        return stripos($subject, $pattern) !== false;
    }
}
