<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Tools\Utils;
use Wallabag\CoreBundle\Repository\TagRepository;
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
    protected $tagRepository;
    protected $mimeGuesser;
    protected $fetchingErrorMessage;

    public function __construct(Graby $graby, RuleBasedTagger $tagger, TagRepository $tagRepository, LoggerInterface $logger, $fetchingErrorMessage)
    {
        $this->graby = $graby;
        $this->tagger = $tagger;
        $this->logger = $logger;
        $this->tagRepository = $tagRepository;
        $this->mimeGuesser = new MimeTypeExtensionGuesser();
        $this->fetchingErrorMessage = $fetchingErrorMessage;
    }

    /**
     * Fetch content using graby and hydrate given entry with results information.
     * In case we couldn't find content, we'll try to use Open Graph data.
     *
     * We can also force the content, in case of an import from the v1 for example, so the function won't
     * fetch the content from the website but rather use information given with the $content parameter.
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     * @param array  $content An array with AT LEAST keys title, html, url, language & content_type to skip the fetchContent from the url
     *
     * @return Entry
     */
    public function updateEntry(Entry $entry, $url, array $content = [])
    {
        // do we have to fetch the content or the provided one is ok?
        if (empty($content) || false === $this->validateContent($content)) {
            $fetchedContent = $this->graby->fetchContent($url);

            // when content is imported, we have information in $content
            // in case fetching content goes bad, we'll keep the imported information instead of overriding them
            if (empty($content) || $fetchedContent['html'] !== $this->fetchingErrorMessage) {
                $content = $fetchedContent;
            }
        }

        $title = $content['title'];
        if (!$title && isset($content['open_graph']['og_title'])) {
            $title = $content['open_graph']['og_title'];
        }

        $html = $content['html'];
        if (false === $html) {
            $html = $this->fetchingErrorMessage;

            if (isset($content['open_graph']['og_description'])) {
                $html .= '<p><i>But we found a short description: </i></p>';
                $html .= $content['open_graph']['og_description'];
            }
        }

        $entry->setUrl($content['url'] ?: $url);
        $entry->setTitle($title);
        $entry->setContent($html);
        $entry->setHttpStatus(isset($content['status']) ? $content['status'] : '');

        if (isset($content['date']) && null !== $content['date'] && '' !== $content['date']) {
            $entry->setPublishedAt(new \DateTime($content['date']));
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

        if (isset($content['open_graph']['og_image']) && $content['open_graph']['og_image']) {
            $entry->setPreviewPicture($content['open_graph']['og_image']);
        }

        // if content is an image define as a preview too
        if (isset($content['content_type']) && in_array($this->mimeGuesser->guess($content['content_type']), ['jpeg', 'jpg', 'gif', 'png'], true)) {
            $entry->setPreviewPicture($content['url']);
        }

        try {
            $this->tagger->tag($entry);
        } catch (\Exception $e) {
            $this->logger->error('Error while trying to automatically tag an entry.', [
                'entry_url' => $url,
                'error_msg' => $e->getMessage(),
            ]);
        }

        return $entry;
    }

    /**
     * Assign some tags to an entry.
     *
     * @param Entry        $entry
     * @param array|string $tags          An array of tag or a string coma separated of tag
     * @param array        $entitiesReady Entities from the EntityManager which are persisted but not yet flushed
     *                                    It is mostly to fix duplicate tag on import @see http://stackoverflow.com/a/7879164/569101
     */
    public function assignTagsToEntry(Entry $entry, $tags, array $entitiesReady = [])
    {
        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }

        // keeps only Tag entity from the "not yet flushed entities"
        $tagsNotYetFlushed = [];
        foreach ($entitiesReady as $entity) {
            if ($entity instanceof Tag) {
                $tagsNotYetFlushed[$entity->getLabel()] = $entity;
            }
        }

        foreach ($tags as $label) {
            $label = trim($label);

            // avoid empty tag
            if (0 === strlen($label)) {
                continue;
            }

            if (isset($tagsNotYetFlushed[$label])) {
                $tagEntity = $tagsNotYetFlushed[$label];
            } else {
                $tagEntity = $this->tagRepository->findOneByLabel($label);

                if (is_null($tagEntity)) {
                    $tagEntity = new Tag();
                    $tagEntity->setLabel($label);
                }
            }

            // only add the tag on the entry if the relation doesn't exist
            if (false === $entry->getTags()->contains($tagEntity)) {
                $entry->addTag($tagEntity);
            }
        }
    }

    /**
     * Validate that the given content as enough value to be used
     * instead of fetch the content from the url.
     *
     * @param array $content
     *
     * @return bool true if valid otherwise false
     */
    private function validateContent(array $content)
    {
        return isset($content['title']) && isset($content['html']) && isset($content['url']) && isset($content['language']) && isset($content['content_type']);
    }
}
