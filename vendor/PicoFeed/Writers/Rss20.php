<?php

namespace PicoFeed\Writers;

require_once __DIR__.'/../Writer.php';

class Rss20 extends \PicoFeed\Writer
{
    private $required_feed_properties = array(
        'title',
        'site_url',
        'feed_url',
    );

    private $required_item_properties = array(
        'title',
        'url',
    );


    public function execute($filename = '')
    {
        $this->checkRequiredProperties($this->required_feed_properties, $this);

        $this->dom = new \DomDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // <rss/>
        $rss = $this->dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttributeNodeNS(new \DomAttr('xmlns:content', 'http://purl.org/rss/1.0/modules/content/'));
        $rss->setAttributeNodeNS(new \DomAttr('xmlns:atom', 'http://www.w3.org/2005/Atom'));

        $channel = $this->dom->createElement('channel');

        // <generator/>
        $generator = $this->dom->createElement('generator', 'PicoFeed (https://github.com/fguillot/picoFeed)');
        $channel->appendChild($generator);

        // <title/>
        $title = $this->dom->createElement('title');
        $title->appendChild($this->dom->createTextNode($this->title));
        $channel->appendChild($title);

        // <description/>
        $description = $this->dom->createElement('description');
        $description->appendChild($this->dom->createTextNode(isset($this->description) ? $this->description : $this->title));
        $channel->appendChild($description);

        // <pubDate/>
        $this->addPubDate($channel, isset($this->updated) ? $this->updated : '');

        // <atom:link/>
        $link = $this->dom->createElement('atom:link');
        $link->setAttribute('href', $this->feed_url);
        $link->setAttribute('rel', 'self');
        $link->setAttribute('type', 'application/rss+xml');
        $channel->appendChild($link);

        // <link/>
        $link = $this->dom->createElement('link');
        $link->appendChild($this->dom->createTextNode($this->site_url));
        $channel->appendChild($link);

        // <webMaster/>
        if (isset($this->author)) $this->addAuthor($channel, 'webMaster', $this->author);

        // <item/>
        foreach ($this->items as $item) {

            $this->checkRequiredProperties($this->required_item_properties, $item);

            $entry = $this->dom->createElement('item');

            // <title/>
            $title = $this->dom->createElement('title');
            $title->appendChild($this->dom->createTextNode($item['title']));
            $entry->appendChild($title);

            // <link/>
            $link = $this->dom->createElement('link');
            $link->appendChild($this->dom->createTextNode($item['url']));
            $entry->appendChild($link);

            // <guid/>
            if (isset($item['id'])) {
                $guid = $this->dom->createElement('guid');
                $guid->setAttribute('isPermaLink', 'false');
                $guid->appendChild($this->dom->createTextNode($item['id']));
                $entry->appendChild($guid);
            }
            else {
                $guid = $this->dom->createElement('guid');
                $guid->setAttribute('isPermaLink', 'true');
                $guid->appendChild($this->dom->createTextNode($item['url']));
                $entry->appendChild($guid);
            }

            // <pubDate/>
            $this->addPubDate($entry, isset($item['updated']) ? $item['updated'] : '');

            // <description/>
            if (isset($item['summary'])) {
                $description = $this->dom->createElement('description');
                $description->appendChild($this->dom->createTextNode($item['summary']));
                $entry->appendChild($description);
            }

            // <content/>
            if (isset($item['content'])) {
                $content = $this->dom->createElement('content:encoded');
                $content->appendChild($this->dom->createCDATASection($item['content']));
                $entry->appendChild($content);
            }

            // <author/>
            if (isset($item['author'])) $this->addAuthor($entry, 'author', $item['author']);

            $channel->appendChild($entry);
        }

        $rss->appendChild($channel);
        $this->dom->appendChild($rss);

        if ($filename) {
            $this->dom->save($filename);
        }
        else {
            return $this->dom->saveXML();
        }
    }


    public function addPubDate($xml, $value = '')
    {
        $xml->appendChild($this->dom->createElement(
            'pubDate',
            date(DATE_RFC822, $value ?: time())
        ));
    }


    public function addAuthor($xml, $tag, array $values)
    {
        $value = '';

        if (isset($values['email'])) $value .= $values['email'];
        if ($value && isset($values['name'])) $value .= ' ('.$values['name'].')';

        if ($value) {
            $author = $this->dom->createElement($tag);
            $author->appendChild($this->dom->createTextNode($value));
            $xml->appendChild($author);
        }
    }
}