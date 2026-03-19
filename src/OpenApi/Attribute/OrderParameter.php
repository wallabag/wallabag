<?php

namespace Wallabag\OpenApi\Attribute;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
class OrderParameter extends OA\Parameter
{
    public function __construct(
        public readonly string $defaultName = 'order',
        public readonly string $default = 'desc',
    ) {
        parent::__construct(
            name: $defaultName,
            in: 'query',
            description: 'Order of results (asc or desc).',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: $default)
        );
    }
}
