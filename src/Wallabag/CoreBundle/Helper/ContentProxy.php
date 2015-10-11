<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
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

    public function __construct(Graby $graby, RuleBasedTagger $tagger)
    {
        $this->graby  = $graby;
        $this->tagger = $tagger;
    }

    /**
     * Fetch content using graby and hydrate given entry with results information.
     * In case we couldn't find content, we'll try to use Open Graph data.
     *
     * @param Entry  $entry Entry to update
     * @param string $url   Url to grab content for
     *
     * @return Entry
     */
    public function updateEntry(Entry $entry, $url)
    {
        $content = $this->graby->fetchContent($url);

        $title = $content['title'];
        if (!$title && isset($content['open_graph']['og_title'])) {
            $title = $content['open_graph']['og_title'];
        }

        $html = $content['html'];
        if (false === $html) {
            $html = '<p>Unable to retrieve readable content.</p>';

            if (isset($content['open_graph']['og_description'])) {
                $html .= '<p><i>But we found a short description: </i></p>';
                $html .= $content['open_graph']['og_description'];
            }
        }

        $entry->setUrl($content['url'] ?: $url);
        $entry->setTitle($title);
        $entry->setContent($html);
        $entry->setLanguage($content['language']);
        $entry->setMimetype($content['content_type']);
        $entry->setReadingTime(Utils::getReadingTime($html));
        $entry->setDomainName(parse_url($entry->getUrl(), PHP_URL_HOST));

        if (isset($content['open_graph']['og_image'])) {
            $entry->setPreviewPicture($content['open_graph']['og_image']);
        }

        $this->tagger->tag($entry);

        return $entry;
    }
}
