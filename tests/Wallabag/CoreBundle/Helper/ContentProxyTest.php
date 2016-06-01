<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Psr\Log\NullLogger;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\UserBundle\Entity\User;

class ContentProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testWithBadUrl()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getTagRepositoryMock(), $this->getLogger());
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://user@:80');

        $this->assertEquals('http://user@:80', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertEquals('<p>Unable to retrieve readable content.</p>', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals(false, $entry->getDomainName());
    }

    public function testWithEmptyContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getTagRepositoryMock(), $this->getLogger());
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://0.0.0.0', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertEquals('<p>Unable to retrieve readable content.</p>', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals('0.0.0.0', $entry->getDomainName());
    }

    public function testWithEmptyContentButOG()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
                'open_graph' => [
                    'og_title' => 'my title',
                    'og_description' => 'desc',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getTagRepositoryMock(), $this->getLogger());
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://domain.io');

        $this->assertEquals('http://domain.io', $entry->getUrl());
        $this->assertEquals('my title', $entry->getTitle());
        $this->assertEquals('<p>Unable to retrieve readable content.</p><p><i>But we found a short description: </i></p>desc', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals('domain.io', $entry->getDomainName());
    }

    public function testWithContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'open_graph' => [
                    'og_title' => 'my OG title',
                    'og_description' => 'OG desc',
                    'og_image' => 'http://3.3.3.3/cover.jpg',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getTagRepositoryMock(), $this->getLogger());
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
    }

    public function testWithForcedContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')->getMock();

        $proxy = new ContentProxy($graby, $tagger, $this->getTagRepositoryMock(), $this->getLogger());
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0', [
            'html' => str_repeat('this is my content', 325),
            'title' => 'this is my title',
            'url' => 'http://1.1.1.1',
            'content_type' => 'text/html',
            'language' => 'fr',
        ]);

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
    }

    public function testTaggerThrowException()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag')
            ->will($this->throwException(new \Exception()));

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $tagger, $tagRepo, $this->getLogger());

        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0', [
            'html' => str_repeat('this is my content', 325),
            'title' => 'this is my title',
            'url' => 'http://1.1.1.1',
            'content_type' => 'text/html',
            'language' => 'fr',
        ]);

        $this->assertCount(0, $entry->getTags());
    }

    public function testAssignTagsWithArrayAndExtraSpaces()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $this->getTaggerMock(), $tagRepo, $this->getLogger());

        $entry = new Entry(new User());

        $proxy->assignTagsToEntry($entry, ['   tag1', 'tag2   ']);

        $this->assertCount(2, $entry->getTags());
        $this->assertEquals('tag1', $entry->getTags()[0]->getLabel());
        $this->assertEquals('tag2', $entry->getTags()[1]->getLabel());
    }

    public function testAssignTagsWithString()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $this->getTaggerMock(), $tagRepo, $this->getLogger());

        $entry = new Entry(new User());

        $proxy->assignTagsToEntry($entry, 'tag1, tag2');

        $this->assertCount(2, $entry->getTags());
        $this->assertEquals('tag1', $entry->getTags()[0]->getLabel());
        $this->assertEquals('tag2', $entry->getTags()[1]->getLabel());
    }

    public function testAssignTagsWithEmptyArray()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $this->getTaggerMock(), $tagRepo, $this->getLogger());

        $entry = new Entry(new User());

        $proxy->assignTagsToEntry($entry, []);

        $this->assertCount(0, $entry->getTags());
    }

    public function testAssignTagsWithEmptyString()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $this->getTaggerMock(), $tagRepo, $this->getLogger());

        $entry = new Entry(new User());

        $proxy->assignTagsToEntry($entry, '');

        $this->assertCount(0, $entry->getTags());
    }

    public function testAssignTagsAlreadyAssigned()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepo = $this->getTagRepositoryMock();
        $proxy = new ContentProxy($graby, $this->getTaggerMock(), $tagRepo, $this->getLogger());

        $tagEntity = new Tag();
        $tagEntity->setLabel('tag1');

        $entry = new Entry(new User());
        $entry->addTag($tagEntity);

        $proxy->assignTagsToEntry($entry, 'tag1, tag2');

        $this->assertCount(2, $entry->getTags());
        $this->assertEquals('tag1', $entry->getTags()[0]->getLabel());
        $this->assertEquals('tag2', $entry->getTags()[1]->getLabel());
    }

    private function getTaggerMock()
    {
        return $this->getMockBuilder('Wallabag\CoreBundle\Helper\RuleBasedTagger')
            ->setMethods(['tag'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTagRepositoryMock()
    {
        return $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getLogger()
    {
        return new NullLogger();
    }
}
