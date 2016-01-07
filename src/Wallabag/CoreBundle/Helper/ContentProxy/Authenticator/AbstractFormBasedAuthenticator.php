<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator;

use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\Exception\AuthenticatorException;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\CookieBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\CredentialsBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\FormBased;
use Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator\UrlBased;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

abstract class AbstractFormBasedAuthenticator implements CredentialsBased, UrlBased, FormBased, CookieBased
{
    /** @var CookieJar */
    private $cookieJar = true;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $uri;

    /** @var \GuzzleHttp\Client */
    protected $guzzle;

    public function setGuzzleClient(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function setCookieJar(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function login()
    {
        $postFields = [
            $this->getUsernameFieldName() => $this->getUsername(),
            $this->getPasswordFieldName() => $this->getPassword(),
        ] + $this->getExtraFormFields();

        $this->guzzle->post(
            $this->getUri(),
            ['body' => $postFields, 'allow_redirects' => true, 'cookies' => $this->getCookieJar(), 'verify' => false]
        );

        $this->verifyCookies($this->getCookieJar());
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    public function isLoggedIn()
    {
        if ($this->cookieJar instanceof CookieJar) {
            try {
                return $this->verifyCookies($this->cookieJar);
            } catch (AuthenticatorException $e) {
                return false;
            }
        }

        return false;
    }
}
