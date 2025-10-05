<?php

namespace Wallabag\Helper;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait to handle created & updated date of an Entity.
 */
trait EntityTimestampsTrait
{
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function timestamps()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }
}
