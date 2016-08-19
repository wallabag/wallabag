<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Entity\Entry;

abstract class AbstractImport implements ImportInterface
{
    protected $em;
    protected $logger;
    protected $contentProxy;

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
     * Fetch content from the ContentProxy (using graby).
     * If it fails return false instead of the updated entry.
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     * @param array  $content An array with AT LEAST keys title, html, url, language & content_type to skip the fetchContent from the url
     *
     * @return Entry|false
     */
    protected function fetchContent(Entry $entry, $url, array $content = [])
    {
        try {
            return $this->contentProxy->updateEntry($entry, $url, $content);
        } catch (\Exception $e) {
            return false;
        }
    }
}
