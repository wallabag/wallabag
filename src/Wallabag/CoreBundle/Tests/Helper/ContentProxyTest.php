<?php

namespace Wallabag\CoreBundle\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\User;
use Wallabag\CoreBundle\Helper\ContentProxy;

class ContentProxyTest extends KernelTestCase
{
    public function testWithEmptyContent()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(array('fetchContent'))
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn(array('html' => false, 'title' => '', 'url' => '', 'content_type' => ''));

        $proxy = new ContentProxy($graby);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://0.0.0.0', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertEquals('<p>Unable to retrieve readable content.</p>', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
    }

    public function testWithEmptyContentButOG()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(array('fetchContent'))
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn(array('html' => false, 'title' => '', 'url' => '', 'content_type' => '', 'open_graph' => array('og_title' => 'my title', 'og_description' => 'desc')));

        $proxy = new ContentProxy($graby);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://0.0.0.0', $entry->getUrl());
        $this->assertEquals('my title', $entry->getTitle());
        $this->assertEquals('<p>Unable to retrieve readable content.</p><p><i>But we found a short description: </i></p>desc', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
    }

    public function testWithContent()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(array('fetchContent'))
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn(array(
                'html' => 'this is my content',
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'open_graph' => array(
                    'og_title' => 'my OG title',
                    'og_description' => 'OG desc',
                    'og_image' => 'http://3.3.3.3/cover.jpg',
                ),
            ));

        $proxy = new ContentProxy($graby);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertEquals('this is my content', $entry->getContent());
        $this->assertEquals('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertEquals('text/html', $entry->getMimetype());
    }
}
