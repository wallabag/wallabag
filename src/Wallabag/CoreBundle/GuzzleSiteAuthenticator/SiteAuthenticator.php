<?php

namespace Wallabag\CoreBundle\GuzzleSiteAuthenticator;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Logs in with the site's configured form before fetching restricted articles.
 */
class SiteAuthenticator
{
    private const MAX_RETRIES = 2;

    private $configBuilder;

    public function __construct(GrabySiteConfigBuilder $configBuilder)
    {
        $this->configBuilder = $configBuilder;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if (!($options['cookies'] ?? null) instanceof CookieJarInterface) {
                return $handler($request, $options);
            }

            $config = $this->configBuilder->buildForHost($request->getUri()->getHost());
            if (false === $config || !$config->requiresLogin()) {
                return $handler($request, $options);
            }

            // Login requests need cookies and redirects, but must not re-enter this middleware.
            $client = new Client(array_replace($options, [
                'handler' => $handler,
                'allow_redirects' => true,
                'http_errors' => true,
            ]));
            $cookies = $client->getConfig('cookies');
            $loggedIn = false;
            foreach ($cookies as $cookie) {
                if ($cookie->getDomain() === $config->getHost()) {
                    $loggedIn = true;
                    break;
                }
            }

            if (!$loggedIn) {
                $this->login($client, $config);
            }

            return $this->send($handler, $request, $options, $client, $config, $cookies);
        };
    }

    private function send(callable $handler, RequestInterface $request, array $options, Client $client, SiteConfig $config, CookieJarInterface $cookies, int $retries = 0): PromiseInterface
    {
        $request = $cookies->withCookieHeader($request);

        return $handler($request, $options)->then(function (ResponseInterface $response) use ($handler, $request, $options, $client, $config, $cookies, $retries) {
            $cookies->extractCookies($request, $response);
            $body = $response->getBody();
            $html = (string) $body;
            if ($body->isSeekable()) {
                $body->rewind();
            } else {
                $response = $response->withBody(Utils::streamFor($html));
            }

            if ($retries < self::MAX_RETRIES && $this->isLoginRequired($html, $config)) {
                $this->login($client, $config);

                return $this->send($handler, $request, $options, $client, $config, $cookies, $retries + 1);
            }

            return $response;
        });
    }

    private function login(Client $client, SiteConfig $config): void
    {
        $extraFields = $config->getExtraFields();
        $language = new ExpressionLanguage();
        $notCompiled = static function () {
            throw new \LogicException('Authentication expressions cannot be compiled.');
        };
        $language->register('request_html', $notCompiled, static function (array $variables, $uri, array $options = []) use ($client) {
            return (string) $client->get($uri, $options)->getBody();
        });
        $language->register('xpath', $notCompiled, function (array $variables, $query, $html) {
            return $this->xpathValue($query, (string) $html);
        });
        $language->register('preg_match', $notCompiled, static function (array $variables, $pattern, $html) {
            preg_match($pattern, (string) $html, $matches);

            return 2 === \count($matches) ? $matches[1] : '';
        });

        foreach ($extraFields as $name => $value) {
            if (str_starts_with($value, '@=')) {
                $extraFields[$name] = $language->evaluate(substr($value, 2), ['config' => $config]);
            }
        }

        $client->post($config->getLoginUri(), [
            'form_params' => [
                $config->getUsernameField() => $config->getUsername(),
                $config->getPasswordField() => $config->getPassword(),
            ] + $extraFields,
        ]);
    }

    private function isLoginRequired(string $html, SiteConfig $config): bool
    {
        if ('' === $html || !$config->getNotLoggedInXpath()) {
            return false;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            $document->loadHTML($html, \LIBXML_NONET);
            $nodes = (new \DOMXPath($document))->query($config->getNotLoggedInXpath());

            return false !== $nodes && $nodes->length > 0;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function xpathValue(string $query, string $html): string
    {
        if ('' === $html) {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            $document->loadHTML($html, \LIBXML_NONET | \LIBXML_NOCDATA);
            $nodes = (new \DOMXPath($document))->query($query);
            $node = false !== $nodes ? $nodes->item(0) : null;

            return $node && $node->attributes && $node->attributes->getNamedItem('value') ? $node->attributes->getNamedItem('value')->nodeValue : '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
