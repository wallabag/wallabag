<?php

namespace Wallabag\MailImport;

class MailImportSettings
{
    public function __construct(
        private readonly string $address,
        private readonly string $dsn,
        private readonly int $maxLinks,
    ) {
    }

    public function isEnabled(): bool
    {
        return str_contains($this->address, '@') && '' !== $this->dsn;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getMaxLinks(): int
    {
        return $this->maxLinks;
    }

    public function getUserAddress(string $token): string
    {
        [$local, $domain] = explode('@', $this->address, 2) + ['', ''];

        return $local . '+' . $token . '@' . $domain;
    }
}
