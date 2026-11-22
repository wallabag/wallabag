<?php

namespace Wallabag\Helper;

final class FeedUrlValidator
{
    public static function isValid(string $url): bool
    {
        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);

        return \in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }
}
