<?php

namespace Wallabag\SiteConfig;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Wallabag\ExpressionLanguage\AuthenticatorProvider;

class LoginFormAuthenticator
{
    private readonly ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly HttpBrowser $browser,
        AuthenticatorProvider $authenticatorProvider,
    ) {
        $this->expressionLanguage = new ExpressionLanguage(null, [$authenticatorProvider]);
    }

    /**
     * Logs the configured user on the given Guzzle client.
     *
     * @return self
     */
    public function login(SiteConfig $siteConfig)
    {
        $postFields = [
            $siteConfig->getUsernameField() => $siteConfig->getUsername(),
            $siteConfig->getPasswordField() => $siteConfig->getPassword(),
        ] + $this->getExtraFields($siteConfig);

        $this->browser->request('POST', $siteConfig->getLoginUri(), $postFields, [], $this->getHttpHeaders($siteConfig));

        return $this;
    }

    /**
     * Checks if we are logged into the site, but without calling the server (e.g. do we have a Cookie).
     *
     * @return bool
     */
    public function isLoggedIn(SiteConfig $siteConfig)
    {
        foreach ($this->browser->getCookieJar()->all() as $cookie) {
            // check required cookies
            if ($cookie->getDomain() === $siteConfig->getHost()) {
                return true;
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
        } catch (\Throwable) {
            return false;
        }

        return \count($loggedIn) > 0;
    }

    /**
     * Processes http_header(*) config, prepending HTTP_ string to the header's name.
     * See : https://github.com/symfony/browser-kit/blob/5.4/AbstractBrowser.php#L349.
     */
    protected function getHttpHeaders(SiteConfig $siteConfig): array
    {
        $headers = [];
        foreach ($siteConfig->getHttpHeaders() as $headerName => $headerValue) {
            $headers["HTTP_$headerName"] = $headerValue;
        }

        return $headers;
    }

    /**
     * Returns extra fields from the configuration.
     * Evaluates any field value that is an expression language string.
     *
     * @return array
     */
    private function getExtraFields(SiteConfig $siteConfig)
    {
        $extraFields = [];

        foreach ($siteConfig->getExtraFields() as $fieldName => $fieldValue) {
            if (str_starts_with((string) $fieldValue, '@=')) {
                $fieldValue = $this->expressionLanguage->evaluate(
                    substr((string) $fieldValue, 2),
                    [
                        'config' => $siteConfig,
                    ]
                );
            }

            $extraFields[$fieldName] = $fieldValue;
        }

        return $extraFields;
    }
}
