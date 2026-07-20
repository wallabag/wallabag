<?php

namespace Wallabag\HttpClient;

final class HostnameDenyList
{
    private array $hostnames = [];
    private array $subtrees = [];

    public function __construct(array $hostnames = [])
    {
        foreach ($hostnames as $hostname) {
            // Symfony's CSV env processor represents an empty value as [null].
            if (null === $hostname) {
                continue;
            }

            if (!\is_string($hostname)) {
                throw new \InvalidArgumentException('Blocked hostname entries must be strings.');
            }

            $hostname = trim($hostname);

            if ('' === $hostname) {
                continue;
            }

            $isSubtree = str_starts_with($hostname, '.');
            $hostname = $this->normalizeConfiguredHostname($isSubtree ? substr($hostname, 1) : $hostname);

            if ($isSubtree) {
                if (false !== filter_var($hostname, \FILTER_VALIDATE_IP)) {
                    throw new \InvalidArgumentException('Leading-dot rules cannot contain IP literals.');
                }

                $this->subtrees[$hostname] = true;

                continue;
            }

            $this->hostnames[$hostname] = true;
        }
    }

    public function isEmpty(): bool
    {
        return [] === $this->hostnames && [] === $this->subtrees;
    }

    public function getBlockedHostname(string $hostname): ?string
    {
        $hostname = $this->normalizeHostname($hostname);

        if (null === $hostname) {
            return null;
        }

        if (isset($this->hostnames[$hostname])) {
            return $hostname;
        }

        foreach (array_keys($this->subtrees) as $subtree) {
            if ($hostname === $subtree || str_ends_with($hostname, '.' . $subtree)) {
                return $hostname;
            }
        }

        return null;
    }

    private function normalizeConfiguredHostname(string $hostname): string
    {
        if (str_starts_with($hostname, '.')) {
            throw new \InvalidArgumentException(\sprintf('Invalid blocked hostname "%s": leading dots are not allowed.', $hostname));
        }

        if (str_contains($hostname, '://') || strpbrk($hostname, "/\\?#@*[](){}^$+|\t\r\n ")) {
            throw new \InvalidArgumentException(\sprintf('Invalid blocked hostname "%s".', $hostname));
        }

        if (false !== filter_var($hostname, \FILTER_VALIDATE_IP)) {
            return $this->normalizeIpAddress($hostname);
        }

        if (str_contains($hostname, ':')) {
            throw new \InvalidArgumentException(\sprintf('Invalid blocked hostname "%s": ports are not allowed.', $hostname));
        }

        $normalized = $this->normalizeHostname($hostname);

        if (null === $normalized || false === filter_var($normalized, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException(\sprintf('Invalid blocked hostname "%s".', $hostname));
        }

        return $normalized;
    }

    private function normalizeHostname(string $hostname): ?string
    {
        if (str_starts_with($hostname, '[') && str_ends_with($hostname, ']')) {
            $hostname = substr($hostname, 1, -1);
        }

        $hostname = rtrim(mb_strtolower($hostname, 'UTF-8'), '.');

        if ('' === $hostname) {
            return null;
        }

        if (false !== filter_var($hostname, \FILTER_VALIDATE_IP)) {
            return $this->normalizeIpAddress($hostname);
        }

        $hostname = idn_to_ascii($hostname, \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46);

        return false === $hostname ? null : strtolower($hostname);
    }

    private function normalizeIpAddress(string $hostname): string
    {
        return inet_ntop(inet_pton($hostname));
    }
}
