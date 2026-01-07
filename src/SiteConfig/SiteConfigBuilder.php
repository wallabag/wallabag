<?php

namespace Wallabag\SiteConfig;

interface SiteConfigBuilder
{
    /**
     * Builds the SiteConfig for a host.
     *
     * @param string $host The "www." prefix is ignored.
     *
     * @throws \OutOfRangeException If there is no config for $host
     *
     * @return SiteConfig|false
     */
    public function buildForHost($host);
}
