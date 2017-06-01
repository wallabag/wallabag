<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Tools\Utils;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;

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
     * Update entry using either fetched or provided content.
     *
     * @param Entry  $entry                Entry to update
     * @param string $url                  Url of the content
     * @param array  $content              Array with content provided for import with AT LEAST keys title, html, url to skip the fetchContent from the url
     * @param bool   $disableContentUpdate Whether to skip trying to fetch content using Graby
     */
    public function updateEntry(Entry $entry, $url, array $content = [], $disableContentUpdate = false)
    {
        if (!empty($content['html'])) {
            $content['html'] = $this->graby->cleanupHtml($content['html'], $url);
        }

        if ((empty($content) || false === $this->validateContent($content)) && false === $disableContentUpdate) {
            $fetchedContent = $this->graby->fetchContent($url);

            // when content is imported, we have information in $content
            // in case fetching content goes bad, we'll keep the imported information instead of overriding them
            if (empty($content) || $fetchedContent['html'] !== $this->fetchingErrorMessage) {
                $content = $fetchedContent;
            }
        }

        // be sure to keep the url in case of error
        // so we'll be able to refetch it in the future
        $content['url'] = !empty($content['url']) ? $content['url'] : $url;

        $this->stockEntry($entry, $content);
    }

    /**
     * Stock entry with fetched or imported content.
     * Will fall back to OpenGraph data if available.
     *
     * @param Entry $entry   Entry to stock
     * @param array $content Array with at least title, url & html
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
                $this->logger->warning('Error while defining date', ['e' => $e, 'url' => $content['url'], 'date' => $content['date']]);
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
     *
     * @return bool true if valid otherwise false
     */
    private function validateContent(array $content)
    {
        return !empty($content['title']) && !empty($content['html']) && !empty($content['url']);
    }
}
