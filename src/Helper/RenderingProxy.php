<?php

declare(strict_types=1);

namespace Wallabag\Helper;

use Wallabag\Entity\Entry;
use Wallabag\Entity\RenderingProxyHost;

class RenderingProxy
{
    public function __construct(
        private readonly null|string $renderingProxyUrl,
        private readonly int $renderingProxyAll,
        private readonly int $renderingProxyTimeout,
    ) {}

    /**
     * Checks if rendering proxy URL feature is enabled
     */
    public function isEnabled(): bool
    {
        return $this->renderingProxyUrl != null && $this->renderingProxyUrl != '';
    }

    /**
     * Checks if given URL should be passed to rendering proxy and returns proxified URL
     *
     * @return array<string,bool>
     */
    public function considerUrl(Entry $entry, string $url): array
    {
        if ($this->isEnabled()) {
            preg_match('~^[^/]+://([^/]+)~', $url, $matches);
            $host = $matches[1];

            $userHosts = $entry
                ->getUser()
                ->getConfig()
                ->getRenderingProxyHosts()
                ->map(fn(RenderingProxyHost $e) => $e->getHost())
                ->toArray();

            $proxy = $this->renderingProxyAll || \in_array($host, $userHosts);

            if ($proxy) {
                return [
                    str_replace('%u', $url, $this->renderingProxyUrl),
                    // This callback will post-process the output of Graby\fetchContent()
                    function ($fetchedContent) use ($url) {
                        $fetchedContent['url'] = $url;
                        $fetchedContent['html'] = $this->fixClientSideRenderedProxyResponse($fetchedContent['html']);
                        return $fetchedContent;
                    },
                ];
            }
        }

        return [$url, null];
    }

    /**
     * Checks if URL is an external rendering proxy one
     */
    public function ownsUrl(string $url): bool
    {
        if ($this->isEnabled()) {
            $rendering_proxy_host = preg_replace('~/.*$~', '', $this->renderingProxyUrl);
            return str_starts_with($url, $rendering_proxy_host);
        }

        return false;
    }

    /**
     * Return timeout for externally rendered pages
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
     *
     * @param string $content
     *
     * @return string
     */
    public function fixClientSideRenderedProxyResponse(string $content): string
    {
        $content = preg_replace('/&lt;img ([^&]+)&gt;/', '<img \1 >', $content);

        return $content;
    }
}
