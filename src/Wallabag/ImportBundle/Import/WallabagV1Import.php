<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Tools\Utils;
use Wallabag\CoreBundle\Helper\ContentProxy;

class WallabagV1Import implements ImportInterface
{
    protected $user;
    protected $em;
    protected $logger;
    protected $contentProxy;
    protected $skippedEntries = 0;
    protected $importedEntries = 0;
    protected $filepath;
    protected $markAsRead;

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
    public function getName()
    {
        return 'wallabag v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v1.description';
    }

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
            $this->logger->error('WallabagImport: unable to read file', array('filepath' => $this->filepath));

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
     * @param $entries
     */
    protected function parseEntries($entries)
    {
        $i = 1;

        //Untitled in all languages from v1. This should never have been translated
        $untitled = array('Untitled', 'Sans titre', 'podle nadpisu', 'Sin título', 'با عنوان', 'per titolo', 'Sem título', 'Без названия', 'po naslovu', 'Без назви', 'No title found', '');

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

            if (in_array($importedEntry['title'], $untitled)) {
                $entry = $this->contentProxy->updateEntry($entry, $importedEntry['url']);
            } else {
                $entry->setContent($importedEntry['content']);
                $entry->setTitle($importedEntry['title']);
                $entry->setReadingTime(Utils::getReadingTime($importedEntry['content']));
                $entry->setDomainName(parse_url($importedEntry['url'], PHP_URL_HOST));
            }

            if (array_key_exists('tags', $importedEntry) && $importedEntry['tags'] != '') {
                $this->contentProxy->assignTagsToEntry(
                    $entry,
                    $importedEntry['tags']
                );
            }

            $entry->setArchived($importedEntry['is_read'] || $this->markAsRead);
            $entry->setStarred($importedEntry['is_fav']);

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
