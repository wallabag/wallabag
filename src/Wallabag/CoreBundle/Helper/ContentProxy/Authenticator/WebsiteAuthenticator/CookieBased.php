<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator;

use GuzzleHttp\Cookie\CookieJar;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\Exception\AuthenticatorException;

interface CookieBased
{
    /**
     * Verifies the contents of the cookie jar after login.
     *
     * @param \GuzzleHttp\Cookie\CookieJar $cookieJar
     *
     * @throws AuthenticatorException If login failed
     */
    public function verifyCookies(CookieJar $cookieJar);

    public function getCookieJar();
}
