<?php

namespace Wallabag\OpenApi\Attribute\PagerFanta;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class JsonContent extends OA\JsonContent
{
    public function __construct(string|array|null $modelClass = null)
    {
        parent::__construct(
            properties: [
                new OA\Property(
                    property: '_embedded',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: new Model(type: $modelClass))
                        )
                    ]
                ),
                new OA\Property(property: 'page', type: 'integer'),
                new OA\Property(property: 'limit', type: 'integer'),
                new OA\Property(property: 'pages', type: 'integer'),
                new OA\Property(property: 'total', type: 'integer'),
                new OA\Property(
                    property: '_links',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'self',
                            type: 'object',
                            properties: [new OA\Property(property: 'href', type: 'string')]
                        ),
                        new OA\Property(
                            property: 'first',
                            type: 'object',
                            properties: [new OA\Property(property: 'href', type: 'string')]
                        ),
                        new OA\Property(
                            property: 'last',
                            type: 'object',
                            properties: [new OA\Property(property: 'href', type: 'string')]
                        ),
                        new OA\Property(
                            property: 'next',
                            type: 'object',
                            properties: [new OA\Property(property: 'href', type: 'string')]
                        )
                    ]
                )
            ]
        );
    }
}
