<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class WallabagV2Import extends WallabagV1Import implements ImportInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wallabag v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v2.description';
    }

    /**
     * @param $entries
     */
    protected function parseEntries($entries)
    {
        $i = 1;

        foreach ($entries as $importedEntry) {
            $existingEntry = $this->em
                ->getRepository('WallabagCoreBundle:Entry')
                ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;
                continue;
            }

            // @see ContentProxy->updateEntry
            $entry = new Entry($this->user);
            $entry->setUrl($importedEntry['url']);
            $entry->setTitle($importedEntry['title']);
            $entry->setArchived($importedEntry['is_archived'] || $this->markAsRead);
            $entry->setStarred($importedEntry['is_starred']);
            $entry->setContent($importedEntry['content']);
            $entry->setReadingTime($importedEntry['reading_time']);
            $entry->setDomainName($importedEntry['domain_name']);
            if (isset($importedEntry['mimetype'])) {
                $entry->setMimetype($importedEntry['mimetype']);
            }
            if (isset($importedEntry['language'])) {
                $entry->setLanguage($importedEntry['language']);
            }
            if (isset($importedEntry['preview_picture'])) {
                $entry->setPreviewPicture($importedEntry['preview_picture']);
            }

            $this->em->persist($entry);
            ++$this->importedEntries;

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
            }
            ++$i;
        }

        $this->em->flush();
    }
}
