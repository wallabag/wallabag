<?php

declare(strict_types=1);

namespace Wallabag\Helper;

use Wallabag\Entity\Config;
use Wallabag\Entity\RenderingProxyHost;

class RenderingProxy
{
    public function __construct(
        private readonly ?string $renderingProxyUrl,
        private readonly int $renderingProxyAll,
        private readonly int $renderingProxyTimeout,
    ) {
    }

    /**
     * Checks if rendering proxy URL feature is enabled.
     */
    public function isEnabled(): bool
    {
        return null !== $this->renderingProxyUrl && '' !== $this->renderingProxyUrl;
    }

    /**
     * Checks if given URL should be passed to rendering proxy and returns
     *      - proxified URL
     *      - post-processing callback.
     *
     * @return array{0: string, 1: \Closure(mixed): mixed|null}
     * @description callback takes Graby::fetchContent output as argument
     */
    public function considerUrl(?Config $userConfig, string $url): array
    {
        if ($this->isEnabled()) {
            preg_match('~^[^/]+://([^/]+)~', $url, $matches);
            $host = $matches[1];

            $userHosts = $userConfig ? $userConfig
                ->getRenderingProxyHosts()
                ->map(fn (RenderingProxyHost $e) => $e->getHost())
                ->toArray() : [];

            $proxy = $this->renderingProxyAll
                // host is in the list
                || \in_array($host, $userHosts, true)
                // Or host is a subhost of something in the list
                || array_find($userHosts, fn ($h) => 1 === preg_match("/\.$h$/", $host));

            if ($proxy) {
                return [
                    str_replace('%u', $url, $this->renderingProxyUrl),
                    // This callback will post-process the output of Graby\fetchContent()
                    function ($fetchedContent) use ($url) {
                        $fetchedContent['url'] = $url;
                        $fetchedContent['html'] = $this->fixResponse($fetchedContent['html']);

                        return $fetchedContent;
                    },
                ];
            }
        }

        return [$url, null];
    }

    /**
     * Checks if URL is an external rendering proxy one.
     */
    public function ownsUrl(string $url): bool
    {
        if ($this->isEnabled()) {
            $base = preg_replace('/%u.*$/', '', $this->renderingProxyUrl);

            return 1 === $this->renderingProxyAll || (str_starts_with($url, $base) && 1 === preg_match("~^{$base}http[s]?://~", $url));
        }

        return false;
    }

    /**
     * Return timeout for externally rendered pages.
     */
    public function getTimeout(): int
    {
        return $this->renderingProxyTimeout;
    }

    /**
     * Try to fix the content retreived through the client-side rendering proxy.
     * Some HTML tags will be escaped (ie: "&lt;img") in original content and
     * their code will appear in the page body.
     * This function processes the content to repair such escaped tags.
     */
    private function fixResponse(string $content): string
    {
        $content = preg_replace('/&lt;img ([^&]+)&gt;/', '<img \1>', $content);

        return $content;
    }
}
