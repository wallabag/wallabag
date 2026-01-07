<?php

namespace Wallabag\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Removed type from DBAL in v3.
 * The type is no more used, but we must keep it in order to avoid error during migrations.
 *
 * @see https://github.com/doctrine/dbal/commit/6ed32a9a941acf0cb6ad384b84deb8df68ca83f8
 * @see https://dunglas.dev/2022/01/json-columns-and-doctrine-dbal-3-upgrade/
 */
class JsonArrayType extends JsonType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value || '' === $value) {
            return [];
        }

        $value = \is_resource($value) ? stream_get_contents($value) : $value;

        return json_decode((string) $value, true);
    }

    public function getName(): string
    {
        return 'json_array';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
