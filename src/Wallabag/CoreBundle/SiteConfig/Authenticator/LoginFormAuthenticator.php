<?php

namespace Wallabag\CoreBundle\SiteConfig\Authenticator;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Wallabag\CoreBundle\ExpressionLanguage\AuthenticatorProvider;
use Wallabag\CoreBundle\SiteConfig\SiteConfig;

class LoginFormAuthenticator implements Authenticator
{
    /** @var \GuzzleHttp\Client */
    protected $guzzle;

    /** @var SiteConfig */
    private $siteConfig;

    public function __construct(SiteConfig $siteConfig)
    {
        // @todo OptionResolver
        $this->siteConfig = $siteConfig;
    }

    public function login(ClientInterface $guzzle)
    {
        $postFields = [
            $this->siteConfig->getUsernameField() => $this->siteConfig->getUsername(),
            $this->siteConfig->getPasswordField() => $this->siteConfig->getPassword(),
        ] + $this->getExtraFields($guzzle);

        $guzzle->post(
            $this->siteConfig->getLoginUri(),
            ['body' => $postFields, 'allow_redirects' => true, 'verify' => false]
        );

        return $this;
    }

    public function isLoggedIn(ClientInterface $guzzle)
    {
        if (($cookieJar = $guzzle->getDefaultOption('cookies')) instanceof CookieJar) {
            /** @var \GuzzleHttp\Cookie\SetCookie $cookie */
            foreach ($cookieJar as $cookie) {
                // check required cookies
                if ($cookie->getDomain() === $this->siteConfig->getHost()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isLoginRequired($html)
    {
        $useInternalErrors = libxml_use_internal_errors(true);

        // need to check for the login dom element ($options['not_logged_in_xpath']) in the HTML
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        $xpath = new \DOMXPath($doc);
        $loggedIn = $xpath->evaluate((string) $this->siteConfig->getNotLoggedInXpath());

        if (false === $loggedIn) {
            return false;
        }

        libxml_use_internal_errors($useInternalErrors);

        return $loggedIn->length > 0;
    }

    /**
     * Returns extra fields from the configuration.
     * Evaluates any field value that is an expression language string.
     *
     * @return array
     */
    private function getExtraFields(ClientInterface $guzzle)
    {
        $extraFields = [];

        foreach ($this->siteConfig->getExtraFields() as $fieldName => $fieldValue) {
            if ('@=' === substr($fieldValue, 0, 2)) {
                $expressionLanguage = $this->getExpressionLanguage($guzzle);
                $fieldValue = $expressionLanguage->evaluate(
                    substr($fieldValue, 2),
                    [
                        'config' => $this->siteConfig,
                    ]
                );
            }

            $extraFields[$fieldName] = $fieldValue;
        }

        return $extraFields;
    }

    /**
     * @return ExpressionLanguage
     */
    private function getExpressionLanguage(ClientInterface $guzzle)
    {
        return new ExpressionLanguage(
            null,
            [new AuthenticatorProvider($guzzle)]
        );
    }
}
