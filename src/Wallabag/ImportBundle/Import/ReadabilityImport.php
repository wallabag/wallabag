<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;

class ReadabilityImport extends AbstractImport
{
    private $user;
    private $skippedEntries = 0;
    private $importedEntries = 0;
    private $filepath;
    private $markAsRead;

    /**
     * We define the user in a custom call because on the import command there is no logged in user.
     * So we can't retrieve user from the `security.token_storage` service.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Readability';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_readability';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.readability.description';
    }

    /**
     * Set file path to the json file.
     *
     * @param string $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Set whether articles must be all marked as read.
     *
     * @param bool $markAsRead
     */
    public function setMarkAsRead($markAsRead)
    {
        $this->markAsRead = $markAsRead;

        return $this;
    }

    /**
     * Get whether articles must be all marked as read.
     */
    public function getMarkAsRead()
    {
        return $this->markAsRead;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return [
            'skipped' => $this->skippedEntries,
            'imported' => $this->importedEntries,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('ReadabilityImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('ReadabilityImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data) || empty($data['bookmarks'])) {
            return false;
        }

        $this->parseEntries($data['bookmarks']);

        return true;
    }

    /**
     * Parse and insert all given entries.
     *
     * @param $entries
     */
    protected function parseEntries($entries)
    {
        $i = 1;

        foreach ($entries as $importedEntry) {
            $existingEntry = $this->em
                ->getRepository('WallabagCoreBundle:Entry')
                ->findByUrlAndUserId($importedEntry['article__url'], $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;
                continue;
            }

            $data = [
                'title' => $importedEntry['article__title'],
                'url' => $importedEntry['article__url'],
                'content_type' => '',
                'language' => '',
                'is_archived' => $importedEntry['archive'] || $this->markAsRead,
                'is_starred' => $importedEntry['favorite'],
            ];

            $entry = $this->fetchContent(
                new Entry($this->user),
                $data['url'],
                $data
            );

            // jump to next entry in case of problem while getting content
            if (false === $entry) {
                ++$this->skippedEntries;
                continue;
            }
            $entry->setArchived($data['is_archived']);
            $entry->setStarred($data['is_starred']);

            $this->em->persist($entry);
            ++$this->importedEntries;

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
                $this->em->clear($entry);
            }
            ++$i;
        }

        $this->em->flush();
    }
}
