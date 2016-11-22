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

    private $restrictedAccess;

    /**
     * HttpClientFactory constructor.
     *
     * @param \GuzzleHttp\Event\SubscriberInterface $authenticatorSubscriber
     * @param \GuzzleHttp\Cookie\CookieJar          $cookieJar
     * @param string                                $restrictedAccess
     */
    public function __construct(SubscriberInterface $authenticatorSubscriber, CookieJar $cookieJar, $restrictedAccess)
    {
        $this->authenticatorSubscriber = $authenticatorSubscriber;
        $this->cookieJar = $cookieJar;
        $this->restrictedAccess = $restrictedAccess;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function buildHttpClient()
    {
        if (0 === (int) $this->restrictedAccess) {
            return null;
        }

        // we clear the cookie to avoid websites who use cookies for analytics
        $this->cookieJar->clear();
        // need to set the (shared) cookie jar
        $client = new Client(['handler' => new SafeCurlHandler(), 'defaults' => ['cookies' => $this->cookieJar]]);
        $client->getEmitter()->attach($this->authenticatorSubscriber);

        return $client;
    }
}
