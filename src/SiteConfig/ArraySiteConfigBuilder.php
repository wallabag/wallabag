<?php

namespace Wallabag\SiteConfig;

class ArraySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * Map of hostname => SiteConfig.
     */
    private $configs = [];

    public function __construct(array $hostConfigMap = [])
    {
        foreach ($hostConfigMap as $host => $hostConfig) {
            $hostConfig['host'] = $host;
            $this->configs[$host] = new SiteConfig($hostConfig);
        }
    }

    public function buildForHost($host)
    {
        $host = strtolower($host);

        if ('www.' === substr($host, 0, 4)) {
            $host = substr($host, 4);
        }

        if (isset($this->configs[$host])) {
            return $this->configs[$host];
        }

        return false;
    }
}
