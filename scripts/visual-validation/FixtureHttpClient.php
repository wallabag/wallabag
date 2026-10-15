<?php

/** Loaded only by the generated, isolated validation configuration. */
class WallabagVisualFixtureHttpClient extends \Wallabag\CoreBundle\Helper\PublicHttpClient
{
    protected function resolve(string $host): array
    {
        if ('feeds.example.org' === $host) {
            return ['93.184.216.34'];
        }

        throw new \RuntimeException('Unexpected validation DNS lookup: ' . $host);
    }

    public function get(string $url): array
    {
        if ('1' !== getenv('WALLABAG_ISOLATED_VISUAL')) {
            throw new \RuntimeException('Synthetic HTTP fixture requires isolated validation mode.');
        }
        if ('https://feeds.example.org/rss' === $url) {
            $body = '<rss version="2.0"><channel><title>Synthetic feed</title><item><title>Synthetic feed entry</title><link>https://feeds.example.org/article</link><guid>synthetic-1</guid></item></channel></rss>';
        } elseif ('https://feeds.example.org/article' === $url) {
            $body = '<html><head><title>Synthetic feed entry</title></head><body><article><h1>Synthetic feed entry</h1><p>' . str_repeat('A synthetic feed paragraph for isolated validation. ', 20) . '</p></article></body></html>';
        } elseif ('https://api.fxtwitter.com/2/status/12345' === $url) {
            $body = json_encode(['code' => 200, 'status' => ['id' => '12345', 'author' => ['name' => 'Synthetic author', 'protected' => false], 'article' => ['title' => 'Synthetic visual article', 'content' => ['blocks' => [['type' => 'unstyled', 'text' => 'Synthetic opening paragraph.'], ['type' => 'unstyled', 'text' => 'Synthetic closing paragraph.']], 'entityMap' => []]]]]);
        } elseif (0 === strpos($url, 'https://publish.twitter.com/oembed?') && false !== strpos($url, '12346')) {
            $body = '{"author_name":"Synthetic author","html":"<p>Synthetic public excerpt.</p>"}';
        } else {
            throw new \RuntimeException('No network permitted; missing synthetic response for ' . $url);
        }

        return ['url' => $url, 'body' => $body, 'status' => 200];
    }
}
