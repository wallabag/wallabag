<?php

namespace Wallabag\CoreBundle\Operator\Doctrine;

class Matches
{
    public function __invoke($subject, $pattern)
    {
        if ($pattern[0] === "'") {
            $pattern = sprintf("'%%%s%%'", substr($pattern, 1, -1));
        }

        return sprintf('UPPER(%s) LIKE UPPER(%s)', $subject, $pattern);
    }
}
