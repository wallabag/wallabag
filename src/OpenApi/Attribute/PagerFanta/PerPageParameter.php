<?php

namespace Wallabag\OpenApi\Attribute\PagerFanta;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PerPageParameter extends OA\Parameter
{
    public function __construct(
        public readonly string $defaultName = 'perPage',
        public readonly int $default = 30,
    ) {
        parent::__construct(
            name: $defaultName,
            in: 'query',
            description: 'Number of items per page.',
            required: false,
            schema: new OA\Schema(type: 'integer', default: $default)
        );
    }
}
