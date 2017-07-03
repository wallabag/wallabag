<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Ring\Client\SafeCurlHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Event\SubscriberInterface;
use Psr\Log\LoggerInterface;

/**
 * Builds and configures the Guzzle HTTP client.
 */
class HttpClientFactory
{
    /** @var [\GuzzleHttp\Event\SubscriberInterface] */
    private $subscribers = [];

    /** @var \GuzzleHttp\Cookie\CookieJar */
    private $cookieJar;

    private $restrictedAccess;
    private $logger;

    /**
     * HttpClientFactory constructor.
     *
     * @param \GuzzleHttp\Cookie\CookieJar $cookieJar
     * @param string                       $restrictedAccess This param is a kind of boolean. Values: 0 or 1
     * @param LoggerInterface              $logger
     */
    public function __construct(CookieJar $cookieJar, $restrictedAccess, LoggerInterface $logger)
    {
        $this->cookieJar = $cookieJar;
        $this->restrictedAccess = $restrictedAccess;
        $this->logger = $logger;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function buildHttpClient()
    {
        $this->logger->log('debug', 'Restricted access config enabled?', ['enabled' => (int) $this->restrictedAccess]);

        if (0 === (int) $this->restrictedAccess) {
            return;
        }

        // we clear the cookie to avoid websites who use cookies for analytics
        $this->cookieJar->clear();
        // need to set the (shared) cookie jar
        $client = new Client(['handler' => new SafeCurlHandler(), 'defaults' => ['cookies' => $this->cookieJar]]);

        foreach ($this->subscribers as $subscriber) {
            $client->getEmitter()->attach($subscriber);
        }

        return $client;
    }

    /**
     * Adds a subscriber to the HTTP client.
     *
     * @param SubscriberInterface $subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}
