<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\Validator\Constraints\Locale as LocaleConstraint;
use Symfony\Component\Validator\Constraints\Url as UrlConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Tools\Utils;

/**
 * This kind of proxy class take care of getting the content from an url
 * and update the entry with what it found.
 */
class ContentProxy
{
    protected $graby;
    protected $tagger;
    protected $validator;
    protected $logger;
    protected $mimeGuesser;
    protected $fetchingErrorMessage;
    protected $eventDispatcher;
    protected $storeArticleHeaders;

    public function __construct(Graby $graby, RuleBasedTagger $tagger, ValidatorInterface $validator, LoggerInterface $logger, $fetchingErrorMessage, $storeArticleHeaders = false)
    {
        $this->graby = $graby;
        $this->tagger = $tagger;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->mimeGuesser = new MimeTypeExtensionGuesser();
        $this->fetchingErrorMessage = $fetchingErrorMessage;
        $this->storeArticleHeaders = $storeArticleHeaders;
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
            $fetchedContent['title'] = $this->sanitizeContentTitle($fetchedContent['title'], $fetchedContent['content_type']);

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
     * Try to sanitize the title of the fetched content from wrong character encodings and invalid UTF-8 character.
     * @param $title
     * @param $contentType
     * @return string
     */
    private function sanitizeContentTitle($title, $contentType) {
        if ('application/pdf' === $contentType) {
            $convertedTitle = $this->convertPdfEncodingToUTF8($title);
            return $this->sanitizeUTF8Text($convertedTitle);
        }
        return $this->sanitizeUTF8Text($title);
    }

    /**
     * If the title from the fetched content comes from a PDF, then its very possible that the character encoding is not
     * UTF-8. This methods tries to identify the character encoding and translate the title to UTF-8.
     * @param $title
     * @return string (maybe contains invalid UTF-8 character)
     */
    private function convertPdfEncodingToUTF8($title) {
        // first try UTF-16 (then UTF-8) because its easier to detect its present/absence
        foreach (array('UTF-16BE', 'UTF-16LE', 'UTF-8', 'WINDOWS-1252') as $encoding) {
            if (mb_check_encoding($title, $encoding)) {
                return mb_convert_encoding($title, 'UTF-8', $encoding);
            }
        }
        return $title;
    }

    /**
     * Remove invalid UTF-8 characters from the given string.
     * @param String $rawText
     * @return string
     */
    private function sanitizeUTF8Text($rawText) {
        if (mb_check_encoding($rawText, 'UTF-8')) {
            return $rawText;
        }
        return iconv("UTF-8", "UTF-8//IGNORE", $rawText);
    }

    /**
     * Use a Symfony validator to ensure the language is well formatted.
     *
     * @param Entry  $entry
     * @param string $value Language to validate and save
     */
    public function updateLanguage(Entry $entry, $value)
    {
        // some lang are defined as fr-FR, es-ES.
        // replacing - by _ might increase language support
        $value = str_replace('-', '_', $value);

        $errors = $this->validator->validate(
            $value,
            (new LocaleConstraint())
        );

        if (0 === \count($errors)) {
            $entry->setLanguage($value);

            return;
        }

        $this->logger->warning('Language validation failed. ' . (string) $errors);
    }

    /**
     * Use a Symfony validator to ensure the preview picture is a real url.
     *
     * @param Entry  $entry
     * @param string $value URL to validate and save
     */
    public function updatePreviewPicture(Entry $entry, $value)
    {
        $errors = $this->validator->validate(
            $value,
            (new UrlConstraint())
        );

        if (0 === \count($errors)) {
            $entry->setPreviewPicture($value);

            return;
        }

        $this->logger->warning('PreviewPicture validation failed. ' . (string) $errors);
    }

    /**
     * Update date.
     *
     * @param Entry  $entry
     * @param string $value Date to validate and save
     */
    public function updatePublishedAt(Entry $entry, $value)
    {
        $date = $value;

        // is it a timestamp?
        if (false !== filter_var($date, FILTER_VALIDATE_INT)) {
            $date = '@' . $date;
        }

        try {
            // is it already a DateTime?
            // (it's inside the try/catch in case of fail to be parse time string)
            if (!$date instanceof \DateTime) {
                $date = new \DateTime($date);
            }

            $entry->setPublishedAt($date);
        } catch (\Exception $e) {
            $this->logger->warning('Error while defining date', ['e' => $e, 'url' => $entry->getUrl(), 'date' => $value]);
        }
    }

    /**
     * Helper to extract and save host from entry url.
     *
     * @param Entry $entry
     */
    public function setEntryDomainName(Entry $entry)
    {
        $domainName = parse_url($entry->getUrl(), PHP_URL_HOST);
        if (false !== $domainName) {
            $entry->setDomainName($domainName);
        }
    }

    /**
     * Helper to set a default title using:
     * - url basename, if applicable
     * - hostname.
     *
     * @param Entry $entry
     */
    public function setDefaultEntryTitle(Entry $entry)
    {
        $url = parse_url($entry->getUrl());
        $path = pathinfo($url['path'], PATHINFO_BASENAME);

        if (empty($path)) {
            $path = $url['host'];
        }

        $entry->setTitle($path);
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
        $entry->setUrl($content['url']);

        $this->setEntryDomainName($entry);

        if (!empty($content['title'])) {
            $entry->setTitle($content['title']);
        } elseif (!empty($content['open_graph']['og_title'])) {
            $entry->setTitle($content['open_graph']['og_title']);
        }

        $html = $content['html'];
        if (false === $html) {
            $html = $this->fetchingErrorMessage;

            if (!empty($content['open_graph']['og_description'])) {
                $html .= '<p><i>But we found a short description: </i></p>';
                $html .= $content['open_graph']['og_description'];
            }
        }

        $entry->setContent($html);
        $entry->setReadingTime(Utils::getReadingTime($html));

        if (!empty($content['status'])) {
            $entry->setHttpStatus($content['status']);
        }

        if (!empty($content['authors']) && \is_array($content['authors'])) {
            $entry->setPublishedBy($content['authors']);
        }

        if (!empty($content['all_headers']) && $this->storeArticleHeaders) {
            $entry->setHeaders($content['all_headers']);
        }

        if (!empty($content['date'])) {
            $this->updatePublishedAt($entry, $content['date']);
        }

        if (!empty($content['language'])) {
            $this->updateLanguage($entry, $content['language']);
        }

        if (!empty($content['open_graph']['og_image'])) {
            $this->updatePreviewPicture($entry, $content['open_graph']['og_image']);
        }

        // if content is an image, define it as a preview too
        if (!empty($content['content_type']) && \in_array($this->mimeGuesser->guess($content['content_type']), ['jpeg', 'jpg', 'gif', 'png'], true)) {
            $this->updatePreviewPicture($entry, $content['url']);
        }

        if (!empty($content['content_type'])) {
            $entry->setMimetype($content['content_type']);
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
