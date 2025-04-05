<?php

namespace Wallabag\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Validator\Constraints\Locale as LocaleConstraint;
use Symfony\Component\Validator\Constraints\Url as UrlConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\Entity\Entry;
use Wallabag\Tools\Utils;

/**
 * This kind of proxy class takes care of getting the content from an url
 * and updates the entry with what it found.
 */
class ContentProxy
{
    protected $mimeTypes;
    protected $eventDispatcher;

    public function __construct(
        protected Graby $graby,
        protected RuleBasedTagger $tagger,
        protected RuleBasedIgnoreOriginProcessor $ignoreOriginProcessor,
        protected ValidatorInterface $validator,
        protected LoggerInterface $logger,
        protected $fetchingErrorMessage,
        protected $storeArticleHeaders = false,
    ) {
        $this->mimeTypes = new MimeTypes();
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
        $this->graby->toggleImgNoReferrer(true);
        if (!empty($content['html'])) {
            $content['html'] = $this->graby->cleanupHtml($content['html'], $url);
        }

        if ((empty($content) || false === $this->validateContent($content)) && false === $disableContentUpdate) {
            $fetchedContent = $this->graby->fetchContent($url);

            $fetchedContent['title'] = $this->sanitizeContentTitle(
                $fetchedContent['title'],
                $fetchedContent['headers']['content-type'] ?? ''
            );

            // when content is imported, we have information in $content
            // in case fetching content goes bad, we'll keep the imported information instead of overriding them
            if (empty($content) || $fetchedContent['html'] !== $this->fetchingErrorMessage) {
                $content = $fetchedContent;
            }
        }

        // be sure to keep the url in case of error
        // so we'll be able to refetch it in the future
        $content['url'] = !empty($content['url']) ? $content['url'] : $url;

        // In one case (at least in tests), url is empty here
        // so we set it using $url provided in the updateEntry call.
        // Not sure what are the other possible cases where this property is empty
        if (empty($entry->getUrl()) && !empty($url)) {
            $entry->setUrl($url);
        }

        $entry->setGivenUrl($url);

        $this->stockEntry($entry, $content);
    }

    /**
     * Use a Symfony validator to ensure the language is well formatted.
     *
     * @param string $value Language to validate and save
     */
    public function updateLanguage(Entry $entry, $value)
    {
        // some lang are defined as fr-FR, es-ES.
        // replacing - by _ might increase language support
        $value = str_replace('-', '_', $value);

        $errors = $this->validator->validate(
            $value,
            new LocaleConstraint(['canonicalize' => true])
        );

        if (0 === \count($errors)) {
            $entry->setLanguage($value);

            return;
        }

        foreach ($errors as $error) {
            $this->logger->warning('Language validation failed. ' . $error->getMessage());
        }
    }

    /**
     * Use a Symfony validator to ensure the preview picture is a real url.
     *
     * @param string $value URL to validate and save
     */
    public function updatePreviewPicture(Entry $entry, $value)
    {
        $errors = $this->validator->validate(
            $value,
            new UrlConstraint()
        );

        if (0 === \count($errors)) {
            $entry->setPreviewPicture($value);

            return;
        }

        foreach ($errors as $error) {
            $this->logger->warning('PreviewPicture validation failed. ' . $error->getMessage());
        }
    }

    /**
     * Update date.
     *
     * @param string $value Date to validate and save
     */
    public function updatePublishedAt(Entry $entry, $value)
    {
        $date = $value;

        // is it a timestamp?
        if (false !== filter_var($date, \FILTER_VALIDATE_INT)) {
            $date = '@' . $date;
        }

        try {
            // (it's inside the try/catch in case of fail to be parse time string)
            $date = new \DateTime($date);

            $entry->setPublishedAt($date);
        } catch (\Exception $e) {
            $this->logger->warning('Error while defining date', ['e' => $e, 'url' => $entry->getUrl(), 'date' => $value]);
        }
    }

    /**
     * Helper to extract and save host from entry url.
     */
    public function setEntryDomainName(Entry $entry)
    {
        $domainName = parse_url($entry->getUrl(), \PHP_URL_HOST);
        if (false !== $domainName) {
            $entry->setDomainName($domainName);
        }
    }

    /**
     * Helper to set a default title using:
     * - url basename, if applicable
     * - hostname.
     */
    public function setDefaultEntryTitle(Entry $entry)
    {
        $url = parse_url($entry->getUrl());
        $path = pathinfo($url['path'], \PATHINFO_BASENAME);

        if (empty($path)) {
            $path = $url['host'];
        }

        $entry->setTitle($path);
    }

    /**
     * Try to sanitize the title of the fetched content from wrong character encodings and invalid UTF-8 character.
     *
     * @param string $title
     * @param string $contentType
     *
     * @return string
     */
    private function sanitizeContentTitle($title, $contentType)
    {
        if ('application/pdf' === $contentType) {
            $title = $this->convertPdfEncodingToUTF8($title);
        }

        return $this->sanitizeUTF8Text($title);
    }

    /**
     * If the title from the fetched content comes from a PDF, then its very possible that the character encoding is not
     * UTF-8. This methods tries to identify the character encoding and translate the title to UTF-8.
     *
     * @return string (maybe contains invalid UTF-8 character)
     */
    private function convertPdfEncodingToUTF8($title)
    {
        // first try UTF-8 because its easier to detect its present/absence
        foreach (['UTF-8', 'UTF-16BE', 'WINDOWS-1252'] as $encoding) {
            if (mb_check_encoding($title, $encoding)) {
                return mb_convert_encoding($title, 'UTF-8', $encoding);
            }
        }

        return $title;
    }

