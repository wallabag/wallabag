<?php

namespace Wallabag\Helper;

/** Public embed snapshots deliberately never claim to be a complete post. */
class XPostExtractor
{
    private PublicHttpClient $http;

    public function __construct(PublicHttpClient $http) { $this->http = $http; }

    public static function canonicalUrl(string $url): ?string
    {
        $p = parse_url($url);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true)
            || !in_array(strtolower($p['host'] ?? ''), ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'], true)
            || isset($p['user']) || isset($p['pass']) || isset($p['port'])
            || !preg_match('~^/(?:[A-Za-z0-9_]{1,15}|i/web)/status/(\d{1,25})(?:/[^?#]*)?$~', $p['path'] ?? '', $m)) {
            return null;
        }

        return 'https://x.com/i/status/' . $m[1];
    }

    public function extract(string $url): array
    {
        $canonical = self::canonicalUrl($url);
        if (null === $canonical) { throw new \InvalidArgumentException('Invalid X post URL.'); }
        $status = 206;
        $html = '<p><strong>Partial X capture.</strong> Public embed content may omit long text, articles, threads, quoted posts and media. Open the original to verify completeness.</p>';
        $author = 'X';
        try {
            $response = $this->http->get('https://publish.twitter.com/oembed?' . http_build_query(['url' => $canonical, 'omit_script' => 'true', 'dnt' => 'true']));
            $data = json_decode($response['body'], true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($data) || !is_string($data['html'] ?? null) || !trim(strip_tags($data['html']))) {
                throw new \RuntimeException('Empty embed.');
            }
            $author = is_string($data['author_name'] ?? null) ? $data['author_name'] : 'X';
            $html .= $this->sanitizeEmbed($data['html']);
        } catch (\Exception $e) {
            $status = 502;
            $html = '<p><strong>X capture unavailable.</strong> The public embed could not be retrieved. This is a bookmark, not a captured post. Retry later or open the original. No authentication or access restrictions were bypassed.</p>';
        }
        $html .= '<p><a rel="noreferrer noopener" href="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . '">Original post on X</a></p>';

        return ['title' => $author . ' — X post (' . (206 === $status ? 'partial capture' : 'unavailable') . ')', 'html' => $html, 'url' => $canonical, 'status' => $status, 'authors' => [$author]];
    }

    public function sanitizeEmbed(string $html): string
    {
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_NONET);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        $render = function (\DOMNode $node) use (&$render): string {
            if ($node instanceof \DOMText) { return htmlspecialchars($node->textContent, ENT_QUOTES, 'UTF-8'); }
            if (!$node instanceof \DOMElement) { return ''; }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'form', 'img', 'video'], true)) { return ''; }
            $inside = '';
            foreach ($node->childNodes as $child) { $inside .= $render($child); }
            if ('a' === $tag) {
                $href = $node->getAttribute('href');
                $p = parse_url($href);
                if ('https' === ($p['scheme'] ?? '') && !isset($p['user']) && !isset($p['port'])
                    && in_array(strtolower($p['host'] ?? ''), ['x.com', 'twitter.com', 't.co'], true)) {
                    return '<a rel="noreferrer noopener" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . $inside . '</a>';
                }
                return $inside;
            }
            if ('br' === $tag) { return '<br>'; }
            return in_array($tag, ['p', 'blockquote', 'strong', 'em'], true) ? '<' . $tag . '>' . $inside . '</' . $tag . '>' : $inside;
        };
        return $render($doc->documentElement);
    }
}
