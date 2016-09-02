<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Helper\ContentProxy;

class BrowserImport implements ImportInterface
{
    protected $user;
    protected $em;
    protected $logger;
    protected $contentProxy;
    protected $skippedEntries = 0;
    protected $importedEntries = 0;
    protected $totalEntries = 0;
    protected $filepath;
    protected $markAsRead;
    private $nbEntries;

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
     *
     * @return $this
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
        return 'Firefox & Google Chrome';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_browser';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.browser.description';
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
            $this->logger->error('WallabagImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            return false;
        }

        $this->nbEntries = 1;
        $this->parseEntries($data);
        $this->em->flush();

        return true;
    }

    private function parseEntries($data)
    {
        foreach ($data as $importedEntry) {
            $this->parseEntry($importedEntry);
        }
        $this->totalEntries += count($data);
    }

    private function parseEntry($importedEntry)
    {
        if (!is_array($importedEntry)) {
            return;
        }

        /* Firefox uses guid while Chrome uses id */

        if ((!key_exists('guid', $importedEntry) || (!key_exists('id', $importedEntry))) && is_array(reset($importedEntry))) {
            $this->parseEntries($importedEntry);

            return;
        }
        if (key_exists('children', $importedEntry)) {
            $this->parseEntries($importedEntry['children']);

            return;
        }
        if (key_exists('uri', $importedEntry) || key_exists('url', $importedEntry)) {

            /* Firefox uses uri while Chrome uses url */

            $firefox = key_exists('uri', $importedEntry);

            $existingEntry = $this->em
                ->getRepository('WallabagCoreBundle:Entry')
                ->findByUrlAndUserId(($firefox) ? $importedEntry['uri'] : $importedEntry['url'], $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;

                return;
            }

            if (false === parse_url(($firefox) ? $importedEntry['uri'] : $importedEntry['url']) || false === filter_var(($firefox) ? $importedEntry['uri'] : $importedEntry['url'], FILTER_VALIDATE_URL)) {
                $this->logger->warning('Imported URL '.($firefox) ? $importedEntry['uri'] : $importedEntry['url'].' is not valid');
                ++$this->skippedEntries;

                return;
            }

            try {
                $entry = $this->contentProxy->updateEntry(
                    new Entry($this->user),
                    ($firefox) ? $importedEntry['uri'] : $importedEntry['url']
                );
            } catch (\Exception $e) {
                $this->logger->warning('Error while saving '.($firefox) ? $importedEntry['uri'] : $importedEntry['url']);
                ++$this->skippedEntries;

                return;
            }

            $entry->setArchived($this->markAsRead);

            $this->em->persist($entry);
            ++$this->importedEntries;

            // flush every 20 entries
            if (($this->nbEntries % 20) === 0) {
                $this->em->flush();
                $this->em->clear($entry);
            }
            ++$this->nbEntries;
        }
    }

    /**
     * Set whether articles must be all marked as read.
     *
     * @param bool $markAsRead
     *
     * @return $this
     */
    public function setMarkAsRead($markAsRead)
    {
        $this->markAsRead = $markAsRead;

        return $this;
    }

    /**
     * Set file path to the json file.
     *
     * @param string $filepath
     *
     * @return $this
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
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
}