    /**
     * Remove invalid UTF-8 characters from the given string.
     *
     * @param string $rawText
     *
     * @return string
     */
    private function sanitizeUTF8Text($rawText)
    {
        if (mb_check_encoding($rawText, 'UTF-8')) {
            return $rawText;
        }

        mb_substitute_character('none');

        return mb_convert_encoding($rawText, 'UTF-8', 'UTF-8');
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
        $this->updateOriginUrl($entry, $content['url']);

        $this->setEntryDomainName($entry);

        if (!empty($content['title'])) {
            $entry->setTitle($content['title']);
        }

        if (empty($content['html'])) {
            $content['html'] = $this->fetchingErrorMessage;
            $entry->setNotParsed(true);

            if (!empty($content['description'])) {
                $content['html'] .= '<p><i>But we found a short description: </i></p>';
                $content['html'] .= $content['description'];
            }
        }

        $entry->setContent($content['html']);
        $entry->setReadingTime(Utils::getReadingTime($content['html']));

        if (!empty($content['status'])) {
            $entry->setHttpStatus($content['status']);
        }

        if (!empty($content['authors']) && \is_array($content['authors'])) {
            $entry->setPublishedBy($content['authors']);
        }

        if (!empty($content['headers'])) {
            $entry->setHeaders($content['headers']);
        }

        if (!empty($content['date'])) {
            $this->updatePublishedAt($entry, $content['date']);
        }

        if (!empty($content['language'])) {
            $this->updateLanguage($entry, $content['language']);
        }

        $previewPictureUrl = '';
        if (!empty($content['image'])) {
            $previewPictureUrl = $content['image'];
        }

        // if content is an image, define it as a preview too
        if (!empty($content['headers']['content-type']) && \in_array(current($this->mimeTypes->getExtensions($content['headers']['content-type'])), ['jpeg', 'jpg', 'gif', 'png'], true)) {
            $previewPictureUrl = $content['url'];
        } elseif (empty($previewPictureUrl)) {
            $this->logger->debug('Extracting images from content to provide a default preview picture');
            $imagesUrls = DownloadImages::extractImagesUrlsFromHtml($content['html']);
            $this->logger->debug(\count($imagesUrls) . ' pictures found');

            if (!empty($imagesUrls)) {
                $previewPictureUrl = $imagesUrls[0];
            }
        }

        if (!empty($content['headers']['content-type'])) {
            $entry->setMimetype($content['headers']['content-type']);
        }

        if (!empty($previewPictureUrl)) {
            $this->updatePreviewPicture($entry, $previewPictureUrl);
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
     * Update the origin_url field when a redirection occurs
     * This field is set if it is empty and new url does not match ignore list.
     *
     * @param string $url
     */
    private function updateOriginUrl(Entry $entry, $url)
    {
        if (empty($url) || $entry->getUrl() === $url) {
            return false;
        }

        $parsed_entry_url = parse_url($entry->getUrl());
        $parsed_content_url = parse_url($url);

        /**
         * The following part computes the list of part changes between two
         * parse_url arrays.
         *
         * As array_diff_assoc only computes changes to go from the left array
         * to the right one, we make two different arrays to have both
         * directions. We merge these two arrays and sort keys before passing
         * the result to the switch.
         *
         * The resulting array gives us all changing parts between the two
         * urls: scheme, host, path, query and/or fragment.
         */
        $diff_ec = array_diff_assoc($parsed_entry_url, $parsed_content_url);
        $diff_ce = array_diff_assoc($parsed_content_url, $parsed_entry_url);

        $diff = array_merge($diff_ec, $diff_ce);
        $diff_keys = array_keys($diff);
        sort($diff_keys);

        if ($this->ignoreOriginProcessor->process($entry)) {
            $entry->setUrl($url);

            return false;
        }

        /**
         * This switch case lets us apply different behaviors according to
         * changing parts of urls.
         *
         * As $diff_keys is an array, we provide arrays as cases. ['path'] means
         * 'only the path is different between the two urls' whereas
         * ['fragment', 'query'] means 'only fragment and query string parts are
         * different between the two urls'.
         *
         * Note that values in $diff_keys are sorted.
         */
        switch ($diff_keys) {
            case ['path']:
                if (($parsed_entry_url['path'] . '/' === $parsed_content_url['path']) // diff is trailing slash, we only replace the url of the entry
                    || ($url === urldecode($entry->getUrl()))) { // we update entry url if new url is a decoded version of it, see EntryRepository#findByUrlAndUserId
                    $entry->setUrl($url);
                }
                break;
            case ['scheme']:
                $entry->setUrl($url);
                break;
            case ['fragment']:
                // noop
                break;
            default:
                if (empty($entry->getOriginUrl())) {
                    $entry->setOriginUrl($entry->getUrl());
                }
                $entry->setUrl($url);
                break;
        }
    }

    /**
     * Validate that the given content has at least a title, an html and a url.
     *
     * @return bool true if valid otherwise false
     */
    private function validateContent(array $content)
    {
        return !empty($content['title']) && !empty($content['html']) && !empty($content['url']);
    }
}
