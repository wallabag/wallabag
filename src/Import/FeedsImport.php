<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\FeedUrlValidator;

class FeedsImport extends AbstractImport
{
    public function importItem(User $user, string $url, ?string $title): void
    {
        $previousUser = $this->user;
        $this->setUser($user);

        try {
            $this->parseEntries([['url' => $url, 'title' => $title]]);
        } finally {
            $this->user = $previousUser;
        }
    }

    public function validateEntry(array $importedEntry): bool
    {
        $url = $importedEntry['url'] ?? null;

        return \is_string($url) && FeedUrlValidator::isValid($url);
    }

    public function parseEntry(array $importedEntry): ?Entry
    {
        $url = $importedEntry['url'];
        $repository = $this->em->getRepository(Entry::class);
        if (false !== $repository->findByUrlAndUserId($url, $this->user->getId())) {
            ++$this->skippedEntries;

            return null;
        }

        $entry = new Entry($this->user);
        $entry->setUrl($url);
        $entry->setTitle($importedEntry['title'] ?? null);
        $this->fetchContent($entry, $url, ['url' => $url, 'title' => $entry->getTitle()]);
        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }
}
