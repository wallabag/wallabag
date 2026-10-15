<?php

namespace Wallabag\Rss;

use Wallabag\Helper\PublicHttpClient;

class FeedParser
{
    public function parse(string $xml, string $base, int $limit): array
    {
        if (\strlen($xml) > 2 * 1024 * 1024 || false !== stripos($xml, '<!DOCTYPE') || false !== stripos($xml, '<!ENTITY')) {
            throw new \RuntimeException('Feed exceeds size limit or contains a forbidden DTD/entity.');
        }
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $ok = $doc->loadXML($xml, \LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$ok || !\in_array($doc->documentElement->localName, ['rss', 'feed', 'RDF'], true)) {
            throw new \RuntimeException('Invalid RSS/Atom feed.');
        }
        $xp = new \DOMXPath($doc);
        $nodes = $xp->query('/*[local-name()="rss"]/*[local-name()="channel"]/*[local-name()="item"] | /*[local-name()="feed"]/*[local-name()="entry"] | /*[local-name()="RDF"]/*[local-name()="item"]');
        $items = [];
        foreach ($nodes as $node) {
            if (\count($items) >= $limit) {
                break;
            }
            $text = static function (string $name) use ($xp, $node): string {
                return trim($xp->evaluate('string(./*[local-name()="' . $name . '"][1])', $node));
            };
            $linkNode = $xp->query('./*[local-name()="link"][1]', $node)->item(0);
            $url = $text('link');
            if ('entry' === $node->localName) {
                $linkNode = $xp->query('./*[local-name()="link" and (not(@rel) or @rel="alternate")][1]', $node)->item(0);
                $url = $linkNode ? $linkNode->getAttribute('href') : '';
            }
            if (!$url) {
                continue;
            }
            $itemBase = $base;
            // XML base is inherited, from document to entry to link.
            $ancestors = [];
            for ($ancestor = $linkNode ?: $node; $ancestor instanceof \DOMElement; $ancestor = $ancestor->parentNode) {
                array_unshift($ancestors, $ancestor);
            }
            foreach ($ancestors as $ancestor) {
                if ($ancestor->hasAttributeNS('http://www.w3.org/XML/1998/namespace', 'base')) {
                    $itemBase = PublicHttpClient::normalize($itemBase, $ancestor->getAttributeNS('http://www.w3.org/XML/1998/namespace', 'base'));
                }
            }
            $tags = [];
            foreach ($xp->query('./*[local-name()="category"]', $node) as $category) {
                $tags[] = $category->getAttribute('term') ?: trim($category->textContent);
            }
            $date = $text('pubDate') ?: $text('published') ?: $text('updated') ?: $text('date');
            $items[] = ['url' => PublicHttpClient::normalize($itemBase, $url), 'title' => $text('title'), 'tags' => array_filter($tags), 'date' => $date];
        }

        return $items;
    }
}
