<?php

namespace PicoFeed\Parsers;

class Atom extends \PicoFeed\Parser
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

        $this->url = $this->getUrl($xml);
        $this->title = $this->stripWhiteSpace((string) $xml->title);
        $this->id = (string) $xml->id;
        $this->updated = $this->parseDate((string) $xml->updated);
        $author = (string) $xml->author->name;

        foreach ($xml->entry as $entry) {

            if (isset($entry->author->name)) {

                $author = (string) $entry->author->name;
            }

            $id = (string) $entry->id;

            $item = new \StdClass;
            $item->url = $this->getUrl($entry);
            $item->id = $this->generateId($id !== $item->url ? $id : $item->url, $this->url);
            $item->title = $this->stripWhiteSpace((string) $entry->title);
            $item->updated = $this->parseDate((string) $entry->updated);
            $item->author = $author;
            $item->content = $this->filterHtml($this->getContent($entry), $item->url);

            if (empty($item->title)) $item->title = $item->url;

            $this->items[] = $item;
        }

        \PicoFeed\Logging::log(\get_called_class().': parsing finished ('.count($this->items).' items)');

        return $this;
    }


    public function getContent($entry)
    {
        if (isset($entry->content) && ! empty($entry->content)) {

            if (count($entry->content->children())) {

                return (string) $entry->content->asXML();
            }
            else {

                return (string) $entry->content;
            }
        }
        else if (isset($entry->summary) && ! empty($entry->summary)) {

            return (string) $entry->summary;
        }

        return '';
    }


    public function getUrl($xml)
    {
        foreach ($xml->link as $link) {

            if ((string) $link['type'] === 'text/html' || (string) $link['type'] === 'application/xhtml+xml') {

                return (string) $link['href'];
            }
        }

        return (string) $xml->link['href'];
    }
}