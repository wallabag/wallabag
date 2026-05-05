<?php

namespace Wallabag\Entity\Api;

use OpenApi\Annotations as OA;

class ApplicationInfo
{
    /**
     * @var string
     * @OA\Property(
     *      description="Name of the application.",
     *      type="string",
     *      example="wallabag",
     * )
     */
    public $appname;

    /**
     * @var string
     * @OA\Property(
     *      description="Version number of the application.",
     *      type="string",
     *      example="2.5.2",
     * )
     */
    public $version;

    /**
     * @var bool
     * @OA\Property(
     *      description="Indicates whether registration is allowed. See PUT /api/user.",
     *      type="boolean"
     * )
     */
    public $allowed_registration;

    /**
     * @var int|null
     * @OA\Property(
     *     description="Retention period in days for soft-deleted entries. Null means entries are never auto-purged.",
     *     type="integer",
     *     nullable=true,
     * )
     */
    public $deleted_entries_expiration_days;

    public function __construct($version, $allowed_registration, $deleted_entries_expiration_days = null)
    {
        $this->appname = 'wallabag';
        $this->version = $version;
        $this->allowed_registration = $allowed_registration;
        $this->deleted_entries_expiration_days = $deleted_entries_expiration_days;
    }
}
