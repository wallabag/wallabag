<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Tools\Utils;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * This kind of proxy class take care of getting the content from an url
 * and update the entry with what it found.
 */
class ContentProxy
{
    protected $graby;
    protected $tagger;
    protected $logger;
    protected $mimeGuesser;
    protected $fetchingErrorMessage;
    protected $eventDispatcher;

    public function __construct(Graby $graby, RuleBasedTagger $tagger, LoggerInterface $logger, $fetchingErrorMessage)
    {
        $this->graby = $graby;
        $this->tagger = $tagger;
        $this->logger = $logger;
        $this->mimeGuesser = new MimeTypeExtensionGuesser();
        $this->fetchingErrorMessage = $fetchingErrorMessage;
    }

    /**
     * Update existing entry by fetching from URL using Graby.
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     */
    public function updateEntry(Entry $entry, $url)
    {
        $content = $this->graby->fetchContent($url);

        $this->stockEntry($entry, $content);
    }

    /**
     * Import entry using either fetched or provided content.
     *
     * @param Entry  $entry                Entry to update
     * @param array  $content              Array with content provided for import with AT LEAST keys title, html, url to skip the fetchContent from the url
     * @param bool   $disableContentUpdate Whether to skip trying to fetch content using Graby
     */
    public function importEntry(Entry $entry, array $content, $disableContentUpdate = false)
    {
        $this->validateContent($content);

        if (false === $disableContentUpdate) {
            try {
                $fetchedContent = $this->graby->fetchContent($content['url']);
            } catch (\Exception $e) {
                $this->logger->error('Error while trying to fetch content from URL.', [
                    'entry_url' => $content['url'],
                    'error_msg' => $e->getMessage(),
                ]);
            }

            // when content is imported, we have information in $content
            // in case fetching content goes bad, we'll keep the imported information instead of overriding them
            if ($fetchedContent['html'] !== $this->fetchingErrorMessage) {
                $content = $fetchedContent;
            }
        }

        $this->stockEntry($entry, $content);
    }

    /**
     * Stock entry with fetched or imported content.
     * Will fall back to OpenGraph data if available.
     *
     * @param Entry  $entry   Entry to stock
     * @param array  $content Array with at least title and URL
     */
    private function stockEntry(Entry $entry, array $content)
    {
        $title = $content['title'];
        if (!$title && !empty($content['open_graph']['og_title'])) {
            $title = $content['open_graph']['og_title'];
        }

        $html = $content['html'];
        if (false === $html) {
            $html = $this->fetchingErrorMessage;

            if (!empty($content['open_graph']['og_description'])) {
                $html .= '<p><i>But we found a short description: </i></p>';
                $html .= $content['open_graph']['og_description'];
            }
        }

        $entry->setUrl($content['url']);
        $entry->setTitle($title);
        $entry->setContent($html);
        $entry->setHttpStatus(isset($content['status']) ? $content['status'] : '');

        if (!empty($content['date'])) {
            $date = $content['date'];

            // is it a timestamp?
            if (filter_var($date, FILTER_VALIDATE_INT) !== false) {
                $date = '@'.$content['date'];
            }

            try {
                $entry->setPublishedAt(new \DateTime($date));
            } catch (\Exception $e) {
                $this->logger->warning('Error while defining date', ['e' => $e, 'url' => $url, 'date' => $content['date']]);
            }
        }

        if (!empty($content['authors'])) {
            $entry->setPublishedBy($content['authors']);
        }

        if (!empty($content['all_headers'])) {
            $entry->setHeaders($content['all_headers']);
        }

        $entry->setLanguage(isset($content['language']) ? $content['language'] : '');
        $entry->setMimetype(isset($content['content_type']) ? $content['content_type'] : '');
        $entry->setReadingTime(Utils::getReadingTime($html));

        $domainName = parse_url($entry->getUrl(), PHP_URL_HOST);
        if (false !== $domainName) {
            $entry->setDomainName($domainName);
        }

        if (!empty($content['open_graph']['og_image'])) {
            $entry->setPreviewPicture($content['open_graph']['og_image']);
        }

        // if content is an image define as a preview too
        if (!empty($content['content_type']) && in_array($this->mimeGuesser->guess($content['content_type']), ['jpeg', 'jpg', 'gif', 'png'], true)) {
            $entry->setPreviewPicture($content['url']);
        }

        try {
            $this->tagger->tag($entry);
        } catch (\Exception $e) {
            $this->logger->error('Error while trying to automatically tag an entry.', [
                'entry_url' => $content['url'],
                'error_msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate that the given content has at least a title, an html and a url.
     *
     * @param array $content
     */
    private function validateContent(array $content)
    {
        if (!empty($content['title']))) {
            throw new Exception('Missing title from imported entry!');
        }

        if (!empty($content['url']))) {
            throw new Exception('Missing URL from imported entry!');
        }

        if (!empty($content['html']))) {
            throw new Exception('Missing html from imported entry!');
        }
    }
}
