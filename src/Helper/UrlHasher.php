<?php

namespace Wallabag\Helper;

/**
 * Hash URLs for privacy and performance.
 */
class UrlHasher
{
    /**
     * Hash the given url using the given algorithm.
     * Hashed url are faster to be retrieved in the database than the real url.
     *
     * @param string $algorithm
     *
     * @return string
     */
    public static function hashUrl(string $url, $algorithm = 'sha1')
    {
        return hash($algorithm, urldecode($url));
    }
}
