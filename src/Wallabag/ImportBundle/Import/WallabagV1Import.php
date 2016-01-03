<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Tools\Utils;

class WallabagV1Import implements ImportInterface
{
    private $user;
    private $em;
    private $logger;
    private $skippedEntries = 0;
    private $importedEntries = 0;
    private $filepath;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
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
        return 'Wallabag v1';
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
        return 'This importer will import all your wallabag v1 articles. On your config page, click on "JSON export" in the "Export your wallabag data" section. You will have a "wallabag-export-1-xxxx-xx-xx.json" file.';
    }

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('WallabagV1Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('WallabagV1Import: unable to read file', array('filepath' => $this->filepath));

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
     * @param $entries
     */
    private function parseEntries($entries)
    {
        $i = 1;

        foreach ($entries as $importedEntry) {
            $existingEntry = $this->em
                ->getRepository('WallabagCoreBundle:Entry')
                ->existByUrlAndUserId($importedEntry['url'], $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;
                continue;
            }

            // @see ContentProxy->updateEntry
            $entry = new Entry($this->user);
            $entry->setUrl($importedEntry['url']);
            $entry->setTitle($importedEntry['title']);
            $entry->setArchived($importedEntry['is_read']);
            $entry->setStarred($importedEntry['is_fav']);
            $entry->setContent($importedEntry['content']);
            $entry->setReadingTime(Utils::getReadingTime($importedEntry['content']));
            $entry->setDomainName(parse_url($importedEntry['url'], PHP_URL_HOST));

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
