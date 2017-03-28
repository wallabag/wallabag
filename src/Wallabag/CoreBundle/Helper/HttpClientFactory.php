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
    /** @var \GuzzleHttp\Event\SubscriberInterface */
    private $authenticatorSubscriber;

    /** @var \GuzzleHttp\Cookie\CookieJar */
    private $cookieJar;

    private $restrictedAccess;
    private $logger;

    /**
     * HttpClientFactory constructor.
     *
     * @param \GuzzleHttp\Event\SubscriberInterface $authenticatorSubscriber
     * @param \GuzzleHttp\Cookie\CookieJar          $cookieJar
     * @param string                                $restrictedAccess        this param is a kind of boolean. Values: 0 or 1
     * @param LoggerInterface                       $logger
     */
    public function __construct(SubscriberInterface $authenticatorSubscriber, CookieJar $cookieJar, $restrictedAccess, LoggerInterface $logger)
    {
        $this->authenticatorSubscriber = $authenticatorSubscriber;
        $this->cookieJar = $cookieJar;
        $this->restrictedAccess = $restrictedAccess;
        $this->logger = $logger;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function buildHttpClient()
    {
        $this->logger->log('debug', 'Restricted access config enabled?', array('enabled' => (int) $this->restrictedAccess));

        if (0 === (int) $this->restrictedAccess) {
            return;
        }

        // we clear the cookie to avoid websites who use cookies for analytics
        $this->cookieJar->clear();
        // need to set the (shared) cookie jar
        $client = new Client(['handler' => new SafeCurlHandler(), 'defaults' => ['cookies' => $this->cookieJar]]);
        $client->getEmitter()->attach($this->authenticatorSubscriber);

        return $client;
    }
}
