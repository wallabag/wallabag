<?php

namespace Wallabag\MailImport;

class MessageParser
{
    /**
     * @param string[] $recipients
     */
    public function getToken(string $importAddress, array $recipients, string $rawHeader = ''): ?string
    {
        [$local, $domain] = explode('@', $importAddress, 2) + ['', ''];
        $pattern = '/^' . preg_quote($local, '/') . '\+([a-z0-9]+)@' . preg_quote($domain, '/') . '$/iD';

        preg_match_all('/^(?:Delivered-To|X-Original-To):\s*<?([^\s>]+)/mi', (string) preg_replace('/\r?\n[ \t]+/', ' ', $rawHeader), $matches);

        foreach (array_merge($recipients, $matches[1]) as $recipient) {
            if (preg_match($pattern, trim($recipient), $match)) {
                return strtolower($match[1]);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getUrls(string $text, string $html): array
    {
        $urls = $this->getUrlsFromText($text);

        if ([] === $urls) {
            $urls = $this->getUrlsFromHtml($html);
        }

        return array_values(array_unique($urls));
    }

    /**
     * @return string[]
     */
    private function getUrlsFromHtml(string $html): array
    {
        if ('' === trim($html)) {
            return [];
        }

        $urls = [];
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($document->getElementsByTagName('a') as $link) {
            $urls[] = $link->getAttribute('href');
        }

        return array_values(array_filter($urls, $this->isValidUrl(...)));
    }

    /**
     * @return string[]
     */
    private function getUrlsFromText(string $text): array
    {
        preg_match_all('#https?://[^\s<>"\x00-\x20]+#i', $text, $matches);

        $urls = [];

        foreach ($matches[0] as $url) {
            $url = rtrim($url, '.,;!?');

            foreach ([')' => '(', ']' => '['] as $closing => $opening) {
                while (str_ends_with($url, $closing) && substr_count($url, $closing) > substr_count($url, $opening)) {
                    $url = substr($url, 0, -1);
                }
            }
            $urls[] = $url;
        }

        return array_values(array_filter($urls, $this->isValidUrl(...)));
    }

    private function isValidUrl(string $url): bool
    {
        return false !== filter_var($url, \FILTER_VALIDATE_URL) && \in_array(strtolower((string) parse_url($url, \PHP_URL_SCHEME)), ['http', 'https'], true);
    }
}
