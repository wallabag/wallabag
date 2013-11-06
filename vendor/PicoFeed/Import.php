<?php

namespace PicoFeed;

class Import
{
    private $content = '';
    private $items = array();


    public function __construct($content)
    {
        $this->content = $content;
    }


    public function execute()
    {
        \PicoFeed\Logging::log(\get_called_class().': start importation');

        try {

            \libxml_use_internal_errors(true);

            $xml = new \SimpleXMLElement(trim($this->content));

            if ($xml->getName() !== 'opml' || ! isset($xml->body)) {
                \PicoFeed\Logging::log(\get_called_class().': OPML tag not found');
                return false;
            }

            $this->parseEntries($xml->body);

            \PicoFeed\Logging::log(\get_called_class().': '.count($this->items).' subscriptions found');
        }
        catch (\Exception $e) {
            \PicoFeed\Logging::log(\get_called_class().': '.$e->getMessage());
            return false;
        }

        return $this->items;
    }


    public function parseEntries($tree)
    {
        if (isset($tree->outline)) {

            foreach ($tree->outline as $item) {

                if (isset($item->outline)) {

                    $this->parseEntries($item);
                }
                else if ((isset($item['text']) || isset($item['title'])) && isset($item['xmlUrl'])) {

                    $entry = new \StdClass;
                    $entry->category = isset($tree['title']) ? (string) $tree['title'] : (string) $tree['text'];
                    $entry->title = isset($item['title']) ? (string) $item['title'] : (string) $item['text'];
                    $entry->feed_url = (string) $item['xmlUrl'];
                    $entry->site_url = isset($item['htmlUrl']) ? (string) $item['htmlUrl'] : $entry->feed_url;
                    $entry->type = isset($item['version']) ? (string) $item['version'] : isset($item['type']) ? (string) $item['type'] : 'rss';
                    $entry->description = isset($item['description']) ? (string) $item['description'] : $entry->title;
                    $this->items[] = $entry;
                }
            }
        }
    }
}