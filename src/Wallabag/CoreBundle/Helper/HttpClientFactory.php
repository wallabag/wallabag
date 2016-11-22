<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Ring\Client\SafeCurlHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Builds and configures the Guzzle HTTP client.
 */
class HttpClientFactory
{
    /** @var \GuzzleHttp\Event\SubscriberInterface */
    private $authenticatorSubscriber;

    /** @var \GuzzleHttp\Cookie\CookieJar */
    private $cookieJar;

    /**
     * HttpClientFactory constructor.
     *
     * @param \GuzzleHttp\Event\SubscriberInterface $authenticatorSubscriber
     * @param \GuzzleHttp\Cookie\CookieJar          $cookieJar
     */
    public function __construct(SubscriberInterface $authenticatorSubscriber, CookieJar $cookieJar)
    {
        $this->authenticatorSubscriber = $authenticatorSubscriber;
        $this->cookieJar = $cookieJar;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function buildHttpClient()
    {
        // we clear the cookie to avoid websites who use cookies for analytics
        $this->cookieJar->clear();
        // need to set the (shared) cookie jar
        $client = new Client(['handler' => new SafeCurlHandler(), 'defaults' => ['cookies' => $this->cookieJar]]);
        $client->getEmitter()->attach($this->authenticatorSubscriber);

        return $client;
    }
}
