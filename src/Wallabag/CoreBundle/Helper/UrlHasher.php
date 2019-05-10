<?php

namespace Wallabag\CoreBundle\Helper;

/**
 * Hash URLs for privacy and performance.
 */
class UrlHasher
{
    /** @var string */
    const ALGORITHM = 'sha1';

    /**
     * @param string $url
     *
     * @return string hashed $url
     */
    public static function hashUrl(string $url)
    {
        return hash(static::ALGORITHM, $url);
    }
}
