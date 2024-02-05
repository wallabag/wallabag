<?php

namespace Wallabag\CoreBundle\SiteConfig\Authenticator;

use GuzzleHttp\ClientInterface;

interface Authenticator
{
    /**
     * Logs the configured user on the given Guzzle client.
     *
     * @return self
     */
    public function login(ClientInterface $guzzle);

    /**
     * Checks if we are logged into the site, but without calling the server (e.g. do we have a Cookie).
     *
     * @return bool
     */
    public function isLoggedIn(ClientInterface $guzzle);

    /**
     * Checks from the HTML of a page if authentication is requested by a grabbed page.
     *
     * @param string $html
     *
     * @return bool
     */
    public function isLoginRequired($html);
}
