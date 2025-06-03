<?php

namespace Wallabag\Helper;

class EntryDeletionExpirationConfig
{
    private int $expirationDays;

    public function __construct(int $defaultExpirationDays)
    {
        $this->expirationDays = $defaultExpirationDays;
    }

    public function getCutoffDate(): \DateTime
    {
        return new \DateTime("-{$this->expirationDays} days");
    }

    public function getExpirationDays(): int
    {
        return $this->expirationDays;
    }

    /**
     * Override the expiration days parameter.
     * This is mostly useful for testing purposes and should not be used in other contexts.
     */
    public function setExpirationDays(int $days): self
    {
        $this->expirationDays = $days;
        return $this;
    }
}
