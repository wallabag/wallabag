<?php

namespace Wallabag\CoreBundle\Helper;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\HandlerStack;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Client\HttpClient;
use Http\HttplugBundle\ClientFactory\ClientFactory;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\SiteAuthenticator;

/**
 * Builds and configures the HTTP client.
 */
class HttpClientFactory implements ClientFactory
{
    private $cookieJar;
    private $restrictedAccess;
    private $logger;
    private $authenticator;

    /**
     * @param string $restrictedAccess This param is a kind of boolean. Values: 0 or 1
     */
    public function __construct(CookieJar $cookieJar, $restrictedAccess, LoggerInterface $logger, SiteAuthenticator $authenticator)
    {
        $this->cookieJar = $cookieJar;
        $this->restrictedAccess = $restrictedAccess;
        $this->logger = $logger;
        $this->authenticator = $authenticator;
    }

    /**
     * @return HttpClient
     */
    public function createClient(array $config = [])
    {
        $this->logger->log('debug', 'Restricted access config enabled?', ['enabled' => (int) $this->restrictedAccess]);

        if (0 === (int) $this->restrictedAccess) {
            return new GuzzleAdapter(new GuzzleClient($config));
        }

        // Clear cookies from the previous fetch, including analytics cookies.
        $this->cookieJar->clear();
        if (!isset($config['cookies'])) {
            $config['cookies'] = $this->cookieJar;
        }

        $handler = $config['handler'] ?? null;
        $stack = $handler instanceof HandlerStack ? clone $handler : HandlerStack::create($handler);
        $stack->unshift($this->authenticator, 'site_authenticator');
        $config['handler'] = $stack;

        return new GuzzleAdapter(new GuzzleClient($config));
    }
}
