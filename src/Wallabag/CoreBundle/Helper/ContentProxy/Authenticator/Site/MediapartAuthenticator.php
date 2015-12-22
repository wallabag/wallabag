<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\Site;

use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\AbstractFormBasedAuthenticator;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\Exception\AuthenticatorException;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\CookieBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\CredentialsBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\FormBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\UrlBased;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class MediapartAuthenticator
    extends AbstractFormBasedAuthenticator
    implements WebsiteAuthenticator, UrlBased, CredentialsBased, FormBased, CookieBased
{
    public function getUsernameFieldName()
    {
        return 'name';
    }

    public function getPasswordFieldName()
    {
        return 'password';
    }

    public function getExtraFormFields()
    {
        return ['op' => 'ok'];
    }

    public function verifyCookies(CookieJar $cookieJar)
    {
        /** @var SetCookie $cookie */
        foreach ($cookieJar as $cookie) {
            if ($cookie->getDomain() === '.mediapart.fr' && $cookie->getName() == 'MPSESSID') {
                return true;
            }
        }

        throw new AuthenticatorException($this->getUri());
    }

    public function requiresAuth($html)
    {
        // we use the length since the restricted access text gets filtered by Graby
        return strlen($html) < 1000;
    }
}
