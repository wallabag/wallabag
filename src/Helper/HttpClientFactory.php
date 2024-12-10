<?php

namespace Wallabag\Helper;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Event\SubscriberInterface;
use Http\Adapter\Guzzle5\Client as GuzzleAdapter;
use Http\Client\HttpClient;
use Http\HttplugBundle\ClientFactory\ClientFactory;
use Psr\Log\LoggerInterface;

/**
 * Builds and configures the HTTP client.
 */
class HttpClientFactory implements ClientFactory
{
    /** @var SubscriberInterface[] */
    private $subscribers = [];

    /** @var string */
    private $userAgent;

    /** @var CookieJar */
    private $cookieJar;

    private $restrictedAccess;
    private $logger;

    /**
     * HttpClientFactory constructor.
     *
     * @param string $restrictedAccess This param is a kind of boolean. Values: 0 or 1
     */
    public function __construct(CookieJar $cookieJar, $restrictedAccess, LoggerInterface $logger)
    {
        $this->cookieJar = $cookieJar;
        $this->restrictedAccess = $restrictedAccess;
        $this->logger = $logger;
    }

    /**
     * Adds a subscriber to the HTTP client.
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * Set the default user-agent.
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Input an array of configuration to be able to create a HttpClient.
     *
     * @return HttpClient
     */
    public function createClient(array $config = [])
    {
        $this->logger->log('debug', 'Restricted access config enabled?', ['enabled' => (int) $this->restrictedAccess]);

        if (0 === (int) $this->restrictedAccess) {
            return new GuzzleAdapter(new GuzzleClient($config));
        }

        // we clear the cookie to avoid websites who use cookies for analytics
        $this->cookieJar->clear();
        if (!isset($config['defaults']['cookies'])) {
            // need to set the (shared) cookie jar
            $config['defaults']['cookies'] = $this->cookieJar;
        }

        if (!isset($config['defaults']['headers'])) {
            $config['defaults']['headers'] = [];
        }
        if (!isset($config['defaults']['headers']['User-Agent'])) {
            // need to set the user-agent
            $config['defaults']['headers']['User-Agent'] = $this->userAgent;
        }

        $guzzle = new GuzzleClient($config);
        foreach ($this->subscribers as $subscriber) {
            $guzzle->getEmitter()->attach($subscriber);
        }

        return new GuzzleAdapter($guzzle);
    }
}
