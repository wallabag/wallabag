<?php

namespace Wallabag\Helper;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait to handle created & updated date of an Entity.
 */
trait EntityTimestampsTrait
{
    /**
     * Dates which were explicitly set (when importing data from an other service, for example)
     * are kept as-is.
     */
    #[ORM\PrePersist]
    public function timestamps(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        if (null === $this->updatedAt) {
            $this->updatedAt = new \DateTime();
        }
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
