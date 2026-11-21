<?php

namespace Wallabag\Helper;

class InstanceImageLocator
{
    /**
     * @param string $wallabagUrl Wallabag instance url
     * @param string $projectDir  Wallabag project directory
     */
    public function __construct(
        private $wallabagUrl,
        private $projectDir,
    ) {
    }

    /**
     * Find the file of an image stored by wallabag (see DownloadImages) from its url.
     *
     * @return array{path: string, extension: string, mimetype: string}|null
     */
    public function locate(?string $url): ?array
    {
        if (null === $url) {
            return null;
        }

        $prefix = rtrim($this->wallabagUrl, '/') . '/assets/images/';

        if (!str_starts_with($url, $prefix)) {
            return null;
        }

        $relativePath = rawurldecode(strtok(substr($url, \strlen($prefix)), '?#'));

        if ('' === $relativePath || str_contains($relativePath, "\0")) {
            return null;
        }

        $folder = realpath($this->projectDir . '/web/assets/images');

        if (false === $folder) {
            return null;
        }

        $path = realpath($folder . '/' . $relativePath);

        if (false === $path || !is_file($path)) {
            return null;
        }

        if (!str_starts_with($path, $folder . \DIRECTORY_SEPARATOR)) {
            return null;
        }

        $mimetypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        $extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));

        if (!isset($mimetypes[$extension])) {
            return null;
        }

        return ['path' => $path, 'extension' => $extension, 'mimetype' => $mimetypes[$extension]];
    }
}
