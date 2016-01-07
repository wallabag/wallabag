<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator;

interface WebsiteAuthenticator
{
    /**
     * Logs the configured user, and returns the session cookie string.
     */
    public function login();

    public function setCredentials($username, $password);

    /**
     * Checks if we are logged into the site, but without calling the server (e.g. do we have a Cookie).
     *
     * @return bool
     */
    public function isLoggedIn();

    /**
     * Checks from the HTML of a page if authentication is requested by a grabbed page.
     *
     * @param string $html
     *
     * @return bool
     */
    public function requiresAuth($html);
}
