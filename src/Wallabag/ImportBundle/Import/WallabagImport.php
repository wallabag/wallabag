<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Helper\ContentProxy;

abstract class WallabagImport implements ImportInterface
{
    protected $user;
    protected $em;
    protected $logger;
    protected $contentProxy;
    protected $skippedEntries = 0;
    protected $importedEntries = 0;
    protected $filepath;
    protected $markAsRead;
    // untitled in all languages from v1
    protected $untitled = [
        'Untitled',
        'Sans titre',
        'podle nadpisu',
        'Sin título',
        'با عنوان',
        'per titolo',
        'Sem título',
        'Без названия',
        'po naslovu',
        'Без назви',
        'No title found',
        '',
    ];

    public function __construct(EntityManager $em, ContentProxy $contentProxy)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
        $this->contentProxy = $contentProxy;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    abstract public function getUrl();

    /**
     * {@inheritdoc}
     */
    abstract public function getDescription();

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('WallabagImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('WallabagImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            return false;
        }

        $this->parseEntries($data);

        return true;
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
                ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;
                continue;
            }

            $data = $this->prepareEntry($importedEntry, $this->markAsRead);

            $entry = $this->contentProxy->updateEntry(
                new Entry($this->user),
                $importedEntry['url'],
                $data
            );

            if (array_key_exists('tags', $data)) {
                $this->contentProxy->assignTagsToEntry(
                    $entry,
                    $data['tags']
                );
            }

            if (isset($importedEntry['preview_picture'])) {
                $entry->setPreviewPicture($importedEntry['preview_picture']);
            }

            $entry->setArchived($data['is_archived']);
            $entry->setStarred($data['is_starred']);

            $this->em->persist($entry);
            ++$this->importedEntries;

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            ++$i;
        }

        $this->em->flush();
    }

    /**
     * This should return a cleaned array for a given entry to be given to `updateEntry`.
     *
     * @param array $entry      Data from the imported file
     * @param bool  $markAsRead Should we mark as read content?
     *
     * @return array
     */
    abstract protected function prepareEntry($entry = [], $markAsRead = false);
}
