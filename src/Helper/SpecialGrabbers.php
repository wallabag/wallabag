<?php

namespace Wallabag\Helper;

use Wallabag\Entity\Config;

/** Fixed, audited rules only: no user regex, endpoint or executable configuration. */
class SpecialGrabbers
{
    public static function mediumUrl(string $url): bool
    {
        $p = parse_url($url);
        if (!$p || \strlen($url) > 2048 || !\in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true)
            || isset($p['user']) || isset($p['pass']) || isset($p['port']) || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }
        $host = strtolower($p['host'] ?? '');
        if ('medium.com' !== $host && !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+medium\.com$/D', $host)) {
            return false;
        }

        return 1 === preg_match('~^/(?:p/[a-f0-9]{12}|(?:@[A-Za-z0-9_.-]+/)?[^/]+-[a-f0-9]{12})/?$~D', $p['path'] ?? '');
    }

    public function list(Config $config): array
    {
        return [
            ['id' => 'x', 'name' => 'X posts and Articles', 'priority' => 10, 'enabled' => $config->isXGrabberEnabled(), 'article_reader_enabled' => $config->isXArticleReaderEnabled(), 'match' => 'Exact x.com / twitter.com post hosts and numeric /status/ IDs.', 'service' => $config->isXArticleReaderEnabled() ? 'Public FxTwitter Article API, then X public oEmbed' : 'X public oEmbed', 'status' => $config->isXGrabberEnabled() ? 'Enabled; embeds are partial. Article reader requires consent.' : 'Disabled; standard Wallabag extraction applies.', 'privacy' => 'Article consent shares only the public post ID and server IP with FxTwitter. No account tokens or private saved content are sent.'],
            ['id' => 'medium', 'name' => 'Medium public stories', 'priority' => 20, 'enabled' => $config->isMediumGrabberEnabled(), 'match' => 'medium.com and its subdomains, story paths ending in a 12-digit hexadecimal ID or /p/ID.', 'service' => 'Direct public HTTP response and local Graby extraction', 'status' => $config->isMediumGrabberEnabled() ? 'Enabled; public-only snapshot, completeness unverified.' : 'Disabled; standard Wallabag extraction applies.', 'privacy' => 'No mirror, credentials or membership access. The former rss-wallabag Freedium rewrite is not used.'],
        ];
    }

    public function update(Config $config, string $id, array $data): void
    {
        if (!\in_array($id, ['x', 'medium'], true) || !\is_bool($data['enabled'] ?? null)
            || array_diff(array_keys($data), 'x' === $id ? ['enabled', 'article_reader_enabled'] : ['enabled'])
            || (array_key_exists('article_reader_enabled', $data) && !\is_bool($data['article_reader_enabled']))) {
            throw new \InvalidArgumentException('Choose x or medium and provide boolean enabled; x also accepts boolean article_reader_enabled.');
        }
        if ('medium' === $id) {
            $config->setMediumGrabberEnabled($data['enabled']);
        } else {
            $config->setXGrabberEnabled($data['enabled']);
            if (isset($data['article_reader_enabled'])) {
                $config->setXArticleReaderEnabled($data['article_reader_enabled']);
            }
        }
    }
}
