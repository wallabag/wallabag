<?php

namespace Wallabag\CoreBundle\Helper;

use GuzzleHttp\Cookie\FileCookieJar as BaseFileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Utils;
use Psr\Log\LoggerInterface;

/**
 * Overidden Cookie behavior to:
 *     - ignore error when the cookie file is malformatted (resulting in clearing it).
 */
class FileCookieJar extends BaseFileCookieJar
{
    private $logger;

    /**
     * @param LoggerInterface $logger     Only used to log info when something goes wrong
     * @param string          $cookieFile File to store the cookie data
     */
    public function __construct(LoggerInterface $logger, $cookieFile)
    {
        parent::__construct($cookieFile);

        $this->logger = $logger;
    }

    /**
     * Load cookies from a JSON formatted file.
     *
     * Old cookies are kept unless overwritten by newly loaded ones.
     *
     * @param string $filename cookie file to load
     *
     * @throws \RuntimeException if the file cannot be loaded
     */
    public function load($filename)
    {
        $json = file_get_contents($filename);
        if (false === $json) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Unable to load file {$filename}");
            // @codeCoverageIgnoreEnd
        }

        try {
            $data = Utils::jsonDecode($json, true);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('JSON inside the cookie is broken', [
                'json' => $json,
                'error_msg' => $e->getMessage(),
            ]);

            // cookie file is invalid, just ignore the exception and it'll reset the whole cookie file
            $data = '';
        }

        if (\is_array($data)) {
            foreach (Utils::jsonDecode($json, true) as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (\strlen($data)) {
            throw new \RuntimeException("Invalid cookie file: {$filename}");
        }
    }
}
