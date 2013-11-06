<?php

namespace PicoFeed;

class Export
{
    private $content = array();

    public $required_fields = array(
        'title',
        'site_url',
        'feed_url'
    );


    public function __construct(array $content)
    {
        $this->content = $content;
    }


    public function execute()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><opml/>');

        $head = $xml->addChild('head');
        $head->addChild('title', 'OPML Export');

        $body = $xml->addChild('body');

        foreach ($this->content as $feed) {

            $valid = true;

            foreach ($this->required_fields as $field) {

                if (! isset($feed[$field])) {

                    $valid = false;
                    break;
                }
            }

            if (! $valid) continue;

            $outline = $body->addChild('outline');
            $outline->addAttribute('xmlUrl', $feed['feed_url']);
            $outline->addAttribute('htmlUrl', $feed['site_url']);
            $outline->addAttribute('title', $feed['title']);
            $outline->addAttribute('text', $feed['title']);
            $outline->addAttribute('description', isset($feed['description']) ? $feed['description'] : $feed['title']);
            $outline->addAttribute('type', 'rss');
            $outline->addAttribute('version', 'RSS');
        }

        return $xml->asXML();
    }
}