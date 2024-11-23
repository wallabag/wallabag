<?php

namespace Wallabag\SiteConfig\Authenticator;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Wallabag\ExpressionLanguage\AuthenticatorProvider;
use Wallabag\SiteConfig\SiteConfig;

class LoginFormAuthenticator
{
    /**
     * Logs the configured user on the given Guzzle client.
     *
     * @return self
     */
    public function login(SiteConfig $siteConfig, ClientInterface $guzzle)
    {
        $postFields = [
            $siteConfig->getUsernameField() => $siteConfig->getUsername(),
            $siteConfig->getPasswordField() => $siteConfig->getPassword(),
        ] + $this->getExtraFields($siteConfig, $guzzle);

        $guzzle->post(
            $siteConfig->getLoginUri(),
            ['body' => $postFields, 'allow_redirects' => true, 'verify' => false]
        );

        return $this;
    }

    /**
     * Checks if we are logged into the site, but without calling the server (e.g. do we have a Cookie).
     *
     * @return bool
     */
    public function isLoggedIn(SiteConfig $siteConfig, ClientInterface $guzzle)
    {
        if (($cookieJar = $guzzle->getDefaultOption('cookies')) instanceof CookieJar) {
            /** @var \GuzzleHttp\Cookie\SetCookie $cookie */
            foreach ($cookieJar as $cookie) {
                // check required cookies
                if ($cookie->getDomain() === $siteConfig->getHost()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks from the HTML of a page if authentication is requested by a grabbed page.
     *
     * @param string $html
     *
     * @return bool
     */
    public function isLoginRequired(SiteConfig $siteConfig, $html)
    {
        // need to check for the login dom element ($options['not_logged_in_xpath']) in the HTML
        try {
            $crawler = new Crawler((string) $html);

            $loggedIn = $crawler->evaluate((string) $siteConfig->getNotLoggedInXpath());
        } catch (\Throwable $e) {
            return false;
        }

        return \count($loggedIn) > 0;
    }

    /**
     * Returns extra fields from the configuration.
     * Evaluates any field value that is an expression language string.
     *
     * @return array
     */
    private function getExtraFields(SiteConfig $siteConfig, ClientInterface $guzzle)
    {
        $extraFields = [];

        foreach ($siteConfig->getExtraFields() as $fieldName => $fieldValue) {
            if ('@=' === substr($fieldValue, 0, 2)) {
                $expressionLanguage = $this->getExpressionLanguage($guzzle);
                $fieldValue = $expressionLanguage->evaluate(
                    substr($fieldValue, 2),
                    [
                        'config' => $siteConfig,
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
