<?php

namespace Wallabag\OpenApi\Attribute\PagerFanta;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PageParameter extends OA\Parameter
{
    public function __construct(
        public readonly string $defaultName = 'page',
        public readonly int $default = 1,
    ) {
        parent::__construct(
            name: $defaultName,
            in: 'query',
            description: 'Requested page number.',
            required: false,
            schema: new OA\Schema(type: 'integer', default: $default)
        );
    }
}
