<?php

namespace Wallabag\SiteConfig\Authenticator;

use GuzzleHttp\ClientInterface;
use Wallabag\SiteConfig\SiteConfig;

interface Authenticator
{
    /**
     * Logs the configured user on the given Guzzle client.
     *
     * @return self
     */
    public function login(SiteConfig $siteConfig, ClientInterface $guzzle);

    /**
     * Checks if we are logged into the site, but without calling the server (e.g. do we have a Cookie).
     *
     * @return bool
     */
    public function isLoggedIn(SiteConfig $siteConfig, ClientInterface $guzzle);

    /**
     * Checks from the HTML of a page if authentication is requested by a grabbed page.
     *
     * @param string $html
     *
     * @return bool
     */
    public function isLoginRequired(SiteConfig $siteConfig, $html);
}
