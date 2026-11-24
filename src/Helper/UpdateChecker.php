<?php

namespace Wallabag\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateChecker
{
    public const RELEASES_URL = 'https://wallabag.org/releases.json';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly string $currentVersion,
        private readonly bool $enabled,
    ) {
    }

    /**
     * @return array{version: string, security: bool, url: string}|null
     */
    public function getAvailableUpdate(): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $newer = array_values(array_filter(
            $this->fetchReleases(),
            fn (array $release): bool => empty($release['prerelease'])
                && \is_string($release['version'] ?? null)
                && version_compare($release['version'], $this->currentVersion, '>')
                && (empty($release['php_min']) || version_compare(\PHP_VERSION, $release['php_min'], '>=')),
        ));

        if ([] === $newer) {
            return null;
        }

        usort($newer, static fn (array $a, array $b): int => version_compare($b['version'], $a['version']));

        return [
            'version' => $newer[0]['version'],
            'security' => [] !== array_filter($newer, static fn (array $release): bool => !empty($release['security'])),
            'url' => $newer[0]['notes_url'] ?? $newer[0]['release_url'] ?? 'https://wallabag.org',
        ];
    }

    private function fetchReleases(): array
    {
        return $this->cache->get('wallabag_releases', function (ItemInterface $item): array {
            $item->expiresAfter(86400);

            try {
                $releases = $this->httpClient->request('GET', self::RELEASES_URL, ['timeout' => 3])->toArray()['releases'] ?? [];

                return \is_array($releases) ? array_filter($releases, 'is_array') : [];
            } catch (\Throwable $e) {
                $this->logger->info('Unable to check the wallabag releases.', ['exception' => $e]);
                $item->expiresAfter(3600);

                return [];
            }
        });
    }
}
