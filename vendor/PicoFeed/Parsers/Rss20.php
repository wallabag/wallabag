<?php

namespace PicoFeed\Parsers;

class Rss20 extends \PicoFeed\Parser
{
    public function execute()
    {
        \PicoFeed\Logging::log(\get_called_class().': begin parsing');

        \libxml_use_internal_errors(true);
        $xml = \simplexml_load_string($this->content);

        if ($xml === false) {
            \PicoFeed\Logging::log(\get_called_class().': XML parsing error');
            \PicoFeed\Logging::log($this->getXmlErrors());
            return false;
        }

        $namespaces = $xml->getNamespaces(true);

        if ($xml->channel->link && $xml->channel->link->count() > 1) {

            foreach ($xml->channel->link as $xml_link) {

                $link = (string) $xml_link;

                if ($link !== '') {

                    $this->url = (string) $link;
                    break;
                }
            }
        }
        else {

            $this->url = (string) $xml->channel->link;
        }

        $this->title = $this->stripWhiteSpace((string) $xml->channel->title);
        $this->id = $this->url;
        $this->updated = $this->parseDate(isset($xml->channel->pubDate) ? (string) $xml->channel->pubDate : (string) $xml->channel->lastBuildDate);

        // RSS feed might be empty
        if (! $xml->channel->item) {
            \PicoFeed\Logging::log(\get_called_class().': feed empty or malformed');
            return $this;
        }

        foreach ($xml->channel->item as $entry) {

            $item = new \StdClass;
            $item->title = $this->stripWhiteSpace((string) $entry->title);
            $item->url = '';
            $item->author= '';
            $item->updated = '';
            $item->content = '';

            foreach ($namespaces as $name => $url) {

                $namespace = $entry->children($namespaces[$name]);

                if (! $item->url && ! empty($namespace->origLink)) $item->url = (string) $namespace->origLink;
                if (! $item->author && ! empty($namespace->creator)) $item->author = (string) $namespace->creator;
                if (! $item->updated && ! empty($namespace->date)) $item->updated = $this->parseDate((string) $namespace->date);
                if (! $item->updated && ! empty($namespace->updated)) $item->updated = $this->parseDate((string) $namespace->updated);
                if (! $item->content && ! empty($namespace->encoded)) $item->content = (string) $namespace->encoded;
            }

            if (empty($item->url)) {

                if (isset($entry->link)) {
                    $item->url = (string) $entry->link;
                }
                else if (isset($entry->guid)) {
                    $item->url = (string) $entry->guid;
                }
            }

            if (empty($item->updated)) $item->updated = $this->parseDate((string) $entry->pubDate) ?: $this->updated;

            if (empty($item->content)) {
                $item->content = isset($entry->description) ? (string) $entry->description : '';
            }

            if (empty($item->author)) {

                if (isset($entry->author)) {

                    $item->author = (string) $entry->author;
                }
                else if (isset($xml->channel->webMaster)) {

                    $item->author = (string) $xml->channel->webMaster;
                }
            }

            if (isset($entry->guid) && isset($entry->guid['isPermaLink']) && (string) $entry->guid['isPermaLink'] != 'false') {

                $id = (string) $entry->guid;
                $item->id = $this->generateId($id !== '' && $id !== $item->url ? $id : $item->url, $this->url);
            }
            else {

                $item->id = $this->generateId($item->url, $this->url);
            }

            if (empty($item->title)) $item->title = $item->url;

            $item->content = $this->filterHtml($item->content, $item->url);
            $this->items[] = $item;
        }

        \PicoFeed\Logging::log(\get_called_class().': parsing finished ('.count($this->items).' items)');

        return $this;
    }
}