<?php

namespace Wallabag\CoreBundle\GuzzleSiteAuthenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use Graby\SiteConfig\ConfigBuilder;
use OutOfRangeException;

class GrabySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * @var \Graby\SiteConfig\ConfigBuilder
     */
    private $grabyConfigBuilder;
    /**
     * @var array
     */
    private $credentials;

    /**
     * GrabySiteConfigBuilder constructor.
     *
     * @param \Graby\SiteConfig\ConfigBuilder $grabyConfigBuilder
     * @param array                           $credentials
     */
    public function __construct(ConfigBuilder $grabyConfigBuilder, array $credentials = [])
    {
        $this->grabyConfigBuilder = $grabyConfigBuilder;
        $this->credentials = $credentials;
    }

    /**
     * Builds the SiteConfig for a host.
     *
     * @param string $host The "www." prefix is ignored
     *
     * @return SiteConfig
     *
     * @throws OutOfRangeException If there is no config for $host
     */
    public function buildForHost($host)
    {
        // required by credentials below
        $host = strtolower($host);
        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        $config = $this->grabyConfigBuilder->buildForHost($host);
        $parameters = [
            'host' => $host,
            'requiresLogin' => $config->requires_login ?: false,
            'loginUri' => $config->login_uri ?: null,
            'usernameField' => $config->login_username_field ?: null,
            'passwordField' => $config->login_password_field ?: null,
            'extraFields' => is_array($config->login_extra_fields) ? $config->login_extra_fields : [],
            'notLoggedInXpath' => $config->not_logged_in_xpath ?: null,
        ];

        if (isset($this->credentials[$host])) {
            $parameters['username'] = $this->credentials[$host]['username'];
            $parameters['password'] = $this->credentials[$host]['password'];
        }

        return new SiteConfig($parameters);
    }
}
